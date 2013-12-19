<?php

class TableEdit extends SpecialPage{

	private $diagnostics = false;


	function __construct() {
		parent::__construct('TableEdit');
		$this->box_req['box_id'] = '';
		$this->act['view'] = '';
		$this->serialized ='';
		$this->view_history = array('');
		$this->msg = array();
		$this->row_save_ok = true;
		#self::loadMessages();
		return true;
	}

	function execute( $par = null ) {
		global $wgOut, $wgUser;
		# set generic Special Page headers

		try{
			$this->setHeaders();
			$output = $this->initialize();
			if ($this->page_name !=''){
				$this->set_title();
				$this->set_box();
				$this->set_view($this->box);
			}else{
				# catch arrival from Special:Specialpages; page name not set
				# if the page name isn't set, the box won't be set either.
				if(!isset($this->box)) $this->box = new wikiBox;
				$this->act['view'] = 'doc';
			}

			/*
			if($this->box->has_foreign_row && count($this->box->rows) > 1 ) {
				// this box has foreign fields, there should be only one row (or none if none have been made yet.)
				throw new Exception("ForeignTable-BadNumRows");
			}*/

			# branch depending on act invoked
			$output .= TableEditView::text2html($this->box->help1);
			switch ($this->act['view']){
				case 'add_multiple':
					$output .= TableEditView::add_multiple_view($this, $this->box);
					break;
				case 'box_dump':
					$output .= TableEditView::box_dump_view($this, $this->box);
					break;
				case 'conflict':
					$output .= TableEditView::conflict_view($this, $this->box, $this->box2,$this->conflict);
					break;
				case 'doc':
					$output .= TableEditView::doc_view($this->previous_view);
					break;
				case 'edit_headings':
					$output .= TableEditView::edit_head_view($this, $this->box);
					break;
				case 'edit_row':
					$output .= TableEditView::edit_row_view($this, $this->box, $this->row);
					break;
				case 'fix_duplicate_tables':
					$this->fix_duplicate_tables();
					break;
				case 'metadata':
					$output .=TableEditView::metadata_view($this, $this->box);
					break;
				case 'msg':
					$output .= TableEditView::msg_view($this);
					break;
				case 'save':
					# set_view tests for conflicts and redirects if needed
				case 'force_save':
					$this->save_to_page($this->titleObj, $this->box);
					break;
				case 'delete':
					$output .= TableEditView::msg_view($this, wfMsg('confirmDelete'),array('force_delete'=>'delete'));
					break;
				case 'force_delete':
					$this->save_to_page($this->titleObj, $this->box,'delete');
					break;
				case 'revert':
					$output .= TableEditView::msg_view($this, wfMsg('confirmRevert'), array('nav' => 'force_revert'));
					break;
				case 'csv':
					$this->do_csv_download( $this->box );
					break;
				case 'nav':
					//fallthrough
				default:
					$output .= TableEditView::nav_view($this, $this->box);
			}
			$output .= TableEditView::extras($this, $this->box );
			# save state
			$this->save_state();
		} catch (Exception $e){
			$output .= TableEditView::exception_view($this, $e);
		}

		// have to specifically call this method to get the <head> stuff we want injected.
		#wfTableEdit_AddHeadThings( $wgOut, $output);

		$wgOut->addHTML( $output );

		// diagnostics
		if($this->diagnostics) {
			if($this->act['view'] !== 'save') {
				echo '<pre><h1>this</h1><br />'. print_r( $this->box, true) . '</pre>';
			}
		}

		return true;
	}

	# initialization for each load of TableEdit
	# get the request parameters from $wgRequest
	# set the initial output for the top of the page
	function initialize($reenter = false){
		global $wgRequest, $wgUser,$wgServer,$wgScriptPath;
		$output = '';
		$this->browser_tab = $wgRequest->getText('browser_tab');
		$this->act = array(
			'view'   	=> $wgRequest->getText('view'),
			'act'   	=> $wgRequest->getText('act')
			);

		$this->box_req = array(
			'box_id' 	=> $wgRequest->getText('id'),
			'page_uid' 	=> $wgRequest->getText('page'),
			'pagename' 	=> $wgRequest->getText('pagename'),
			'template'  => $wgRequest->getText('template'),
			'type'   	=> $wgRequest->getText('type'),
			'style'   	=> $wgRequest->getText('box_style')
			);

		$this->row_req = array(
			'index' 	=> $wgRequest->getInt('row_index'),
			'owner' 	=> $wgRequest->getText('row_owner'),
			'data'   	=> $wgRequest->getText('row_data'),
			'style'   	=> $wgRequest->getText('row_style')
			);

		$this->new_row = $wgRequest->getText('new_row');

		$this->meta = array(
			'type' => $wgRequest->getText('meta_type'),
			'id' => $wgRequest->getText('meta_id'),
			'data' => $wgRequest->getText('metadata'));

		$this->page_name 	= $wgRequest->getText('pagename');
		$this->field    	= $wgRequest->getArray('field');
		if (is_array($this->field)){
			foreach ($this->field as $key => $value){
				if (is_array($value)) $this->field[$key] = implode("\n",$value);
			}
		}
		$this->bulk_add_form 	= $wgRequest->getText('bulk_add_form');
		$this->bulk_add_file 	= $wgRequest->getText('bulk_add_file');
		$this->style     	= $wgRequest->getText('style');
		$this->conflict    	= $wgRequest->getText('conflict');
		$this->col_index    = $wgRequest->getInt('col_index');
		$this->headings 	= $wgRequest->getText('headings');
		$this->back      	= $wgRequest->getInt('back');
		$ff = trim($wgRequest->getText('ff') );

		# get the serialized version if there is a set action
		# mark the session key for when this browser window was opened - for dealing with multiple tabs open at the same time.
		if (!isset($this->browser_tab) || $this->browser_tab == '') $this->browser_tab = str_replace(' ','_',microtime());
		$this->sesskey = "TableEdit".md5($this->box_req['box_id']).".".$wgUser->getID().$this->browser_tab;
		if(!isset($_SESSION[$this->sesskey])) wfSetupSession();
		if ($this->act['view'] == '' && $ff=='') unset ($_SESSION[$this->sesskey]);
		if (isset($_SESSION[$this->sesskey])) $this->serialized = $_SESSION[$this->sesskey];

		if (isset($_SESSION[$this->sesskey.'.box2'])){
			$this->box2 = new wikiBox;
			$this->box2 = unserialize($_SESSION[$this->sesskey.'.box2']);
		}
		#set the old delimiter (where we expect to see the table on the page)
		$this->url = $wgServer.$wgScriptPath."/index.php?title=Special:TableEdit"
			."&pagename=$this->page_name"
			."&id=".$this->box_req['box_id']
			."&page=".$this->box_req['page_uid']
			."&conflict=$this->conflict"
			."&ff=loaded"
			."&browser_tab=".$this->browser_tab
			;
		$this->old_delimiter = "<!--box uid=".$this->box_req['box_id']."-->";

		# read the view history

		if($this->act['view'] == '') unset($_SESSION[$this->sesskey.'TableEditHist']);
		if (isset($_SESSION[$this->sesskey.'TableEditHist']) && $_SESSION[$this->sesskey.'TableEditHist'] != '') $this->view_history = unserialize($_SESSION[$this->sesskey.'TableEditHist']);
		if($this->back == 1) array_shift($this->view_history);
		$this->previous_view = @$this->view_history[0];

		# reusable links
		$extras = '';
		if (isset($this->row_req['index'])) $extras .="&row_index=".$this->row_req['index'];

		$this->backlink = "<a href='".$this->url."&view=".$this->previous_view.$extras."&back=1'>".wfMsg('back')."</a>";
		# Set the common header
		$link = "<a href='".$this->url."&view=doc'>".wfMsg('helpTableEdit')."</a>";
		if ($this->act['view'] == 'doc') $link = $this->backlink;
		if ($this->page_name == '') $link = '';
		$output .= "\n<h2><span class = 'editsection'>$link</span>".str_replace('_',' ', $this->page_name)."</h2><a id='top'></a>\n";


		#Firefox problem
		if ($this->browser() == 'Firefox' && $this->act['view'] == '' &&  $ff == ''){
			header("Location:". $this->url ."&view=new&foo=" .@$_SESSION[$this->sesskey.'TableEditView']);
		}
		$this->uid = $wgUser->getID();
		if (!$wgUser->isAllowed('edit')) $output .= "<p>".wfMsg('insufficientRights')."</p>";
		$this->debug[__METHOD__] = $output;
		return $output;
	}

