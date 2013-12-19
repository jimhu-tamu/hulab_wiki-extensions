<?php


class ecTableEdit_myrule extends TableEdit_Column_rule{
	
	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
	}

	
	function make_form_row(){
		$index = $this->col_index;
		$this->col_data = preg_replace("/^\*/","",$this->col_data);
		$value = preg_split("/<li></li>\n\*?/", $this->col_data);
		
		$form .= "<li>" .XML::input("field[$index][]",40, $value, array('maxlength' =>200))."<br>\n";
		$form .= "<li>" .XML::input("field[$index][]",40, $value, array('maxlength' =>200))."<br>\n";
		
		return $form;
	
	}

	
	function show_mydata(){
		$tmp = str_replace("\n*","\n",trim($this->col_data));
		$tmp = preg_replace("/^\*/","",$tmp);
		$value = explode("\n",$tmp);
		if(count($value) > 1) $this->col_data = "*".implode("\n*",$value);
		echo $this->col_data."\n";
		return $this->col_data;
	}




}