<?php
# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEdit:coordinates',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Special column type for genome coordinates',
    'version'=>'0.1'
);


// Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = new ecTableEditCoordinateColumn;

// Define the class that gets run at the hook.
class ecTableEditCoordinateColumn {


	function onTableEditApplyColumnRules( $te, $rule_fields, $box, $row_data, $i, $type ) {
		global $wgRequest, $wgTitle;

		if ($rule_fields[0] != 'coordinates') return true;

		if ($wgRequest->getVal('use_form') == "1") {
			$this->coordA = ($wgRequest->getVal( 'coordA' )) ? $wgRequest->getVal( 'coordA' ): null;
			$this->coordB = ($wgRequest->getVal( 'coordB' )) ? $wgRequest->getVal( 'coordB' ): null;
			$this->strand = ($wgRequest->getVal( 'strand' )) ? $wgRequest->getVal( 'strand' ): null;
		} else {
			preg_match('{
					     :				# a colon
						 \s*			# any/no whitespace
						 (\d+)			# save a digit
						 \.\.			# two literal periods
						 (\d*)			# save another digit
						 \s*			# any/no whitespace
						 \(?			# a possible open parenthesis
						   ([-+]?)		# save a possible plus or minus char
						 \)?			# a possible closing parenthesis
						}xms', $row_data[$i], $matches);
			$this->coordA = isset($matches[1]) ? $matches[1] : null;
			$this->coordB = isset($matches[2]) ? $matches[2] : null;
			$this->strand = isset($matches[3]) ? $matches[3] : null;
		}

		$error_message = "";
		if (isset($this->coordA) && !is_numeric($this->coordA)) {
			$error_message = '<span style="color:red; font-weight:bolder;">';
			if (isset($this->coordb) && !is_numeric($this->coordB))
				$error_message .= "Neither of the coordinates you entered were numeric.";
			else
				$error_message .= "The coordinate you entered was not numeric.";
			$error_message .= '</span><br />';
		}

		# $this->action = $wgTitle->getLocalURL();
		list($url, $query) = explode('?', $te->url);
		$this->action = wfAppendQuery( $url, $query );

		if ($type == "SAVE"){
			$row_data[$i] = sprintf(
				"%s:%d..%d %s",
				$row_data[0],
				$this->coordA,
				$this->coordB,
				($this->strand) ? "(" . $this->strand . ")" : ""
			);
			$row_data[$i] .= '<br />' . sprintf("<gbrowse_img>%s|%d|%d</gbrowse_img>", $row_data[0], $this->coordA, $this->coordB);
			return true;
		}

		$row_data[$i] = $error_message . $this->make_form();
		$type = 'coord';		# necessary to interpret the HTML
		return true;
	}

	function make_form() {
		$html = "";
		$html .= Xml::openElement( 'form', array('id'=>'filter_page_by', 'method'=>'GET', 'action'=>$this->action) );
		//$html .= wfInput( "coordA", 10, ($this->coordA) ? $this->coordA : "", array('maxlength'=>255) );
		$html .= Xml::input( "coordA", 10, ($this->coordA) ? $this->coordA : "", array('maxlength'=>255) );
		$html .= " .. ";
		//$html .= wfInput( "coordB", 10, ($this->coordB) ? $this->coordB : "", array('maxlength'=>255) );
		$html .= Xml::input( "coordB", 10, ($this->coordB) ? $this->coordB : "", array('maxlength'=>255) );
		$html .= Xml::openElement( 'select', array('name'=>'strand') );
		$options = array(' ', '+', '-');
		//foreach ($options as $option){
			//$html .= XML::option(
				//$option,
				//$value,
				//($this->strand == $option) ? 'selected="selected"' : ""
			//);
		//}
		foreach( $options as $label => $value ) {
			$html .= Xml::option(
				$label,
				$value,
				($this->strand == $label) ? 'selected="selected"' : ""
			);
		}
		$html .= Xml::closeElement( 'select' );
		//$html .= wfHidden( "use_form", "1");
		$html .= Html::hidden( "use_form", "1");
		$html .= Xml::closeElement( 'form' ); echo "<pre>";  #print_r($this);echo "</pre>";

		return $html;
	}

}

/*
function efTableEditCoordinateColumn( $te, $rule_fields, $box, $row_data, $i, $type ){
	if ($rule_fields[0] != 'coordinates') return true;
	$r = str_replace("..", "\n", $row_data[$i]);
	preg_match('/(\d+)\s+(\d*)\s+([-+]?)/', $r, $matches);

echo "<pre>";
var_dump($row_data[$i], $matches);
echo "</pre>";


	if (!is_numeric($matches[1]))
		$te->msg[] = "<span style = 'color:red'>$matches[1] " . wfMsg('is_not_number',$key) . "</span>";
	if (isset($matches[2]) && !is_numeric($matches[2]))
		$te->msg[] = "<span style = 'color:red'>$matches[2] " . wfMsg('is_not_number',$key) . "</span>";

	if ($type == "SAVE"){
		$row_data[$i] = $matches[1];
		if (isset($matches[2]) && is_numeric($matches[2]))
			$row_data[$i] .= "..".$matches[2];
		if (isset($matches[3]) && $matches[3] != '')
			$row_data[$i] .= "(".$matches[3].")";
		if(isset($row_data[0]) && $row_data[0] != '')
			$row_data[$i] .= <<<EOD
<br /><gbrowse_img>$row_data[0]
$matches[1]
$matches[2]
</gbrowse_img>
EOD;
		return true;
	}

	// build the form
	$text  = "";
	$text .= XML::input("field[$i][]", 10, $matches[1], array('maxlength'=>255));
	$text .= " .. ";
	$text .= XML::input("field[$i][]", 10, $matches[2], array('maxlength'=>255));

	$menu = "<select name = 'field[$i][]'>";
	$options = array('','+','-');
	foreach ($options as $option){
	$selected = '';
		if (isset ($matches[3]) && $option ==$matches[3]) $selected = 'selected';
		$menu .= "<option label='$option' value='$option' $selected>$option</option>";
	}
	$menu.= "</select>";

	$row_data[$i] = $text.$menu;
	$type = "coord";
	return true;
}
*/