	function browser(){
		$agent = wfGetAgent(); #echo $agent;
		if (strpos($agent,'Safari')) return 'Safari';
		if (strpos($agent,'Firefox')) return 'Firefox';
		return '';

	}
	# returns a MW title object
	# has to check that the pagename and the page_uid match and deal with it if it doesn't
	function set_title(){
		# create a temporary title object from the page name
		$title = Title::newFromDBkey($this->page_name);
		if (is_object($title) && $title->exists() ){
			# the title is for a real page
			$this->titleObj = $title;
			return true;
		}
		# if we get here, the title doesn't exist, and we need to throw an error
		throw new Exception('pageNotFound');
	}

	/* Check that the box_uid passed corresponds to the one we want to edit
	potential problems:
	Table was copied within a page - this is the hardest situation... just fail and throw error
	Table was copied from a different page or imported from a different wiki
	Table was made on a new page so that page_uid = 0
	*/
	function set_box(){
		if ($this->box_req['box_id'] == '') throw new Exception('boxNotFound');
		$box = new wikiBox();
		# this should eval false only on the first load from the Edit link
		if($this->act['view'] != '' && $this->act['view'] != 'csv'){
			$box = unserialize( $this->serialized );
			$this->debug['set from'] = 'session';
			$this->box_uid = (isset($box->box_uid)) ? $box->box_uid : null;
		} else {
			# get the current page and check the frequency of the delimiter
			# throw an error if the same table shows up more than once on the page
			$old_page = Revision::newFromTitle($this->titleObj);
			$this->old_page_text = $old_page->getText();
			if(substr_count($this->old_page_text, $this->old_delimiter) > 2 ) throw new Exception('tableDupFound');
			# make a box from the box_id
			$box->box_uid = $this->box_req['box_id'];
			if (!$box->set_from_DB()){
				# the box_uid wasn't found.  Create a new record in the db for the current page_title
				$box->template = $this->box_req['template'];
				$box->page_name = $this->box_req['pagename'];
				$title = Title::newFromText($this->box_req['pagename']);
				$box->page_uid = $title->getArticleID();
				$this->box_req['page_uid'] = $box->page_uid;
				$box->save_to_db();
				#throw new Exception('setfromDBfailed');
			}			
			# check whether the page_name and the page id match
			$titleObj_id = $this->titleObj->getArticleID();
			if($titleObj_id != $this->box_req['page_uid']){
				# if there's a mismatch and this->page_uid > 0, then the table was probably copied or transcluded to a new page
				if ($this->box_req['page_uid'] > 0){
					$title = Title::newFromID($this->box_req['page_uid']);
					if (is_object($title) && $title->exists() ){
						# the title is for a real page
						$this->titleObj = $title;
						$this->page_name = $title->getDBkey();
						$this->msg[] = wfMsg('copiedOrTranscluded');
						$this->set_box(); #recursion!
					}else{
						throw new Exception('page_id_mismatch');
					}
				}else{
					# if page_uid = 0, could still be a problem.  Page name from box and page name from page should match
					$title = Title::newFromDBkey($box->page_name);
					if ($title->getArticleID() != $titleObj_id) throw new Exception('page_name_mismatch');
					# page_id = 0 but we have the right page. Fix the box->page_uid in the database
					$box->page_uid = $titleObj_id;
					$box->save_to_db();
				}
			}
			$this->debug['set from'] = 'db';
		}
		$this->box = $box;

		return true;
	}

	# adjust view based on various cases
	function set_view($box){
		global $wgUser;
		# first load, act not set
		if ($this->act['view'] == '' || $this->act['view'] == 'new'){
			$wiki_box = $this->extract_wiki_table($this->titleObj,$this->box);
			if($this->check_conflict($this->box,$wiki_box) == true){
				$this->act['view'] = 'conflict';
				$this->conflict = 'wiki';
				$this->box2 = $wiki_box;
			}else{
				$this->act['view'] = 'nav';
			}
			return true;
		}

		# adjust in other cases
		switch($this->act['view']){
			case 'add_multiple':
				$this->add_multiple();
				$this->act['view'] = 'nav';
				break;
			case 'box_dump':
				break;
			case 'conflict':
				$owner_uid = $box->rows[$this->row_req['index']]->owner_uid;
				switch($this->act['act']){
					case wfMsg('copy'):
						$this->box->insert_row($this->row_req['data'],$this->row_req['owner'],$this->row_req['style']);
						break;
					case wfMsg('delete'):
						if($owner_uid == $wgUser->getID()  || $owner_uid == 0 || $wgUser->isAllowed('delete') ){
							$this->box->delete_row($this->row_req['index']);
							# not sure this is going anywhere so commented out.
							#$output.= $this->row_req['index']."<br>".wfMsg('rowDeleted')."!!<br>";
						}else return "<br>".wfMsg('wrongOwner')."<br>";
						break;
				}

				break;
			case 'delete':
				#delete table
				if(!$box->user_owned && !$wgUser->isAllowed('delete')){
					$this->msg[] = wfMsg('cantDeleteTable');
					$this->act['view'] = 'msg';
				}
				break;
			case 'doc':
				break;
			case 'edit_headings':
				if($this->back != 1 && !in_array($this->act['act'], array('<','>') ) ){
					# only read new headings if any of the form parts have been edited.
					$box->headings = implode("\n",$this->field);
					$box->heading_style = $this->style;
				}
				# handle different form actions.
				switch ($this->act['act']){
					case wfMsg('addHeading'):
						$box->append_column(wfMsg('newHeading'));
						break;
					case wfMsg('deleteLastHeading'):
						$box->remove_column();
						break;
					case 'v':
					case '>':
						$box->shift_cols($this->col_index, 1);
						break;
					case '^':
					case '<':
						$box->shift_cols($this->col_index, -1);
						break;
					case wfMsg('save'):
						$this->act['view'] = 'nav';
						$box->headings = implode("\n",$this->field);
						$box->heading_style = $this->style;
						$box->is_changed = true;
						$this->act['view'] = 'nav';
						break;
				}
				$this->serialized = $box->get_serialized();
				break;
			case 'edit_row':
				# handle back from metadata
				if(isset($this->back) && $this->back == 1 && isset($this->row_req['index'])){
					$row = $this->box->rows[$this->row_req['index']];
					$this->field = explode("||",$row->row_data);
				}
				# check fields for unclosed tags and invalid content
				#$this->print_obj($this->box->column_rules);die;
				foreach ($this->field as $i => $field){
					$xml_parser = xml_parser_create();
					$search = array('&');
					$replace = array('&amp;');
					xml_parse_into_struct($xml_parser, "<xml>".str_replace($search, $replace, $field)."</xml>", $vals, $index);# $this->print_obj(array($vals,$index));
					xml_parser_free($xml_parser);
					foreach ($index as $tag=>$item) $state[$tag] = 0;
					foreach ($vals as $item){
							if($item['type'] == 'open') $state[$item['tag']]++;
							if($item['type'] == 'close') $state[$item['tag']]--;
					} #$this->print_obj($state);
					foreach($state as $key=>$count){
						if ($count > 0){
							$this->row_save_ok = false;
							$state[$key]--;
							if($key != 'XML'){
								array_unshift( $this->msg, "<span style = 'color:red'>".wfMsg('unclosedTag',$key)."</span>" );
							}else{
								continue;
								//array_unshift( $this->msg, "<span style = 'color:red'>".wfMsg('unclosedTag','')."</span>" );
							}
						}
					}
				}
				
				switch ($this->act['act']){
					case wfMsg('save-row'):
						if ($this->row_save_ok){
							$this->save_row($this->box);
							$this->act['view'] = 'nav';
							break;
						}
					case wfMsg('update'):
					default:
						$this->row = $box->rows[$this->row_req['index']];
						$this->row->row_data = implode('||',$this->field);
						$this->row->row_style = $this->row_req['style'];
						$this->row->row_owner = $this->row_req['owner'];
						break;
				}
				break;
			case 'metadata':
				switch ($this->act['act']){
					case wfMsg('delete'):
						switch ($this->meta['type']){
							case 'box':
								$box->box_metadata[$this->meta['id']]->delete();
								break;
							case 'row':
								$row = $box->rows[$this->row_req['index']];
								$row->row_metadata[$this->meta['id']]->delete();
								break;
						}
						$box->is_changed = true;
						break;
					case wfMsg('add'):
						switch ($this->meta['type']){
							case 'box':
								$box->insert_box_metadata('', $this->meta['data']); $table .= "add box meta";
								break;
							case 'row':
								$row = $box->rows[$this->row_req['index']];$table .= "add row meta";
								$row->insert_row_metadata('', $this->meta['data']);
								break;
						}
						$box->is_changed = true;
						break;
					case wfMsg('revertMeta'):
						$table .= "revert box meta";
						$box->set_metadata_fromDb();
						foreach ($box->rows as $row){
							$box->rows[$row->row_index]->set_metadata_fromDb();
						}
						break;
					default:
						switch ($this->meta['type']){
							case 'box':
								$box->box_metadata[$this->meta['id']]->metadata = $this->meta['data'];
								break;
							case 'row':
								$row = $box->rows[$this->row_req['index']];
								$meta = $row->row_metadata[$this->meta['id']];
								$meta->metadata = $this->meta['data'];
								break;
					}
				}
				break;
			case 'save':
				if( !(isset($this->box->has_foreign_row) && $this->box->has_foreign_row == true)){//&& count($this->box->rows) == 1 && $this->box->has_deleted_rows == false){
					$db_box = new wikiBox;
					$db_box->box_uid = $this->box_req['box_id'];
					if (!$db_box->set_from_DB()) throw new Exception('setfromDBfailed');
					if ($db_box->timestamp > $this->box->timestamp){
						if($this->check_conflict($this->box,$db_box) == true){
							$this->act['view'] = 'conflict';
							$this->conflict = 'db';
							$this->box2 = $db_box;
						}
					}
				}
				break;
			default:
				# in nav view
				$this->process_nav($box);

		}
		return true;
	}

