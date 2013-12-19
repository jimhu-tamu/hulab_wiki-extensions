<?php
# Column rules for TableEdit templates related to GO annotations

# Credits
$wgExtensionCredits['other'][] = array(
    'name'			=> 'TableEdit:GoTermLookup',
    'author'		=> '[mailto:bluecurio@gmail.com Daniel Renfro]',
    'description'	=> 'Complete\'s the GO term given a GO id.',
    'version'		=> '0.1'
);

# Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEditGoTermLookup';

function efTableEditGoTermLookup( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $wgParser, $wgTitle, $wgUser;

	// skip if this isn't the right column rule
	if (!in_array($rule_fields[0], array( 'GoTermLookup')) ) return true;

	// get the index of the column named go_id
	$go_id_rIndex = array_search('go_id', $box->column_names);

	// return if we can't find a column named go_id
	if ( $go_id_rIndex === false ) {
		$row_data[$i] = "";
		trigger_error('could not find a \'go_id\' column ' . $row_data[$go_id_rIndex], E_USER_WARNING);
		return true;
	}

	// make sure the GO id is formatted correctly
	if ( isset( $row_data[$go_id_rIndex]) && !preg_match('/GO:\s*\d{7}/', $row_data[$go_id_rIndex]) && trim($row_data[$go_id_rIndex]) != "" ) {
		$row_data[$i] = 'Bad GO ID' . Html::hidden('field[' . $i . ']',"");
	}

	// if the GO column isn't empty...
	if ( isset($row_data[$go_id_rIndex]) && $row_data[$go_id_rIndex] != "" ) {

		// get a database handle
		$dbr =& wfGetDB( DB_SLAVE );

		// execute some SQL and return an object of the row
		$row = $dbr->selectRow(
			'GO_archive.term',
			'page_title AS go_term',
			'go_id LIKE "%' . trim($row_data[$go_id_rIndex]) . '%"',
			__METHOD__,
			array('ORDER BY' =>  'term_update DESC', 'LIMIT' => 1)
		);
		if ( !$row || is_null($row->go_term) || !isSet($row->go_term) ) {
			trigger_error('could not find go_term for ' . $row_data[$go_id_rIndex], E_USER_WARNING);
			$type = 'GoTermLookup';
			$row_data[$i] = Html::hidden('field[' . $i . ']', "");
			return true;
		}

		// process the info
		list($go_id, $term_name) = explode('_!_', $row->go_term);
		$term_name = str_replace('_', ' ', $term_name);

		// assign to the row
		if ($type == 'EDIT') {
			$type = 'GoTermLookup';
			$row_data[$i] =  $term_name . Html::hidden('field[' . $i . ']', $term_name);
			return true;
		}
		else {
			$type = 'GoTermLookup';
			// don't put anything in $row_data[$i] because it will show up on the page!
		}
	}
	else {
		$type = 'GoTermLookup';
		$row_data[$i] = Html::hidden('field[' . $i . ']', "");
	}

	return true;
}