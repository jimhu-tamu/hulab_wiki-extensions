<?php
/*    
 * MirrorAnnotations - An extension 'module' for the TableEdit extension.
 * @author Daniel Renfro (bluecurio@gmail.com)
 * @version 0.1
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 */

if ( ! defined( 'MEDIAWIKI' ) ) die("Not a valid entry point.");

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'MirrorAnnotations',
    'author'=>'[mailto:bluecurio@gmail.com Daniel Renfro]',
    'description'=>'Connect the annotations on GO Tables and PMID pages.',
    'version'=>'0.1'
);
  
$wgHooks['TableEditBeforeSave'][] = new TableEdit_MirrorAnnotations;

class TableEdit_MirrorAnnotations{
	
	var $create_pmid_page_func 		= 'wfLoadPubmedPageOnDemand';
	var $pmid_table_template 		= 'GO table reference';   	// why no underscores?
	var $product_table_template 	= 'GO_table_product';
	
	var $page_types = array(
			"Quickview"			=> "Quickview", 
			"Gene"				=> "Gene", 
			"Gene_Product(s)"	=> "Product", 
			"Expression"		=> "Expression", 
			"Evolution"			=> "Evolution", 
			"On_One_Page"		=> "On_One_Page"
		);
	
	/**
	 * Main method that gets called on the "TableEditBeforeSave" hook.
	 * 
	 * @param object $teObj TableEdit object coming from the hooked code.
	 * @param string $table A wikitext representation of the current box.
	 */	
	function onTableEditBeforeSave( &$teObj, &$table ){
		global $egMirrorAnnotations;
			
		if(isset($egMirrorAnnotations)){
			// keep from doing circular saves. 
			return true;					
		}
		
		$this->teObj =& $teObj;
		$this->table =& $table;
		
		switch ($teObj->box->template){
	
			case $this->product_table_template:				// Gene Ontology table on the Product Page.
				$egMirrorAnnotations = $this->product_table_template;
				// set the gene name 
				$product_text_parts = explode(':', $this->teObj->page_name);
				$this->gene_name = strtolower($product_text_parts[0]);
				
				// loop through the rows in the box, finding new ones (w/o timestamp)
				foreach($teObj->box->rows as $row){
					$this->annotation = $this->get_table_obj($row->row_data, $this->product_table_template);
					if($row->is_current && ($row->is_edited === true || $row->timestamp == "")){
						if( !preg_match("/^IEA/", $this->annotation->evidence) && isset($this->annotation->go_id) && isset($this->annotation->evidence)){	
							// lookup this row in the `relations` table. 
							if(!$row->relations){
								preg_match_all("/PMID:\d+/", $this->annotation->refs, $m);
								for ($i=0, $c=count($m[0]); $i<$c; $i++) {
									$current_reference = $m[0][$i];
									// get the pmid page's article object, or create one if it doesn't exist. 
									$ref_title_obj = Title::newFromText(trim($current_reference));
									if(!$ref_title_obj->exists()){
										// if we can make a PMID page "onDemand," then do it, else give a silent error (to the log)
										$this->create_new_pmid_on_demand($ref_title_obj);										
									}
									// get the PMID page's article object
									$pmid_page = new Article($ref_title_obj);
									$pmid_page->loadContent();
									// get the GO_table_reference box object
									$pmid_box= new wikiBox("");
									$pmid_box->setPageName($ref_title_obj->getDBkey());
									$pmid_box->setTemplate($this->pmid_table_template);
									$pmid_box->set_from_DB();								
									$pmid_row_data = $this->create_pmid_row_data_from_product_annotation($this->annotation);
									foreach($pmid_box->rows as $pmid_row){
										$pmid_annotation = $this->get_table_obj($pmid_row->row_data, $this->pmid_table_template);
										if (	
											$pmid_annotation->go_id == $this->annotation->go_id  &&
											$pmid_annotation->evidence == $this->annotation->evidence &&
											strtolower($pmid_annotation->product) == $this->gene_name
										) {
											$matching_pmid_row =& $pmid_row;
										}
									}
									if(!is_null($matching_pmid_row)){			// found a matching row.
										$matching_pmid_row->row_data = $this->create_pmid_row_data_from_product_annotation($this->annotation);
										$this->save($pmid_page, $pmid_box);
										wikiBoxRow::insert_relation($row->row_id, NULL, $matching_pmid_row->row_id, NULL);
									} else {									// looked through this table but found nothing. 
										$new_pmid_row = $this->append_and_save($pmid_page, $pmid_box, $pmid_row_data);
										wikiBoxRow::insert_relation($row->row_id, NULL, $new_pmid_row->row_id, NULL);
									}
								}
							} else {
								foreach($row->relations as $relation){
									$x = TableEdit::lookup_box_id_from_relation($relation, $row);
									$relational_box = new wikiBox();
									$relational_box->setId($x->box_id);
									$relational_box->set_from_DB();	
									//find the right row in this box, and remake it. then save.
									foreach($relational_box->rows as &$rrow) {
										if($rrow->row_id == $relation->row_id){
											$rrow->row_data = $this->create_pmid_row_data_from_product_annotation($this->annotation);
											break;
										}
									}
									$pmid_page = new Article(Title::newFromText($relational_box->page_name));
									$pmid_page->fetchContent();
									$this->save($pmid_page, $relational_box);
								}
							}
						}
					}
				}
				break;

					
			case $this->pmid_table_template:				// Gene Ontology table on the PMID (reference) pages. 	
				$this->page_name = $this->teObj->page_name;
				$egMirrorAnnotations = $this->pmid_table_template;
				foreach($teObj->box->rows as $row){
					if($row->is_current && ($row->is_edited === true || $row->timestamp == "")){
						$this->annotation = $this->get_table_obj($row->row_data, $this->pmid_table_template);
						if(!preg_match("/^IEA/", $this->annotation->evidence) && isset($this->annotation->go_id) && isset($this->annotation->evidence)){	
							$relations = TableEdit::query_relations_table( $row, 'both' );
							$product_title = Title::newFromText(trim($this->annotation->product . ":Gene_Product(s)"));
							$product_article = new Article($product_title);
							$product_article->loadContent();
							if(!$product_article->exists()) {	
								continue;	// this isn't a product page, skip it...probably should throw an error. 
								trigger_error("\"{$product_title->getDBkey()} does not exist.\"", E_USER_NOTICE);
							}
							$product_article->loadContent();
							if( !$relations ) {
								if(!$product_article->exists()) return true;
								// get the GO_table_reference box object
								$product_box= new wikiBox("");
								$product_box->setPageName($product_title->getDBkey());
								$product_box->setTemplate($this->product_table_template);
								$product_box->set_from_DB();
								
								/* 	Right now this code just appends a row onto the product's box. Really it should
									check to see if that annotation exists based on some criteria and update that row.
									But how do we know which annotation goes with which?
								*/							
								$row_data = $this->create_product_row_data_from_pmid_annotation($this->annotation);
								$new_product_row = $this->append_and_save($product_article, $product_box, $row_data);
								TableEdit::insert_relation($row, $new_product_row);		
							} else {
								foreach($relations as $relation){
									$relational_box = new wikiBox($relation->box_uid);
									$relational_box->set_from_DB();
									list($tmp_gene_name, $tmp_page_type) = explode(":", $relational_box->page_name);
									if(trim($this->annotation->product) == trim($tmp_gene_name)){
										//find the right row in this box, and remake it. then save.
										foreach($relational_box->rows as &$prow) {
											if($prow->row_id == $relation->row_id){
												$prow->row_data = $this->create_product_row_data_from_pmid_annotation($this->annotation);
												break;
											}
										}
										$this->save($product_article, $relational_box);
									} else {
										// there are relations, but nothing matched what we've got. just append if you can. 
										die("PMID product doesn't match database entry for rel_id={$relation->rel_id}.");
									}
								}
							}
						}
					}
				}
				break;
				

			default:				// the default should do nothing, this only applies to the previous two templates	
				break;
		}
		return true;
	}
	