	function process_nav($box){
		global $wgUser;
		# not sure how we get here without a valid box object, but it seems to happen
		if(is_object($box)){
			switch ($this->act['act']){
				#==	row handling ==
				case wfMsg('editHeadings'):
					if ($box->template != '' && $this->get_template_attribute($box->template, 'HEADING_STYLE') !=''){
						$this->msg[] = wfMsg('cantEditHeadings', $box->template);
						$this->act['view'] = 'msg';
						break;
					}
					#store the state when leaving for edit_row view, in case user cancels
					$_SESSION[$this->sesskey.'old_box'] = $box->get_serialized;
					$this->act['view'] = 'edit_headings';
					break;
				case 'restoreheadings':
					#actuallu restore whole box, as manipulating headings may have deleted data
					$box = unserialize($_SESSION[$this->sesskey.'old_box']);
					break;
				#==	row handling ==
				case wfMsg('edit'):
					#store the state when leaving for edit_row view, in case user cancels
					$_SESSION[$this->sesskey.'old_box'] = $box->get_serialized();
					$this->row = $box->rows[$this->row_req['index']];
					$this->act['view'] = 'edit_row';
					break;
				case wfMsg('addData',wfMsg('row')):
				case wfMsg('addData',wfMsg('column')):
					$this->row = $this->box->insert_row('');
					$this->row_req['index'] = $this->row->row_index;
					$this->act['view'] = 'edit_row';
					break;
				# bulk add from an uploaded file
				case wfMsg('addMultiple'):
					$this->act['view'] = 'add_multiple';
					break;
				case wfMsg('copy'):
					$box->insert_row($this->box->rows[$this->row_req['index']]->row_data,$uid);
					$box->is_changed = true;
					break;
				case wfMsg('delete'):
					$owner_uid = $wgUser->getID();
					if($owner_uid == $this->row_req['owner']  || $this->row_req['owner'] == 0 || $wgUser->isAllowed('delete') ) {
						$box->delete_row($this->row_req['index']);
					}else{
						$this->msg[] = wfMsg('wrongOwner');
						return $this->act['view'] = 'msg';
					}
					break;
				case 'restorerow':
					$old_box = new wikiBox;
					$i = $this->row_req['index'];
					$old_box = unserialize($_SESSION[$this->sesskey.'old_box']);
					$box->rows[$i] = $old_box->rows[$i];
					break;
				#==	table handling ==
				case wfMsg('revertToSaved'):
					$this->act['view'] = 'revert';
					break;
				case wfMsg('rotate'):
					$box->type = ($this->box->type + 1)%2 ;
					$box->is_changed = true;
					break;
				case wfMsg('force_revert'):
					$box->set_from_DB();
					$box->is_changed = false;
					unset ($_SESSION[$this->sesskey]);
					break;
				case wfMsg('undeleteRows'):
					foreach ($box->rows as $row) if (is_object($row)) $row->undelete_row();
					break;
				case wfMsg('editBox2',$this->conflict):
					# need to preserve box_id and other box data that can't be recovered from the wiki
					$box->headings = $this->box2->headings;
					$box->heading_style = $this->box2->heading_style;
					$box->box_style = $this->box2->box_style;
					# only replace the unmatched rows
					foreach($box->rows as $row) if (!$row->matched) $row->delete_row();
					foreach($this->box2->rows as $row) if (!$row->matched) $box->insert_row($row->row_data, 0, $row->row_style);

					break;
				case '>':
				case 'v':
					$box->shift_cols($this->col_index, 1);
					$box->is_changed = true;
					break;
				case '<':
				case '^':
					$box->shift_cols($this->col_index, -1);
					$box->is_changed = true;
					break;
				case wfMsg('saveStyles'):
					$box->box_style = $this->box_req['style'];
					$box->heading_style = $this->style;
					$box->is_changed = true;
					break;
			}
		}	
		return;
	}

	# function add from file
	function add_multiple(){
		global $IP,$wgScriptPath, $wgRequest;

		$upload_type = trim($wgRequest->getVal('upload_type'));
		$allowed_types = array(
			'txt' 	=> array('TEXT'),
			'xls'	=> array('OFFICE'),
		);
		$rows = array(); // will get populated with the rows that we're adding

		switch ($this->act['act']){
			case wfMsg('save'):
				$input_data = $this->bulk_add_form;
				$input_data = str_replace("\t","||",$input_data);
				$rows = explode("\n",$input_data);
				break;
			case wfMsg('load'):
				$title = Title::newFromText($this->bulk_add_file);
				$img = wfFindFile($title);
				if ($img === false){
					$this->msg[] = $this->bulk_add_file.' '.wfMsg('notFound');
					return false;
				}
				if(!in_array($img->getMediaType(), $allowed_types[$upload_type] )){
					$this->msg[] = wfMsg('wrongType');
					return false;
				}
				$path = $img->getPath();
		}
		switch($upload_type){
			case 'txt':
				$input_data = file_get_contents($path);
				$input_data = str_replace("\t","||",$input_data);
				$rows = explode("\n",$input_data);
				break;
			case 'xls':
				require_once('excel/reader.php');
				$xls = new SpreadSheet_Excel_Reader();
				// $xls->setOutputEncoding('CP1251');
				$xls->read($path);
				// loop through in 2 dimensions
				for($ii=1; $ii<=$xls->sheets[0]['numRows']; $ii++){
					if(isset($arr_of_fields)) unset($arr_of_fields);
					if(!isset($xls->sheets[0]['cells'][$ii]) || sizeof($xls->sheets[0]['cells'][$ii]) == 0) continue;

					$width_of_data[] = array_pop(array_keys($xls->sheets[0]['cells'][$ii]));
					$max_num_of_cols = count(explode("\n", $this->box->headings));

					for($jj=1; $jj<=$max_num_of_cols; $jj++){
						$arr_of_fields[] = (isset($xls->sheets[0]['cells'][$ii][$jj])) ? $xls->sheets[0]['cells'][$ii][$jj] : " " ;
					}
					$rows[] = implode('||', $arr_of_fields);

					if($width_of_data > $max_num_of_cols) $some_lines_truncated = true;
				}
				if($some_lines_truncated){
					$this->box->above .= <<<END
<br />
<span style="color:red; font-weight:larger">
  <b>Warning: </b>Some lines of the loaded file may have been truncated to fit the table.
</span>
END;
				}
			default:
				break;
		}
		foreach ($rows as $data){
			if (trim($data) != ''){
				$this->box->insert_row($data);
				$this->row_req['index'] = $this->box->rownum;
				$this->save_row($this->box,$data);
			}
		}
		return true;
	}

