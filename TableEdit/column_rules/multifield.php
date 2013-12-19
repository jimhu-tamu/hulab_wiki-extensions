<?php
class ecTableEdit_multifield extends TableEdit_Column_rule{

	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
        $this->keys = array_slice($this->rule_fields, 1);
	}

	function make_form_row(){
		$index = $this->col_index;
		$this->col_data = preg_replace("/^\*/","",$this->col_data);
		$value = preg_split("/\n\*?/", $this->col_data);
		$form = "<table>";
		foreach ($this->keys as $i =>$label){
			$val = isset($value[$i]) ? $value[$i] : '';
			$form .= "<tr><td>$label</td><td>".XML::input("field[$index][]",40,str_replace("$label: ",'',$val), array('maxlength'=>255))."</td></tr>\n";
		}
		$form .= "</table>";
		return $form;
	
	}

	# overload to make the data a list
	function show_data(){
		$tmp = str_replace("\n*","\n",trim($this->col_data));
		$tmp = preg_replace("/^\*/","",$tmp);
        $fields = explode("\n",$tmp);
		$value = explode("\n",$tmp);
        foreach ($fields as $i =>$value){
	    //NML: why is this here?
            //var_dump( $i, $value, $this->keys[$i], $fields[$i]  );

            if (strpos(" $value", $this->keys[$i].":") == 0) $fields[$i] = $this->keys[$i].": $value";
        }
        $this->col_data = "*".implode("\n*",$fields);
		return $this->col_data;
	}

}
