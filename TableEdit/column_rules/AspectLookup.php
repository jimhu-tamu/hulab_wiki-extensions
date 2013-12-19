<?php
# Column rules for TableEdit templates related to GO annotations

# Credits
$wgExtensionCredits['other'][] = array(
    'name'			=> 'TableEdit:AspectLookup',
    'author'		=> '[mailto:bluecurio@gmail.com Daniel Renfro]',
    'description'	=> 'Complete\'s the Aspect given a GO id.',
    'version'		=> '0.1'
);

# Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEditAspectLookup';

function efTableEditAspectLookup( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $wgParser, $wgTitle, $wgUser;

	// skip if this isn't the right column rule
	if (!in_array($rule_fields[0], array( 'AspectLookup')) ) {
		return true;
	}

	// get the index of the column named go_id
	$go_id_rIndex = array_search('go_id', $box->column_names);

	// return if we can't find a column named go_id
	if ( $go_id_rIndex  === false ) {
		$row_data[$i] = "";
		trigger_error('could not find a \'go_id\' column ' . $row_data[$go_id_rIndex], E_USER_WARNING);
		return true;
	}

	// if the go_id column isn't empty...
	if ( isset ($row_data[$go_id_rIndex]) && trim($row_data[$go_id_rIndex]) != "" ) {

		// get a database handle
		$dbr =& wfGetDB( DB_SLAVE );

		// execute some SQL and return an object of the row
		$row = $dbr->selectRow(
			'GO_archive.term',
			'namespace AS aspect',
			'go_id LIKE "%' . trim( $row_data[$go_id_rIndex] ) . '%"',
			__METHOD__,
			array('ORDER BY'=>'term_update DESC', 'LIMIT'=> '1')
		);
		if ( !$row || is_null($row->aspect) || !isset($row->aspect) ) {
			trigger_error('could not find aspect for ' . $row_data[$go_id_rIndex], E_USER_WARNING);
			$type = 'AspectLookup';
			$row_data[$i] = Html::hidden('field[' . $i . ']', "");
			return true;
		}

		// assign to the row
		if ($type == 'EDIT')  {
			$type = 'AspectLookup';
			$row_data[$i] = $row->aspect . Html::hidden('field[' . $i . ']', $row->aspect);
			return true;
		}
		else {
			$type = 'AspectLookup';
		}
	}
	else {
		$type = 'AspectLookup';
		$row_data[$i] = Html::hidden('field[' . $i . ']', "");

	}


	return true;
}