	# preserves session data on current state
	function save_state(){
		global $wgRequest;
		# save the working and alternate boxes
		if (isset($this->box) && is_object($this->box)) $_SESSION[$this->sesskey] = $this->box->get_serialized();
		if (isset($this->box2) && is_object($this->box2)) $_SESSION[$this->sesskey.'.box2'] = $this->box2->get_serialized();

		#save the views used
		$norecord = array($this->view_history[0],'force_delete','force_save','save','doc');
		if(!in_array($this->act['view'],$norecord)){
			array_unshift($this->view_history, $this->act['view']);
		}
		if ($this->act['view'] == 'conflict' ) $_SESSION[$this->sesskey.'TableEditView'] = $this->act['view'];
		else $_SESSION[$this->sesskey.'TableEditView'] = 'nav';
		$_SESSION[$this->sesskey.'TableEditHist'] = serialize($this->view_history);
		return true;
	}

	# look for an attribute, such as HEADING_STYLE in a table template
	# returns style string or ''
	#
	# ***** $attribute parameter MUST BE IN ALL-CAPS *******
	#
	function get_template_attribute( $template_name, $attribute ){
		$templatePage = Revision::newFromTitle(Title::makeTitle(NS_TEMPLATE, $template_name));
		if (! $templatePage){
			return false;
		}else{
			$template_text = trim($templatePage->getText());
			$template_text = '<xml>'.$template_text.'</xml>';
			$xml_parser = xml_parser_create();
			$parse = xml_parse_into_struct($xml_parser, $template_text, $values, $index);
			xml_parser_free($xml_parser);
			if (isset ($index[$attribute])) return trim($values[$index[$attribute][0]]['value']);
			return false;
		}
	}

	# default is to take data from $this->field array, but allow it to come as a string
	# for add_multiple
	/**
	 *  This method gets data from the $this->field array, but you can specify data ( added for
	 *  the add_multiple() method.)
	 *
	 *  Needs a box object to save on.
	 */
	function save_row( $box, $data = '' ) {
		global $wgUser, $wgReadOnly;

		if ( $wgReadOnly ) {
			return false;
		}

		if ( $data == '' ) {
			$data = implode('||',$this->field);
		}

		// save a copy for later (used in the hook at the bottom of this method.)
		$original_data = $data;

		// handle some very common manipulations
		$data = str_replace("PMID: ","PMID:", $data);

		// ?
		$row = $box->rows[$this->row_req['index']];

		// keep running this until convergence, to handle updates that depend on other updates
		//    eg. column rules that need to run in an order or more than once.
		$row_data_start = '';
		while ($row_data_start != $data){
			$tmp_row_data = '';
			$row_data_start = $data;
			$row_data = explode('||',$data);
			$headings = explode("\n",$box->headings);
			foreach ($headings as $i => $heading){
				if ($i>0) $tmp_row_data .= '||';
				$tmp_row_data .= $this->apply_column_rules($row_data,$box, $row, $i,'SAVE');
				$i++;
			}
			$data = $tmp_row_data;
		}
		$row->row_data = $data;
		$row->row_style = $this->row_req['style'];
		$row->owner_uid = $this->row_req['owner'];
		$row->is_edited = true;
		$box->is_changed = true;

		// gets called whenever anyone clicks "save" on a row...
		// NOT when the row is actually saved to the database.
		wfRunHooks( 'TableEditSaveRow', array( &$this, &$box, &$row, $original_data)  );

		return true;
	}


