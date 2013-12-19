<?php
/*
Jim Hu 10.12.2010
Column rule for a field with a numeric value and a unit type.
Define units in the rule fields, e.g.
<headings>
test||test|units|minutes|seconds|hours
</headings>

Note that this currently only accepts 1 field, so h m s would not work. Use multifield instead.

Units not in the defined list can be created using the "other" menu selection.
Units can have spaces, as in 5 golden rings.  But the unit can't start with "other"
*/
class ecTableEdit_units extends TableEdit_Column_rule{

	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
	}

	function make_form_row(){
		# adjust the menu options
		$options = array_slice($this->rule_fields, 1);
		if(!in_array('other', $options)) $options[] = 'other';
		if(!in_array('', $options)) array_unshift($options,'');
		
		$tmp = preg_split("/\s|\n/", $this->col_data); #echo $this->rdata[$this->col];
		if(isset($tmp[2])){
			array_splice($tmp,1,1); #print_r($tmp);
		}	
		#(empty($tmp[1])) array_unshift($tmp,'other');
		$menu = "<select name = 'field[$this->col_index][]' onchange='this.form.submit();'>";
		if (isset ($tmp[1]) && !in_array($tmp[1], $options)) $options[] = implode(" ", array_slice($tmp, 1));
		foreach ($options as $option){
			$selected = ''; 
			if (!empty($tmp) && ($option == $tmp[1] || $option == implode(" ", array_slice($tmp, 1)))) $selected = 'selected';
			$menu .= "<option label='$option' value='$option' $selected>$option</option>";
			if ($option == 'other' && $tmp[1] == 'other') $menu .= XML::input("field[$this->col_index][]",10);
		}
		$menu .= "</select>";
		$menu = XML::input("field[$this->col_index][]",10,trim(@$tmp[0]), array('maxlength'=>255)).$menu;
		return $menu;
	}

	# overload to make the data a list
	function show_data(){
		$tmp = preg_split("/\s/", trim($this->col_data));
		if(isset($tmp[2]) && $tmp[1] = 'other'){
			array_splice($tmp,1,1); #print_r($tmp);
		}	
		return implode(' ',$tmp);
	}
}