	/**
	 * Turns a line of $row_data from a GO_table_product table into an object.
	 * @param string $row_data 
	 * @return object 
	 */
	function get_table_obj( $row_data, $template ){
		if(is_null($row_data) || $row_data == "") return;
		switch($template){
			case $this->product_table_template:
				$field_names = array('qualifier', 'go_id', 'go_term', 'refs', 'evidence', 'with', 'aspect', 'notes', 'status');
				break;
			case $this->pmid_table_template:
				$field_names = array('product', 'qualifier', 'go_id', 'go_term', 'evidence', 'with', 'aspect', 'notes', 'status');
			default:
				break;
		}
		$fields = explode('||', $row_data);
		$obj = new StdClass;
		for ($i=0, $c=count($field_names); $i<$c; $i++) {
			$obj->{$field_names[$i]} = $fields[$i];
		}
		return $obj;
	}

	function create_pmid_row_data_from_product_annotation(&$obj){
		unset($obj->refs);
		$x[0] = $this->format_gene_name($this->gene_name);
		$x[1] = $obj->qualifier;
		$x[2] = $obj->go_id;
		$x[3] = $obj->go_term;
		$x[4] = $obj->evidence;
		$x[5] = $obj->with;
		$x[6] = $obj->aspect;
		$x[7] = $obj->notes;
		$x[8] = $obj->status;
		return implode('||', $x);
	}
	