	/**
	 * Runs the associated code for column rules.
	 *
	 * All modules that hook into this function (TableEditApplyColumnRules hook) should return true. Modify
	 * the variable $row_data[$i] to add or remove functionality. This is where the data in this field lives.
	 *
	 * @param array 	$row_data 	an array of the fields of this row's data
	 * @param object 	$box 		the current box object
	 * @param object 	$row 		the current row object
	 * @param int 		$i  		the field number that we're on in this row
	 * @param string 	$type 		What is going on in TableEdit (??)
	 * @return string 	(??)
	 * @todo Clean this up. This should remain public static...just in case we need to call it externally.
	 */
	public function apply_column_rules($row_data, $box, $row, $i, $type){

		if (!isset($row_data[$i])) $row_data[$i] = '';
		if (isset($box->column_rules[$i])){
			if(isset($row_data[$i]) && trim($row_data[$i]) == '') $row_data[$i] = '';
			# apply rules
			#TableEdit::print_obj($row_data[$i],"row_data $i");
			$rule_fields = $box->column_rules[$i];
			wfRunHooks( 'TableEditBeforeApplyColumnRules', array( &$this, &$rule_fields, &$box, &$row_data, $i, &$type) );
			if (isset($rule_fields[0])){
				switch ($rule_fields[0]){
					# first two cases only are relevant to EDIT mode.  If in SAVE mode, just return the row_data unaltered.
					case 'select':
						$inputs = explode("\n", $row_data[$i]);
						$options = array_slice($rule_fields,1);
						foreach ($options as $k=>$option) $options[$k] = trim($option);

						$tmp = array();
						foreach ( $inputs as $input ) {
							if ( strtolower($input) != 'other' ) $tmp[] = trim($input);
							if (!in_array($input, $options)) $options[] = $input;
						}
						#TableEdit::print_obj($tmp,"tmp row $i");
						if ($type == 'EDIT'){
							$menu = "<select name = 'field[$i]' onchange='this.form.submit();'>";
							$otherbox = '' ;
							if ( empty($tmp) || $tmp[0] !='' && !in_array($tmp[0],$options)) {
								$otherbox = XML::input("field[$i]",10,$tmp[0], array('maxlength'=>255));
							}
							foreach ($options as $option){
								$selected = ''; #echo "-".$tmp[0]."<br>\n$option<br /><br>\n\n";
								if (isset ($row_data[$i]) && !empty($tmp) && $option == $tmp[0]){
									$selected = 'selected';
									#echo "match!!<br />";

								}
								if (strtolower($option) == 'other' && $otherbox != '' ) $selected = 'selected';
								$menu .= "<option label='$option' value='$option' $selected>$option</option>";
							}
							return $menu."</select> $otherbox";
						}

						$row_data[$i] = implode("\n", $tmp);
						return $row_data[$i];
						break;
					case 'checkbox':
							$row_data_list = explode("\n",trim($row_data[$i]));
							foreach ($row_data_list as $j => $row_data_item){
								$row_data_list[$j] = preg_replace('/\*\s*/','',$row_data_item);
							}
						if ($type == 'EDIT'){
							$menu = Html::hidden("field[$i]","");
							$options = array_slice($rule_fields,1);
							foreach ($options as $option){
								$selected = '';
								if (isset ($row_data[$i]) && in_array($option, $row_data_list)) $selected = 'checked';
								$menu .= "<input type='checkbox' name='field[$i][]' label='$option' value='$option' $selected>$option</input>";
							}
							return $menu;
						}
						
						$row_data[$i] = "\n* ".implode("\n* ", $row_data_list);
						return $row_data[$i];
						break;
					case 'text':
						if ($type == 'EDIT') return TableEditView::form_field($i,$row_data[$i], 40,'text');
						return $row_data[$i];
						break;
					case 'calc':
						return self::do_calc($box, array_slice($rule_fields, 1),$row_data);
						break;
					case 'timestamp':
						return date("Y-m-d",$row->timestamp);
						break;
					case 'foreign':
						// we're going to fetch some data from another box/page to mirror here
						# JH 20130527: daniel had field using -> operators, which created it as a stdclass object, which threw
						# strict stds errors. refactored fields as array keys.
						if( !isset($row->field) ) $row->field = array();
						$row->field[$i]['is_foreign'] = true;			// tell this row it's foreign
						$row->field[$i]['editable'] = false;			// default to just show data and not edit it.
						// get options from the row_data
						$options 			= array_slice($rule_fields,1);
						if(count($options) <= 2) throw new Exception('wrongParameters');
						$row->field[$i]['foreign_pagename'] 			= $options[0];
						$row->field[$i]['foreign_table_template'] 	= $options[1];
						$row->field[$i]['foreign_table_heading']		= $options[2];
						if(isset($options[3])) {
							// if we see the "edit" option, make this row editable
							if(strtolower($options[3]) == 'edit')	$row->field[$i]->editable = true;
						}
						// do {{{SIBLING}}} processing to get the correct pagename.
						if(stripos($row->field[$i]['foreign_pagename'], '{{{SIBLING}}}') !== false){
							list($pagename, $pagetype) = explode(':', $box->page_name);
							if(isset($pagename) && $pagename != ""){
								$row->field[$i]['foreign_pagename'] = str_replace('{{{SIBLING}}}', $pagename, $row->field[$i]['foreign_pagename']);
							} else throw new Exception('errorFindingPagenameFromSibling');
						}
						// grab the foreign row data and headings.
						$dbr =& wfGetDB( DB_SLAVE );
						$x = $dbr->selectRow(
							array('ext_TableEdit_box', 'ext_TableEdit_row'),
							array('ext_TableEdit_row.row_data','ext_TableEdit_box.headings', 'ext_TableEdit_row.row_id', 'ext_TableEdit_box.box_uid'),
							array('ext_TableEdit_box.page_name = "' . $row->field[$i]['foreign_pagename'] . '"',
								  'ext_TableEdit_box.template  = "' . $row->field[$i]['foreign_table_template'] . '"',
								  'ext_TableEdit_box.box_id    = ext_TableEdit_row.box_id',
								 ),
							__METHOD__
						);

						// construct the box we're getting data from ($rel_box)
						$rel_box = new wikiBox( $x->box_uid );

						if(is_null($x) || empty($x)) {
							// throw new Exception('errorFindingRow');
							$row->field[$i]['message'] = '<div class="tableEdit_note">';
							$row->field[$i]['message'] .= sprintf(
								"<span style=\"color:red\">Could not find data to mirror on %s</span>",
								TableEdit::create_edit_table_link($rel_box,  $row->field[$i]['foreign_pagename'], $box->page_name)
							);
							$row->field[$i]['message'] .= '</div>';
						} else {
							$foreign_box = new wikiBox($x->box_uid);
							$foreign_box->set_from_db();
							// loop through the headings and when we find one that matches, pull the corresponding data
							$headings = explode("\n", $foreign_box->headings);
							$row_data_fields = explode('||', $x->row_data);
							for($jj=0, $c=count($headings); $jj<$c; $jj++){
								if(strcasecmp($headings[$jj], $row->field[$i]['foreign_table_heading']) == 0){
									$row_data[$i] = $row_data_fields[$jj];
									/*	The relation between the fields needs to be saved, and it would look something like this:
										wikiBoxRow::insert_relation($x->row_id, $jj, $row->row_id, $i);
										BUT, if the current row is new it doesn't have a row_id yet, so we have to defer this until later.
										Put these arguments in an array for storage and set a flag.
									*/
									$this_row_has_a_relation = false;
									if(isset($row->relations) && $row->relations){
										foreach($row->relations as $relation){
											if($jj == $relation->to_field) $this_row_has_a_relation = true;
										}
									}
									if(!$this_row_has_a_relation){
										$row->needs_relations_update = true;
										$row->relations_update[] = array($x->row_id, $jj, $i);	// from_row, from_field, (to_row), to_field
									}
								}
							}
							$row->field[$i]['message'] = '<hr style="width:88%; text-align:center; margin: 0 auto 0 auto;">';
			   				$row->field[$i]['message'] .= sprintf("<div class=\"tableEdit_note\"><b>Note:</b> Data mirrored from '''''%s''''' on '''''%s'''''</div>",
			   					$row->field[$i]['foreign_table_heading'],
			   					TableEdit::create_edit_table_link($rel_box,  $row->field[$i]['foreign_pagename'], $box->page_name)
			   				);
						}


						if ($type == 'EDIT'){
							$stuff_to_display = TableEditView::text2html($row_data[$i]);
							$hidden_version = TableEditView::form_field($i, $row_data[$i], null, 'hidden');
							return $stuff_to_display . $hidden_version;
						} elseif ($type == 'BOT'){
							$stuff_to_display = $row_data[$i];
							return $stuff_to_display;
						}
						break;
					default:
						wfRunHooks( 'TableEditApplyColumnRules', array( &$this, $rule_fields, &$box, &$row_data, $i, &$type) );
						break;
				}
			}
		}

		if ( $type == 'EDIT' ) {
			return TableEditView::form_field($i, $row_data[$i]);
		}
		return $row_data[$i];
	}

	function do_calc($box, $calc_arr, $row_data, $inputstring=''){
		$string = '';
		switch ($calc_arr[0]){
			case 'split':
				$tmp = explode($calc_arr[1],$inputstring);
				$fields = array_slice($calc_arr,2);
				foreach ($fields as $field){
					if(isset($tmp[$field])) $string .= $tmp[$field].' ';
				}
				break;
			case 'reqcomplete':
				$string = 'complete';
				$fields = array_slice($calc_arr,1);
				foreach ($fields as $field){
					for ($i = 0; $i<= $box->colnum; $i++){
						if (isset($box->column_names[$i]) && $box->column_names[$i] == $field && (!isset($row_data[$i]) || trim($row_data[$i]) == '')) $string = 'required field missing';
					}
				}
				break;
			default:
				$string = implode(";", $calc_arr);
		}
		return trim(str_replace('_',' ',$string));
	}


	/**
	 * Compares two box objects and checks for conflicts.
	 * Only check undeleted rows, since these will be deleted from the db on save.
	 * Note that unchecked rows can be recovered, however, if the user chooses to go back to editing.
	 *
	 * @param object $box
	 * @param object $box2
	 * @return bool true for a conflict, false if there isn't one.
	 */
	function check_conflict($box, $box2){
		foreach ($box->rows as $row){
			$row->row_data = str_replace('PMID: ','PMID:', $row->row_data);
			$row->matched = false;
		}
		foreach ($box2->rows as $row) $row->matched = false;
		# check A vs B and B vs A
		$orders = array(array($box,$box2),array($box2, $box));
		foreach ($orders as $order){
			$boxA = $order[0];
			$boxB = $order[1];
			foreach ($boxA->rows as $rowA){
				if (!$rowA->is_current) continue;
				if ($rowA->matched) continue;
				$cleanup = array("\r");
				$a = str_replace($cleanup,'',trim($rowA->row_data));

				foreach ($boxB->rows as $rowB){
					if (!$rowB->is_current || $rowB->matched) continue;
					$b = str_replace($cleanup,'',trim($rowB->row_data));
					#ignore leading and trailing whitespace in row data
					$a = $this->trim_row($a);
					$b = $this->trim_row($b);
				#	echo "<textarea>$a</textarea><textarea>$b</textarea>\n".strcmp($a,$b)."\n</pre>";
				#	$this->print_obj(array($a,$b),'conflict');
					if ($a == $b){
						$rowA->matched = true;
						$rowB->matched = true;
						break;
					}
				}
			}
		}

		foreach ($box->rows as $row) if ($row->matched === false) return true;
		foreach ($box2->rows as $row) if ($row->matched === false) return true;
		return false; # no conflict
	}

	function trim_row($data){
		$tmp = explode ('||',$data);
		foreach ($tmp as $key=>$val) $tmp[$key] = trim($val);
		return implode('||',$tmp);
	}


	/**
	 * Creates the top-level edit table link.
	 * @param object $box The box object of the table to be edited.
	 */
	function create_edit_table_link( $box, $display_text = "", $originating_page = "{{FULLPAGENAMEE}}"){
		if (!is_object($box)) return "";
		if($display_text == "") $display_text = wfMsg('tableEditEditLink'); 	// default to 'edit table'
		$text = "[{{SERVER}}{{SCRIPTPATH}}?title=Special:TableEdit&id=" . $box->box_uid  .
				"&page=" . $box->page_uid .
				"&pagename=" . $originating_page . "&type=" . $box->type .
				"&template=" . str_replace(' ', '_', $box->template) .
				" " . $display_text . "]";
		$text = '<span class="tableEdit_editLink plainlinks">' . $text . '</span>';
		return $text;
	}

