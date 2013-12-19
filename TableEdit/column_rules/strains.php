<?php
# Column rules for TableEdit templates related to GO annotations

# Credits
$wgExtensionCredits['other'][] = array(
    'name'			=> 'TableEdit:strains',
    'author'		=> '[mailto:bluecurio@gmail.com Daniel Renfro]',
    'description'	=> 'Special column type for creating a dropdown list of strains.',
    'version'		=> '0.1'
);

# Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEdit_strains';

function efTableEdit_strains( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $wgParser, $wgTitle, $wgUser;

	if (!in_array($rule_fields[0], array( 'strains_lookup')) ) return true;
	
	// get a databaes handle
	$dbr =& wfGetDB( DB_SLAVE );
	
	// pull out the SQL to run, query 
	
	$result = $dbr->query( "SELECT page_title, cl_to 
							FROM page 
							  INNER JOIN categorylinks ON ( page_id = cl_from  ) 
							WHERE page_namespace = 14 
							  AND page_title LIKE \"Gene_List:%\""
					);
	
	if ($dbr->numRows($result) == 0 ) {
		return true;
	}
	
	$ecoli_genomes = array();
	$plasmid_genomes = array();
	$phage_genomes = array();
	$prophage_genomes = array();
	$remaining_genomes = array();
	
	while ($row = $dbr->fetchObject($result)) {
		$value = str_replace('Gene_List:', "", $row->page_title);
		if ( $row->cl_to == 'E._coli_genomes' ) {
			array_push( $ecoli_genomes, str_replace("_", " ", $value)  );
		}
		else {
			if ( preg_match('/plasmid/i', $value) ) {
				array_push( $plasmid_genomes, str_replace("_", " ", $value)  );
			} else if ( preg_match('/^Phage/', $value) ) {
				array_push( $phage_genomes, str_replace("_", " ", $value)  );
			} else if ( preg_match('/ProPhage/i', $value) ) {
				array_push( $prophage_genomes, str_replace("_"," ", $value) );
			} else {
				array_push( $remaining_genomes, str_replace("_"," ", $value) );
			}
		}
	}
	$phage_genomes = array_unique( $phage_genomes );
	natcasesort( $phage_genomes );

	$prophage_genomes = array_unique( $prophage_genomes );
	natcasesort( $prophage_genomes );

	$remaining_genomes = array_unique( $remaining_genomes );
	natcasesort( $remaining_genomes );

		
	//$menu = "<select name ='field[$i]'>";
$onchange = <<<EOD
var lastInput = function (e) {
  if (e !== undefined) {
    window.organismOrStrainInput = e;
  } else if (window.organismOrStrainInput === undefined) {
    window.organismOrStrainInput = '';
  } 
  return window.organismOrStrainInput;
};
(function (e) {
  var value = e.options[e.selectedIndex].value;
  if (value == 'other') {
    var input = document.createElement('input');
    input.value = lastInput();
    input.onkeyup = function () {
      var text = this.value;

      if (text == '') 
	this.name = ''
      else if (this.name != 'field[$i]') 
	this.name = 'field[$i]';
    };

    e.parentNode.appendChild(input);
  } else {
    var c = e.parentNode.getElementsByTagName('input')[0];
    if (c !== undefined) {
      lastInput(c.value);
      e.parentNode.removeChild(c);
      var brs = e.parentNode.getElementsByTagName('br');
      if (brs.length > 1) 
	e.parentNode.removeChild(brs[0]);
    }
  }
})(this);
EOD;

	$selectFlag = 0;
	$menu = "<select name ='field[$i]' onchange=\"$onchange\">";
	$menu .= '<option value=" "> </option>'; 
	$menu .= '<optgroup label="E. coli Genomes">';
	foreach ( $ecoli_genomes as $genome ) {
		$selected = '';
		if (isset($row_data[$i]) && $genome == $row_data[$i]) {
		  $selected = 'selected="selected"';
		  $selectFlag = 1;
		}
		$menu .= sprintf(
			'<option value="%s" %s>%s</option>', 
			$genome, 
			$selected,
			$genome
		);
	}
	$menu .= '</optgroup>';

	$menu .= '<optgroup label="Plasmids">';
	foreach ( $plasmid_genomes as $genome ) {
		$selected = '';
		if (isset($row_data[$i]) && $genome == $row_data[$i]) {
		  $selected = 'selected="selected"';
		  $selectFlag = 1;
		}
		$menu .= sprintf(
			'<option value="%s" %s>%s</option>', 
			$genome, 
			$selected,
			$genome
		);
	}	
	$menu .= '</optgroup>';
	
	$menu .= '<optgroup label="Phage Genomes">';
	foreach ( $phage_genomes as $genome ) {
		$selected = '';
		if (isset($row_data[$i]) && $genome == $row_data[$i]) {
		  $selected = 'selected="selected"';
		  $selectFlag = 1;
		}
		$menu .= sprintf(
			'<option value="%s" %s>%s</option>', 
			$genome, 
			$selected,
			$genome
		);
	}	
	$menu .= '</optgroup>';

	$menu .= '<optgroup label="Prophage Genomes">';
	foreach ( $prophage_genomes as $genome ) {
		$selected = '';
		if (isset($row_data[$i]) && $genome == $row_data[$i]) {
		  $selected = 'selected="selected"';
		  $selectFlag = 1;
		}
		$menu .= sprintf(
			'<option value="%s" %s>%s</option>', 
			$genome, 
			$selected,
			$genome
		);
	}	
	$menu .= '</optgroup>';

	$menu .= '<optgroup label="Other">';
	foreach ( $remaining_genomes as $genome ) {
		$selected = '';
		if (isset($row_data[$i]) && $genome == $row_data[$i]) {
		  $selected = 'selected="selected"';
		  $selectFlag = 1;
		}
		$menu .= sprintf(
			'<option value="%s" %s>%s</option>', 
			$genome, 
			$selected,
			$genome
		);
	}	
	$menu .= '</optgroup>';

    $selected = '';
    $input = '';
    if ($selectFlag == 0) {
      if ($row_data[$i] == 'other') {
	$selected = 'selected="selected"';
      }
$onkeyup = <<<EOD
(function (e) {
  var text = e.value;

  if (text == '') 
    e.name = ''
  else if (e.name != 'field[$i]') 
    e.name = 'field[$i]';
})(this);
EOD;
      if (isset($row_data[$i]) && $row_data[$i] == 'other') {
	$input = "<br><input onkeyup=\"$onkeyup\"></input>"; 
      } elseif ($selectFlag == 0 && $row_data[$i] != '') {
	$selected = 'selected="selected"';
	$input = "<br><input onkeyup=\"$onkeyup\" name='field[$i]' value='$row_data[$i]'></input>";
      }
    }
    $menu .= "<option $selected>other</option>";
    //$menu .= '<option>other</option>';

	$menu .= "</select>$input";

	if ($type == 'EDIT')  {
		// we are still in the EDIT view... someone/thing submitted the form for update
	
		$row_data[$i] = $menu;
		$type = 'foo';
	}
	else {
		// we're through and want the literal data, not the dropdown
		
		global $wgRequest;
		if ( isSet($row_data[$i]) || $wgRequest->getArray('field') ) {
			$field_values = $wgRequest->getArray('field');
			if ( isSet($field_values[1]) ) {
				$row_data[$i] = $field_values[1];
			}
		}
	}		
	return true;
}
