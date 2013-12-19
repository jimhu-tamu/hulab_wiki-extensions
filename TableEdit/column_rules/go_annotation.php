<?php
# Column rules for TableEdit templates related to GO annotations

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEdit:go_annotation',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Special column type for go annotation completeness',
    'version'=>'0.1'
);

$GO_ANNOTATION_CALLED=0;

# Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEditGOannoColumn';

function efTableEditGOannoColumn( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $wgParser, $wgTitle, $wgUser;

	if (!in_array($rule_fields[0], array( 'go_annotation')) ) return true;

	$col_type = $box->column_names[$i];
	$col = new ecTableEditGOanno($box, $row_data, $i, $col_type, $rule_fields, $type);

	global $GO_ANNOTATION_CALLED;
	$GO_ANNOTATION_CALLED++;
	$row_data[$i] = $col->content();
	if ( $col->getError() && $GO_ANNOTATION_CALLED == 1) {
					    //if ($GO_ANNOTATION_CALLED == 1 ) {
					    //}
		array_unshift( $te->msg, $col->getError() );
	}
	$type = 'go_annotation'; #echo "</pre>";
	return true;
}

class ecTableEditGOanno{

	private $error;

	function __construct($box, $data, $col, $col_type, $rules, $type){
		$this->box = $box;
		$this->rdata = $data;
		$this->col = $col;
		$this->col_type = $col_type;
		array_shift($rules);
		$this->rules = $rules;
		$this->type = $type;
		return true;
	}

	public function getError() {
		return $this->error;
	}

	function content(){

		global $GO_ANNOTATION_CALLED;
		$msg = '';
		# if GO table reference, also need a product name
		if (in_array('product', $this->box->column_names)) $product = trim($this->rdata[array_search('product', $this->box->column_names)]);

		$go_id_column_index = array_search('go_id', $this->box->column_names);
		$go_id = '';
		if (isset($this->rdata[ $go_id_column_index ])) $go_id = trim($this->rdata[ $go_id_column_index ]);

		$e_column_index = array_search('evidence', $this->box->column_names);
		$evidence = '';
		if (isset($this->rdata[$e_column_index])) $evidence = trim($this->rdata[$e_column_index]);
		$tmp = explode(':', $evidence);
		$evidence = $tmp[0];
		$wcodes = array('IPI','IGI','ISS','ISO','ISA','ISM', 'IEA', 'IGC', 'IC');
		switch ($this->col_type){
			case 'with':		

				$wdata = str_replace(":\n",':',$this->rdata[$this->col]); #echo $this->rdata[$this->col];

				/* Kludgy fix to prevent multiple messages printing for the same error. 
				This function is getting called twice for reasons I don't understand.
				*/
				$wlines = explode("\n", $wdata);
				$wlines[] = '';
				$wlines = array_unique($wlines);
				$cnt = 0;
				foreach ($wlines as $wtmp) {
				    if ( trim($wtmp) != '' && strpos($wtmp, ':') === false ) {
					$this->error =
					'<div style="color:red">Free text in the <b>with/from</b> field needs to be entered in the format'
					. '<br /><b>identifier:value</b>, or an identifier must be selected from the pulldown list.'
					. "<br />You entered: <b>$wtmp</b></div>";

					/* Remove the error data from the form */
					//$wdata = preg_replace("/\n$wtmp$/",'',$this->rdata[$this->col]); 
					$wdata = preg_replace("/\n$wtmp$/",'',$wdata);

					/* Remove from $wlines as well so it doesn't create a blank <select> */
					unset($wlines[$cnt]);
				    }
				    $cnt++;
				#	$this->error .= "<pre>";print_r($this->rdata[$this->col], true);echo "</pre>";
				}

				if ($this->type == 'EDIT'){


					if( in_array($evidence, $wcodes)){
				#	return TableEditView::form_field($this->col, $this->rdata[$this->col]);;
						switch ($evidence){
							default:
								$options = $this->rules;
						}
						array_unshift($options,'');
						#$menu = "";

						//$wlines = explode("\n", $wdata);
						//$wlines[] = '';
						//$wlines = array_unique($wlines);
						foreach ($wlines as $wline){
						    $menu = "<select name = 'field[$this->col][]' onchange='this.form.submit();'>";

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
						    $menu.= "</select>".XML::input("field[$this->col][]",10,trim(@$tmp[1]), array('maxlength'=>255));
						    $menu_items[] = $menu;
						    $tmp = null;
						}
						sort($menu_items);
						return implode('<br />', $menu_items);
					}else return Html::hidden('field[]','');
				}
				elseif ($this->type == 'SAVE'){
				/* Get rid of identifiers with no value, 
				otherwise it screws up the data when saving */
				    $wdata = preg_replace("/[^,\n].+:[\n,$]/",'',$wdata);
				}
				$msg = $wdata;
				break;
			case 'status':
				$missing = array();
				$msg = implode(", ",$this->box->column_names);
				if (isset($product) && $product == '') $missing[] = 'Gene product';
				if (!isset($go_id) || $go_id == ''){
					$missing[] = 'GO ID';
				}else{
					$dbr =& wfGetDB( DB_SLAVE );
					$sql = "SELECT * from GO_archive.term WHERE go_id = '$go_id'";
					$result = $dbr->query($sql);
					$x = $dbr->fetchObject ( $result );
					$dbr->freeResult( $result );
					if (!isset($x->go_id)) $missing[] = 'Valid GO ID';
				}
				if (!isset($evidence) || $evidence == '') $missing[] = 'evidence';
				else {
					if (in_array($evidence, $wcodes)	){
							$with = trim($this->rdata[array_search('with', $this->box->column_names)]);
							if (!isset($with) || $with == '') $missing[] = 'with/from';
						}
				}
				$ref = '';
				if (isset( $this->rdata[array_search('refs', $this->box->column_names)])) $ref = $this->rdata[array_search('refs', $this->box->column_names)];
				if (!isset($ref) || $ref == '') $missing[] = 'reference';
				if (!empty($missing)) $msg = "Missing: ".implode(', ',$missing);
				else $msg = "complete";
				break;
			default:
				#$msg = $this->rdata;
		}
		return $msg;
	}

	private function encodeTheDamnWithField( $array ) {
		$a = "";
		for ($i=1, $c=count($array); $i<$c; $i+=2) {
			if ( $array[$i-1] && $array[$i]  )  {
				$a[] = $array[$i-1] . $array[$i];
			}
		}
		return implode( '|', $a );
	}

}