	function create_product_row_data_from_pmid_annotation(&$obj){
		$x[0] = $obj->qualifier;
		$x[1] = $obj->go_id;
		$x[2] = $obj->go_term;
		$x[3] = trim($this->page_name);
		$x[4] = $obj->evidence;
		$x[5] = $obj->with;
		$x[6] = $obj->aspect;
		$x[7] = $obj->notes;
		$x[8] = $obj->status;
		return implode('||', $x);
	}
	
	function format_gene_name( &$str ){
		$last_char_pos = strlen($str) - 1;
		$str[$last_char_pos] = strtoupper($str[$last_char_pos]);
		return $str;
	}
	
	function create_new_pmid_on_demand( $title_obj ){
		if(function_exists($this->create_pmid_page_func)){
			call_user_func_array($this->create_pmid_page_func, array($title_obj, null));
		} else trigger_error ("Failed making a PMID page. {$create_pmid_page_func} does not exist.", E_USER_WARNING);
		return;
	}

	function getBoxUid($page_name, $template=''){
		$dbr =& wfGetDB( DB_SLAVE );
		$conditions['page_name'] =  $page_name;
		$conditions['template'] = str_replace(' ', '_', $template);
		$result = $dbr->select('ext_TableEdit_box', 'box_uid', $conditions, __METHOD__);
		if(!is_null($result)){
			$row = $dbr->fetchObject($result);	
			return $row->box_uid;
		} else return false;
	}
	
	function make_wikibox($box){
		global $wgScript, $wgUser;
		$delimiter = "<!--box uid=$box->box_uid-->";		
		$table = '';
		$warning = wfMsg('pleaseDontEditHere');
		$editlink = "[{{SERVER}}{{SCRIPTPATH}}?title=Special:TableEdit&id=$box->box_uid&page=$box->page_uid&pagename={{FULLPAGENAMEE}}&type=".$box->type
		."&template=".str_replace(' ','_',$box->template)
		." ".wfMsg('tableEditEditLink')."]";
		$editlink = "\n|-class='sortbottom'\n|$editlink";
		if ($box->type ==1){
			for ($i = 0; $i < count($box->rows); $i++) if ($box->rows[$i]->is_current) $editlink .= " ||";		
		}else{
			for ($i = 1; $i < $box->colnum; $i++) $editlink .= " ||";
		}
		$editlink .= "\n|}\n";
		$table = TableEditView::make_pre_wikibox($box); 
		$table = str_replace(array('{{{__TOP__}}}','{{{__BOTTOM__}}}','{{{__LEFT_H__}}}','{{{EDITHEADINGS}}}','{{{UL_STYLE}}}'),'',$table);
		$table = preg_replace('/\|\n\{\{\{__LEFT_\d+__\}\}\}\n/','', $table);
		$table = preg_replace('/\{\{\{__HEAD_\d+__\}\}\}/','', $table);
		$table = preg_replace('/\n\|\}\n$/',$editlink, $table);
		return $delimiter.$warning.$box->above.$table.$box->below.$delimiter;
	}
	
	/**
	 * Searches some wiki text for a particular box, returns the portion of the page
	 * that matches (the portion inbetween the box tags.)
	 *
	 * @access public
	 * @param string $wikitext Wikitext of the page.
	 * @param object $box_obj A box object to find on the page.
	 * @return string The wikitext representation of the box, or false on failure.
	 */
	function getTableFromWikitext($wikitext, $box_obj){
		if(!is_object($box_obj)) return false;
		if(!isset($box_obj->box_uid)) return false;
		$tag = "<!--box uid={$box_obj->box_uid}-->";
		$tag = str_replace('.','\.', $tag);				# . escaped in regex	
		$tag = str_replace('<!--','<\!--', $tag);		# ! escaped in regex
		$pattern = "/$tag.*?$tag/is";	
		preg_match($pattern, $wikitext, $matches);
		return (isset($matches[0])) ? $matches[0] : false;
	}	
	
	/**
	 *	Will save a box, remake a page with it and save that page. (a box and page "touch")
	 */
	function save($article, $box){
		$box->save_to_db();
		$new_content = str_replace( 
			$this->getTableFromWikitext($article->mContent, $box), 
			$this->make_wikibox($box), 
			$article->mContent
		);	
		return $article->doEdit($new_content, "Page saved by \"Mirror Annotations\" extension.", EDIT_FORCE_BOT);
	}
	
	function append_and_save($article, $box, $row_data){
		if($article->mContent == "") $article->loadContent();
		$row = $box->insert_row($row_data);
		$this->save($article, $box);
		return $row;
	}

	function delete_and_save( $article, $box, $row ){
		if($article->mContent == "") $article->loadContent();
		foreach($box->rows as $i => $r) {
			if ($row->row_id == $r->row_id) $box->delete_row($index);
		}
		return $this->save($article, $box);
	}
}