	function create_csv_link( $box, $display_text = "", $originating_page = "{{FULLPAGENAMEE}}"){
		if ($display_text == "") $display_text = wfMsg('tableEditEditCSVLink');
		$text = sprintf(
			"[{{SERVER}}{{SCRIPTPATH}}?title=Special:TableEdit&id=%s&page=%s&pagename=%s&type=%s&template=%s&view=csv %s]",
			$box->box_uid,								// id
			$box->page_uid,								// page
			$originating_page,							// page_name
			$box->type,									// type
			str_replace(' ', '_', $box->template),		// template
			$display_text								// text to display
		);
		return $text;
	}

	# converts the prebox to the final for saving to the page
	function make_wikibox( $box, $for_export = false ){
		global $wgScript, $wgUser;

		$table = '';
		if ( !$for_export ) {
			$delimiter = "<!--box uid=$box->box_uid-->";
			$warning = wfMsg('pleaseDontEditHere');
			$editlink = $this->create_edit_table_link($box) ;
			$editlink = "\n|- class=\"tableEdit_footer\" \n|$editlink";
			if ($box->type ==1){
				for ($i = 0; $i < count($box->rows); $i++) if ($box->rows[$i]->is_current) $editlink .= " ||";
			}else{
				for ($i = 1; $i < $box->colnum; $i++) $editlink .= " ||";
			}
			$editlink .= "\n|}\n";
		}
		else {
			$editlink = "\n|}\n";
		}
		$table = TableEditView::make_pre_wikibox($this, $box);
		$table = str_replace(array('{{{__TOP__}}}','{{{__BOTTOM__}}}','{{{__LEFT_H__}}}','{{{EDITHEADINGS}}}','{{{UL_STYLE}}}'),'',$table);
		$table = preg_replace('/\|\n\{\{\{__LEFT_\d+__\}\}\}\n/','', $table);
		$table = preg_replace('/\{\{\{__HEAD_\d+__\}\}\}/','', $table);
		$table = preg_replace('/\n\|\}\n$/',$editlink, $table);

		if ( !$for_export ) {
			wfRunHooks( 'TableEditBeforeSave', array( &$this, &$table, &$box ) );

			// glue everything together into one big string
			$ret = 	$delimiter .
				$warning .
				$box->above .
				$table .
				$box->below .
				$delimiter;
		}
		else {
			$ret = $box->above .
				$table .
				$box->below;
		}
		return $ret;
	}

	# saves box to page, or deletes it from the page
	function save_to_page($title, $box, $action = ''){
		#echo 'save to page';
		global $wgScript, $wgUser, $wgCommandLineMode, $wgReadOnly;
		
		if ( $wgReadOnly  ) {
			return false;
		}

		if ($wgUser->isAllowed('edit') || $wgCommandLineMode){
			if ($action == 'delete'){
				# Need a delete permission! box_>user_owned returns true if all rows are public or belong to the user
				if ($box->user_owned() && !$wgUser->isAllowed('delete')) throw new Exception('not allowed');
				$box->delete_box_from_db();
				$replacement = '';
				$act_msg = wfMsg('tableDeleted');
			} else {
				// update the places where this data is mirrored
				if (isset($box->skip_saving_relations) && !$box->skip_saving_relations) {
					foreach($box->rows as $row){
						if($row->is_current && ($row->is_edited === true || $row->timestamp == "")){
							$current_row_fields = explode('||', $row->row_data);
							// check if we are mirroring this data on some other row...
							if(!empty($row->relations)) {
								// update each mirror
								foreach($row->relations as $relation){
									$x = $this->lookup_box_id_from_relation($relation, $row);
									// create the box object that holds the mirrored data
									$rel_box = new wikiBox();
									$rel_box->setId($x->box_id);
									if(!$rel_box->set_from_DB()) {
										// something went wrong finding the relational box.
										$msg = "Could not set relational box object, rel_id {$relation->rel_id}, box_id {$x->box_id}.";
										trigger_error($msg, E_USER_ERROR);
										throw new Exception($msg);
									}
									// update the correct field in the row data
									foreach($rel_box->rows as &$rb_row){
										if($rb_row->row_id == $relation->to_row){
											$rfields = explode('||', $rb_row->row_data);
											$rfields[$relation->to_field] = $current_row_fields[$relation->from_field];
										}
										$rb_row->row_data = implode('||', $rfields);
									}
									// save the object to the db...
									$rel_box->save_to_db();
									// ...and remake the page and table on it.
									$article = new Article(Title::newFromDBkey(  urldecode($rel_box->page_name)  ));
									$article->fetchContent();
									$new_content = str_replace(
										$this->getTableFromWikitext($article->mContent, $rel_box),
										$this->make_wikibox($rel_box),
										$article->mContent
									);
									$article->doEdit($new_content, "Updating {$rel_box->template} table.", EDIT_FORCE_BOT);
								}
							}
						}
					}
				}
				// finally, save this box and remake this page.
				$box->save_to_db();

				// if we had to defer saving things to the relations table because of a missing row_id, do it now...
				foreach($box->rows as $row){
					if(isset($row->needs_relations_update) ){
						foreach($row->relations_update as $vars){
							list($from_row, $from_field, $to_field) = $vars;
							wikiBoxRow::insert_relation($from_row, $from_field, $row->row_id, $to_field);
						}
					}
				}
				$replacement = $this->make_wikibox($box);
				$act_msg = wfMsg('tableEdited');
			}
			$this->get_old_table($title, $box);
			$new_page = str_replace($this->old_table, $replacement, $this->old_page_text);
			if (trim($this->old_page_text) != trim($new_page)){
				$article = new Article($title);
				# check again that the page doesn't already exist (just in case)
				# hook to edit the new page before saving. For things like category tags.
				wfRunHooks( 'TableEditBeforeArticleSave', array( &$new_page ) );
				$article->doEdit( $new_page, wfMsg('saveMsg',$act_msg, $wgUser->getName()), EDIT_UPDATE );
			}
		} # end if $wgUser->isAllowed
		if (isset($this->box_req['box_id'])) unset($_SESSION[$this->sesskey]);
		$_SESSION[$this->sesskey.'TableEditView'] = '';
		$_SESSION[$this->sesskey] = '';
		# Redirect to the changed page
		$article = new Article($title);
		$article->doRedirect();
		return true;
	}

	function lookup_box_id_from_relation($relation, $current_row){
		// determine which row_id to use as a lookup
		if ($current_row->row_id == $relation->from_row ) {
			$current_row_id_lookup = $relation->to_row;
		} elseif ($current_row->row_id == $relation->to_row ) {
			$current_row_id_lookup = $relation->from_row;
		} else {
			throw new Exception("Nonmatching relation for row_id {$current_row->row_id}, rel_id {$relation->rel_id}.");
		}
		// grab the box_id of the box to update.
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select(
			'ext_TableEdit_row',
			'box_id',
			"row_id = '" . $current_row_id_lookup . "'",
			__METHOD__
		);
		return $dbr->fetchObject($result);
	}



