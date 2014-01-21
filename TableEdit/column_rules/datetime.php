<?php
/*
Column rule for picking datetime timestamps

See the README for other requirements for implementing a new column rule
*/

# the class name must be ecTableEdit_ followed by the name of the column rule
class ecTableEdit_datetime extends TableEdit_Column_rule{
	# the constructor instantiates the object. In this case it could be
	# omitted, but use this to do additional initialization
	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
	}

	/*
	This method overloads make_form_row in the abstract class. Make two input box using the jquery ui datetime
	*/
	function make_form_row(){
		$index = $this->col_index;
		$this->col_data = preg_replace("/^\*/","",$this->col_data); 
		# split the lines and remove the * from each. The value in each line goes into the array $values
		list($date, $time) = preg_split("/\n\**/", $this->col_data."\n\n"); 

		# process rule fields
		foreach($this->rule_fields as $param){
			list($k, $v) = explode('=', "$param=");
			if($v != '') $params[$k] = $v;
		}
	/*
		echo "<pre>$this->col_data\nDate:$date\nTime:$time";
		print_r($params);
		echo "</pre>"; /**/
		
		if($date != ''){ 
			$date = date("Y-m-d", strtotime($date));
		}elseif(isset($params['date'])){
			$date = date("Y-m-d", strtotime($params['date']));
		}
		if($time != ''){
			$time = date("h:i A", strtotime("$time"));
		}elseif(isset($params['time'])){
			$time = date("h:i A", strtotime($params['time']));
		}	
		/*
		
		 
		 */
		 $form = '';
		 $form .= "Date:".XML::input("field[$index][]",20,$date, array('maxlength'=>255))."<br>\n";
		 $form .= "Time".XML::input("field[$index][]",20,$time, array('maxlength'=>255))."<br>\n";

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