<?php
/*
This file is an example column rule illustrating how to extend class TableEdit_Column_rule

See the README for other requirements for implementing a new column rule
*/

# the class name must be ecTableEdit_ followed by the name of the column rule
class ecTableEdit_test_rule extends TableEdit_Column_rule{
	# the constructor instantiates the object. In this case it could be
	# omitted, but use this to do additional initialization
	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
	}

	/*
	This method overloads make_form_row in the abstract class.  In this case it takes column data
	that is in wiki markup for an unordered list with three items, splits it up, and makes three
	input text boxes, one for each row in the list.
	*/
	function make_form_row(){
		$index = $this->col_index;
		# remove the * at the start of the first line
		$this->col_data = preg_replace("/^\*/","",$this->col_data);
		# split the lines and remove the * from each. The value in each line goes into the array $values
		$values = preg_split("/\n\*?/", $this->col_data);
		# pad the values array to make sure there are 3 entries
		$values = array_pad($values, 3, "");
		
		/*
		make three input boxes. TableEdit takes row input from an input array named field a 
		value for a particular field[$index] can be an array
			field[$index][] is the field name
			40 is the length of the box
			$value is the value for the ith line
		 
		 */
		 $form = ''; #initialize
		 foreach($values as $i => $value){
			$form .= "$i:".XML::input("field[$index][]",40,$value, array('maxlength'=>255))."<br>\n";
		}
		return $form;
	
	}

	# overload to make the data a list
	function show_data(){
		$tmp = str_replace("\n*","\n",trim($this->col_data));
		$tmp = preg_replace("/^\*/","",$tmp);
		$value = explode("\n",$tmp);
		if(count($value) > 1) $this->col_data = "*".implode("\n*",$value);
		#echo $this->col_data."\n";
		return $this->col_data;
	}




}