	/**
	 * Returns a box object from what was in the wiki
	 *
	 * @param string $title The title of the page you want to get the table from.
	 * @param object $box ??
	 * @return object A wikibox object
	 */
	function extract_wiki_table($title, $box){
		global $wgUser,$wgParser;
		$old_box = new wikiBox;
		$old_box->page_name = $this->page_name;
		$old_table = $this->get_old_table($title, $box);
		$output = $wgParser->parse(
			$old_table,
			$this->titleObj,
			ParserOptions::newFromUser( $wgUser )
		);
		$output = $wgParser->doTableStuff($old_table);
		preg_match_all ('/\<table(.*)\>/U',$output, $box_style);
		$old_box->box_style = $box_style[1][0];
		preg_match_all ('/\<tr(.*)\>([\S\s\|]*)\<\/tr\>/U',$output, $rows);
		if(strpos($rows[0][0], '<td')) $old_box->type = 1;
		if(isset($rows[0][1]) && strpos($rows[0][1],'<th') ) $old_box->type = 1;
		$old_box->heading_style = $rows[1][0];
		array_pop($rows[2]);
		$data = array();
		foreach($rows[2] as $ykey => $row){
			preg_match_all ('/\<t[hd](.*)\>([\S\s\|]*)\<\/t[hd]\>/U',$row, $cells);
			$style[$ykey][] = trim($rows[1][$ykey]);
			foreach ($cells[0] as $xkey => $cell){
				if(strpos(' '.$cell, '<th')) $headings[] = trim($cells[2][$xkey]);
				else{
					$data[$ykey][$xkey] = trim($cells[2][$xkey]);
					@$style[$ykey][$xkey] .= trim($cells[1][$xkey]);
				}
			}
		}
		$old_box->headings = implode("\n",$headings);
		if ($old_box->type == 1){
			$arr = array();
			foreach($data as $ykey => $xarr)
				foreach ($xarr as $xkey => $value) $arr[$xkey][$ykey] = $value;
			$data = $arr;
			$arr = array();
			foreach($style as $ykey => $xarr)
				foreach ($xarr as $xkey => $value) $arr[$xkey][$ykey] = $value;
			$style = $arr;
		}

		if (isset($data)){
			foreach ($data as $ykey => $rowdata){
				wfRunHooks( 'TableEditCheckConflict', array( &$this, &$rowdata, &$box ) );
				$old_box->insert_row(implode('||',$rowdata),0,implode(' ',array_unique($style[$ykey]) ) );

			}
		}
		return $old_box;
	}

	function parse_wiki_table($table_wikitext,$title){
		global $wgParser, $wgUser;
		$extracted_box = new wikiBox;
		$extracted_box->page_name = $this->page_name;
		$output = $wgParser->parse(
			$table_wikitext,
			$title,
			ParserOptions::newFromUser( $wgUser )
		);
		$output = $wgParser->doTableStuff($table_wikitext);
		preg_match_all ('/\<table(.*)\>/U',$output, $box_style);
		$extracted_box->box_style = $box_style[1][0];
		preg_match_all ('/\<tr(.*)\>([\S\s\|]*)\<\/tr\>/U',$output, $rows);
		if(strpos($rows[0][0], '<td')) $extracted_box->type = 1;
		if(isset($rows[0][1]) && strpos($rows[0][1],'<th') ) $extracted_box->type = 1;
		$extracted_box->heading_style = $rows[1][0];
		# last row is edit table
		array_pop($rows[2]);
		$data = array();
		foreach($rows[2] as $ykey => $row){
			preg_match_all ('/\<t[hd](.*)\>([\S\s\|]*)\<\/t[hd]\>/U',$row, $cells);
			$style[$ykey][] = trim($rows[1][$ykey]);
			foreach ($cells[0] as $xkey => $cell){
				if(strpos(' '.$cell, '<th')) $headings[] = trim($cells[2][$xkey]);
				else{
					$data[$ykey][$xkey] = trim($cells[2][$xkey]);
					@$style[$ykey][$xkey] .= trim($cells[1][$xkey]);
				}
			}
		}
		$extracted_box->headings = implode("\n",$headings);
		if ($extracted_box->type == 1){
			$arr = array();
			foreach($data as $ykey => $xarr)
				foreach ($xarr as $xkey => $value) $arr[$xkey][$ykey] = $value;
			$data = $arr;
			$arr = array();
			foreach($style as $ykey => $xarr)
				foreach ($xarr as $xkey => $value) $arr[$xkey][$ykey] = $value;
			$style = $arr;
		}
		foreach($data as $ykey => $xarr) $extracted_box->insert_row(implode('||',$xarr));

		#$this->print_obj($extracted_box);
		return $extracted_box;
	}

	/**
	 * Get the old page.
	 * This a string corresponding the entire section containing the table between the delimiters.
	 * use extract_wiki_table
	 *
	 * @param string $title
	 * @param ojbect $box ?? is this even used ??
	 */
	function get_old_table($title,$box){
		$old_page = Revision::newFromTitle($title);
		$this->old_page_text = $old_page->getText();
		$delimiter = str_replace('.','\.', $this->old_delimiter);	# . escaped in regex
		$delimiter = str_replace('<!--','<\!--', $delimiter);		# ! escaped in regex
		$pattern = "!$delimiter(.*)$delimiter!isU";
		preg_match ($pattern,$this->old_page_text, $matches);
		# something is there
		$this->old_table = $matches[0]; #echo "<pre>$pattern\n\n";print_r($matches);echo "</pre>";
		return $this->old_table;
	}


	/**
	 * Deal with cases where there are two copies of a table with the same box_uid on the same page
	 */
	function fix_duplicate_tables(){
		global $wgUser;
		$box = new wikiBox;
		$box->box_uid = $this->box_req['box_id'];
		if (!$box->set_from_DB()) throw new Exception('setfromDBfailed');
		#$this->print_obj($box);
		$this->get_old_table($this->titleObj, $box);
		$new_page = $this->old_page_text;
		$pattern = "/".preg_quote($this->old_table)."/";
		while(substr_count($new_page, $this->old_delimiter) > 2 ){
			$box->save_clone();
			$replacement = $this->make_wikibox($box);
			$new_page = preg_replace($pattern, $replacement, $new_page, 1);
		}
	#	$this->print_obj($this->titleObj);
		$article = new Article($this->titleObj);
		$article->doEdit( $new_page, wfMsg('saveMsg','fix duplicate tables', $wgUser->getName()), EDIT_UPDATE );
		$article->doRedirect();
		return true;
	}

	function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache, $egTableEditMessages;
		if ( $messagesLoaded ) return true;
		$messagesLoaded = true;
		require( dirname( __FILE__ ) . '/SpecialTableEdit.i18n.php' );
		#foreach ( $allMessages as $lang => $langMessages ) 	$wgMessageCache->addMessages( $langMessages, $lang );
		#foreach( $egTableEditMessages as $key => $value ) 	$wgMessageCache->addMessages( $egTableEditMessages[$key], $key );
		return true;
	}

	function print_obj($obj,$item = 'debug item'){
		if ($this->act['view'] == 'save') return true;
		echo "<br><br><br>$item:<pre>";
		echo ":";print_r($obj);echo "</pre><br>";
		return true;
	}

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

	function create_box_for_export( $box ){
		global $egTableEditDataTag;
		if(!is_object($box) || get_class($box) !== 'wikiBox') return false;
		return Xml::element($egTableEditDataTag, array("id"=>$box->box_uid, "class"=>"table_data"), $box->get_serialized());
	}

	function do_csv_download( $box ){
		die("This has yet to be implemented.");
	}
