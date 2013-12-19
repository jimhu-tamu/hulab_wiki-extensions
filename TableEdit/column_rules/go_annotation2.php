<?php
/*
TableEdit column rules to deal with rules for GO annotations 
Example: used by Annotation_headings tables in GONUTS
Registration of this rule is done in TableEdit/column_rules/column_rules_settings.php
*/
	

class ecTableEdit_go_annotation extends TableEdit_Column_rule{

	private $ontology;
	private $dbi;
	private $wcodes = array('IPI','IGI','ISS','ISO','ISA','ISM', 'IEA', 'IGC', 'IC');

	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		global $wgCodePath;
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
		# instantiate classes to register the other classes and provide bases for extension.
		require_once ($wgCodePath.'library/OntologyProject.php');
		$project = new OntologyProject;
		$project->register_class('OntologyArchive2', $wgCodePath.'library/OntologyProject/OntologyDBI/OntologyArchive2.php');
		
		# create db interface. 
		$this->dbi = new OntologyArchive2('obo_archive');
		$this->ontology = $this->dbi->getOntologyByName('go');
		$this->get_term(self::correct_go_id($this->row_hash['go_id']));
		$this->box->row_tooltips = array(
			"Evidence Code" => "Evidence Codes are used to describe the kind of experiment used to infer the function described by the GO term"
		);
	}

	function make_form_row(){
		$index = $this->col_index;
		return $this->{$this->col_name}();	
	}

	# overload to make the data a list
	function show_data(){
		switch($this->col_name){
			case 'with':
				$this->col_data = str_replace(":\n", ":", $this->col_data);
				break;
			case 'status':
				$this->col_data = $this->calc_status();
				break;
			default:	
		}
		return $this->col_data;
	}

	function go_id(){
		$go_id = str_replace(' ','', strtoupper(trim($this->col_data)));
		#fall through if blank
		if($go_id != ''){
			# fall through to check if it looks right already
			if(!preg_match('/^GO:\d{7}$/',$go_id)){
				$new_go_id = self::correct_go_id($go_id);
				if ($new_go_id != $go_id){
					$go_id = $new_go_id;
					$this->error =
						"<div style='color:green'>Guessing that when you wrote $this->col_data you meant...</div>";
				}
			}
			if(trim($go_id) != '' && $this->term->name == ''){
				$go_id = $this->col_data;
				$this->error =
					"<div style='color:red'>$this->col_data doesn't look like a GO id</div>";
			}
		}
		return TableEditView::form_field($this->col_index, $go_id, 40,'text');
	}
	
	static function correct_go_id($go_id){
		if(preg_match('/^(GO:)*\s*(\d{1,7})/', $go_id, $m)){
			$go_id = 'GO:'.str_pad($m[2],7,'0',STR_PAD_LEFT);
		}
		return $go_id;
	}

	function go_term(){
		return $this->term->name.Html::hidden('field[]',$this->term->name);
	}
	
	function evidence_code(){
		$tmp = explode(':', $this->row_hash['evidence']);
		return $tmp[0];
	}
				
	function with(){
		# list of codes needing with
		$evidence = $this->evidence_code();

		$wdata = str_replace(":\n",':',$this->col_data);
		$wdata = str_replace(array("<br>","<br />"),"\n",$wdata);
		$wlines = explode("\n", $wdata);
		$wlines[] = "";
		$wlines = array_unique($wlines);

		$options = $this->rule_fields;
		array_shift($options);
		array_unshift($options,'');
		foreach ($wlines as $wtmp) {
			if ( trim($wtmp) != '' && strpos($wtmp, ':') === false ) {
				$this->error =
				'<div style="color:red">Free text in the <b>with/from</b> field needs to be entered in the format'
				. '<br /><b>identifier:value</b>, or an identifier must be selected from the pulldown list.'
				. "<br />You entered: <b>$wtmp</b></div>";
			}
			if( in_array($evidence, $this->wcodes)){
				#$menu = "";
				foreach ($wlines as $wline){
					$menu = "<select name = 'field[$this->col_index][]' onchange='this.form.submit();'>";
	
					#Allow user to add identifiers by typing free text in the 
					#identifier:value format by splitting on the colon separator
					$tmp = explode(':', $wline); 
	
					/*The textbox contains a value, and the identifier is 
					not already in the options pulldown list */
					//if (isset ($tmp[1]) && !in_array($tmp[0], $options)) { 
					if (isset ($tmp[1]) && !in_array($tmp[0], $options)) { 
						$options[] = $tmp[0];
					} 
	
					foreach ($options as $option){
						$selected = '';
						if (!empty($tmp) && $option == $tmp[0]) $selected = 'selected';
						if ($option != '') $option .= ':';
						$menu .= "<option label='$option' value='$option' $selected>$option</option>";
					}
					$menu.= "</select>".XML::input("field[$this->col_index][]",10,trim(@$tmp[1]), array('maxlength'=>255));
					$menu_items[] = $menu;
				}
				sort($menu_items);
				return implode('<br />', $menu_items);
			}else{
				return Html::hidden('field[]','');
			}	
		}	
	}
	function aspect(){
		$aspect = '';
		switch ($this->term->namespace){
			case 'molecular_function':
				$aspect = 'F';
				break;
			case 'biological_process':
				$aspect = 'P';
				break;
			case 'cellular_component':
				$aspect = 'C';
				break;
		}
		return $aspect.Html::hidden('field[]',$aspect);
	}
	
	function notes(){
		return TableEditView::form_field($this->col_index,trim($this->col_data), 40);
	}
	
	function status(){
		$msg = $this->calc_status();
		return $msg.Html::hidden('field[]',$msg);
	}
	
	function calc_status(){
		$this->update_row_hash();
		$msg = "";
		# if GO table reference, also need a product name
		if (in_array('product', $this->box->column_names)) $product = trim($this->rdata[array_search('product', $this->box->column_names)]);
		$missing = array();
		if (isset($product) && $product == '') $missing[] = 'Gene product';
		if ($this->row_hash['go_id']  == ''){
			$missing[] = 'GO ID';
		}else{
			# look this up from term, not row_hash, because the go_term is updating on the same refresh cycle
			if (trim($this->term->name)  == '') $missing[] = 'Valid GO ID';
		}
		if ($this->row_hash['evidence'] == '') $missing[] = 'evidence';
		else {
			if (in_array($this->evidence_code(), $this->wcodes)	){
				if (trim($this->row_hash['with']) == ''){ 
					$missing[] = 'with/from';
				}else{
					#print_r($this->row_hash['with']); 
				}	
			}
		}
		$ref_fields = explode(':',$this->row_hash['refs']);
		if (trim($this->row_hash['refs']) == '' || !isset($ref_fields[1]) || trim($ref_fields[1]) == ''){
			$missing[] = 'reference';
		}			
		if (!empty($missing)){ 
			$msg = "Missing: ".implode(', ',$missing);
		}else{
			$msg = "complete";
		}	
		return $msg;
	}
	
	function get_term($id){
		$this->term = new OntologyTerm; #$id = 'GO:0000003';
		$this->term->setTag('id', $id);
		$x = $this->dbi->getLastTermRevision($this->term);
		if (is_object($x)) $this->term->setStanza($x->term_text);
	}

}