/*		$arr = $this->build_2D_array_from_rows( $box );
		if ($box->type == 1) {
			$download = $this->make_csv_from_array($this->rotate_2D_array($arr));
		} else {
			$download = $this->make_csv_from_array($arr);
		}
		$today= date("m.d.y");
		$name = sprintf("%s_%s_%s.csv", $box->page_name, $box->template, $today);
		header("Cache-Control: public"); 										// tell the browser this file is for downloading.
		header("Content-Description: File Transfer");
		header("Content-disposition: attachment; filename={$name}");
		header("Content-Type: txt/plain");
		echo $download;
		die(); # a real die, not diagnostic.
	}

	function build_2D_array_from_rows( $box ) {
		if(!is_object($box) || !isset($box->rows) || count($box->rows) == 0) return false;
		$arr = array();
		for ($i=0, $c=count($box->rows); $i<$c; $i++) {
			$arr[$i] = explode("||", $box->rows[$i]->row_data);
		}
		return $arr;
	}

	function rotate_2D_array( $arr ) {
		$rotated_arr = array();
		for ($i=0, $c=count($arr); $i<$c; $i++) {
			for ($j=0, $k=count($arr[$i]); $j<$k; $j++) {
				$rotated_arr[$j][$i] = $arr[$i][$j];
			}
		}
		return $rotated_arr;
	}

	function make_csv_from_array( $arr ) {
		$csv_str = "";
		for ($i=0, $c=count($arr); $i<$c; $i++) {
			foreach ($arr[$i] as $field) {
				$csv_str .= '"' . str_replace('"', '\"', $field) . '",';		// escape double quotes and wrap in quotes.
			}
			if ($csv_str[strlen($csv_str)-1] == ',') {
				$csv_str = substr_replace($csv_str, "", -1);
			}
			$csv_str .= "\n";
		}
		return $csv_str;
	}
*/

	static function newBox( $pagename, $ns = NS_MAIN, $template = "" ) {
		$box = new wikiBox();
		$box->setPageName( $pagename );
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->selectRow(
			"page",
			array( "page_id" ),
			array( "page_namespace" => $ns, "page_title" => $pagename ),
			__METHOD__
		);
		$box->setPageUid( $result->page_id );
		$box->setTemplate( $template );
		$box->set_headings_from_template();
		$box->box_uid = $box->new_box_uid();
		return $box;
	}

	/**
	 * Reorders the columns of a box obj.
	 *
	 * @param 	array 	$new_column_order_arr 	An array of the names of the columns in the intended order.
	 *											This is based on their definition in the template.
	 *
	 * @return 	object 							The new box object with the columns reordered.
	 */
	function reorder_columns( $box_obj, $new_column_order_arr ) {
		// check for things we need.
		if (!$box_obj || !$new_column_order_arr)
			return false;
		if (!isset($box_obj->template) || trim($box_obj->template) == "")
			return false;

		// get the headings from the template, **attribute must be in ALL-CAPS**
		$headings_from_template = $this->get_template_attribute($box_obj->template, 'HEADINGS');

		list($headings_from_template_hash, $column_names_from_template_arr)
			= $this->parse_template_headings( explode("\n", $headings_from_template) );

		// check if there are the same number of columns and they exist in both arrays somewhere, or die.
		if ( array_diff($column_names_from_template_arr, $new_column_order_arr) ) {
			trigger_error("Inconsistent column names! Make sure there are the same number of columns and the names are spelled correctly.", E_USER_ERROR);
		}

		// get a mapping of where each column exists now
		$mapping = $this->reorder_map( $column_names_from_template_arr, $new_column_order_arr  );

		// clone the box_obj to get something clean to work with
		$new_box_obj = clone($box_obj);

		// A. reassign the column_names member-variable directly
		$new_box_obj->column_names = $new_column_order_arr;

		// B. use the mapping to reorder the data in each row
		foreach ($new_box_obj->rows as $row) {
			$row_fields = explode('||', $row->row_data);
			$row->row_data = implode('||', $this->reorder( $row_fields, $mapping ));
		}

		// C. use the mapping to reorder the headings
		$reordered_headings = array();
		for ($i=0, $c=count($new_column_order_arr); $i<$c; $i++) {
			$reordered_headings[$i] = $headings_from_template_hash[$new_column_order_arr[$i]];
		}
		ksort($reordered_headings); # ABSOLUTELY NECESSARY
		$new_box_obj->headings = implode("\n", $reordered_headings);

		// D. use the mapping to reorder the column_rules
		$new_box_obj->column_rules = $this->reorder($new_box_obj->column_rules, $mapping);

		// E. set that this box has been changed
		$new_box_obj->is_changed = true;

		return $new_box_obj;
	}

	private function parse_template_headings( $headings_arr ) {
		$headings_hash = array();
		$column_names_arr = array();
		foreach ( $headings_arr as $line ) {
			if ( strpos($line, "||") === false ) {
				# there doesn't seem to be any names, throw an error
				trigger_error("There doesn't seem to be any column-names specified in the template", E_USER_ERROR);
			}
			list($heading, $column_specs) = explode('||', $line);
			if ( strpos($column_specs, '|') === false ) {
				if ( trim($column_specs) !== "" ) {
					# there's no single pipes, treat only as a column name
					array_push($column_names_arr, $column_specs);
					$headings_hash[$column_specs] =  $heading;
				}
				else {
					# there's a double pipe with nothing after it...strange.
					trigger_error("Error getting column name for heading \"" . $heading . "\"", E_USER_WARNING);
				}
			}
			else {
				# there are single pipes, the name is the first parameter
				$column_specs = explode('|', $column_specs);
				array_push($column_names_arr, $column_specs[0]);
				$headings_hash[$column_specs[0]] = $heading;
			}
		}
		return array($headings_hash, $column_names_arr);
	}

/*
Needs method documentation
*/
	private function reorder_map( $old_arr, $new_arr ) {
		$mapping = array();
		for ($i=0, $old_c=count($old_arr); $i<$old_c; $i++) {
			for ($j=0, $new_c=count($new_arr); $j<$new_c; $j++) {
				if ($old_arr[$i] == $new_arr[$j]) {
					$mapping[$i] = $j;
					break;
				}
			}
		}
		return $mapping;
	}

	private function reorder( $fields_arr, $mapping ) {
		$reordered_arr = array();
		for ($i=0, $c=count($fields_arr); $i<$c; $i++) {
			$reordered_arr[ $mapping[$i] ] = $fields_arr[$i];
		}
		ksort($reordered_arr);	# ABSOLUTELY NECESSARY
		return $reordered_arr;
	}

	function reorder_template( $template_str, $new_column_order_arr ) {
		// check for things we need.
		if (!$template_str || !$new_column_order_arr) return false;

		// get the article object for this template
		$template = new Article(Title::newFromText($template_str, NS_TEMPLATE));
		if (!$template) return false;

		// pull out an array of the lines in the <headings> tag
		preg_match("/<headings>(.*)<\/headings>/ims", $template->getContent(), $matches);
		if (!isset($matches[1])) {
			trigger_error("Could not find headings in " . $template, E_USER_ERROR);
		}
		$original_headings = explode("\n", trim($matches[1]));

		// do some minor checking to see if these two arrays have the same number of elements
		// this says nothing about what is IN the arrays
		if (count($original_headings) !== count($new_column_order_arr)) {
			trigger_error("Incorrect number of columns specified. Please make sure you've included all the columns and their names are spelled correctly.", E_USER_ERROR);
		}

		// get a mapping of the columns
		list($headings_from_template_hash, $column_names_from_template_arr)
			= $this->parse_template_headings( $original_headings );
		$mapping = $this->reorder_map($column_names_from_template_arr, $new_column_order_arr);

		// map
		$new_headings = array();
		for ($i=0, $c=count($mapping); $i<$c; $i++) {
			$new_headings[$mapping[$i]] = $original_headings[$i];
		}
		ksort($new_headings);

		// implode (turn it into a string) and wrap with the right tags on lines above and below
		$new_headings = "\n<headings>\n" . implode("\n", $new_headings) . "\n</headings>\n";

		// put it into the template
		$new_content = str_replace(
			$matches[0],		// what we matched from above
			$new_headings,
			$template->getContent()
		);

		// and then save the template
		return $template->doEdit($new_content, "Reordering columns.", EDIT_FORCE_BOT | EDIT_UPDATE);
	}

    public static function processAuthorshipHistory( $row_id ) {
        $dbr =& wfGetDB( DB_SLAVE );
        $result = $dbr->select(
            'ext_TableEdit_row_metadata',
            array('row_metadata', 'timestamp'),
            'row_id = ' . $row_id,
            __METHOD__
        );
        $authorship = array();
        while ( $version = $dbr->fetchObject($result) ) {
        	if ($version->timestamp == 0 || is_null($version->timestamp) ) {
        		continue; // trash non-timestamped metadata (maybe a bad idea)
        	}
        	if ( preg_match('/Saved by:(\d+)/i', $version->row_metadata, $m) ) {
        		$authorship[ $version->timestamp ] = $m[1];
        	}
        }
        // sort by the keys (timestamps) in reverse order
        ksort($authorship);
        return ( $authorship )
        	? $authorship
        	: false;
    }

	/**
	 * Returns the first user to edit this row who isn't a Bot. This the criteria for being the owner/editor
	 * of a row/annotation. Returns ONLY the user_id, trashes the associated timestamp
	 *
	 * @return int
	 */
    public function originalRowAuthor( $row_id ) {
    	$authors = self::processAuthorshipHistory( $row_id );
    	if ( !$authors ) {
    		return false;
    	}
		foreach ( $authors as $a ) {
			$u = User::newFromId( $a );
			if ( !$u ) {
				continue;
			}
			if ( $u->isBot() || $u->getId() == 0 || $u->getId() == false || $u->getId() == 2 ) {
				continue;
			}
			return $a;
		}
    }
}
