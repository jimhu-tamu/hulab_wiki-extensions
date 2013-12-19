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

	if (!in_array($rule_fields[0], array( 'subtilis_strains_lookup')) ) return true;

	// get a databaes handle
	$dbr =& wfGetDB( DB_SLAVE );

	// pull out the SQL to run, query

	$result = $dbr->query(
		"SELECT DISTINCT(page_title), cl_to
		FROM page
		  INNER JOIN categorylinks ON ( page_id = cl_from  )
		WHERE page_namespace = 14
		  AND cl_to LIKE \"B._subtilis_genomes\""
	);


	if ($dbr->numRows($result) == 0 ) {
		return true;
	}

	$subtilis_genomes = array();
	$plasmid_genomes = array();
	$phage_genomes = array();
	$prophage_genomes = array();
	$remaining_genomes = array();

	while ($row = $dbr->fetchObject($result)) {
		$value = str_replace('Gene_List:', "", $row->page_title);
		$value = str_replace('_', ' ', $value);

		// might have to switch into different arrays when things get more complicated

		array_push( $subtilis_genomes, $value );

	}
	$subtilis_genomes = array_unique( $subtilis_genomes );
	natcasesort( $subtilis_genomes );

	$menu = "<select name='field[$i]'>";
	$menu .= '<option value=" "> </option>';
	$menu .= '<optgroup label="B. subtilis Genomes">';
	foreach ( $subtilis_genomes as $genome ) {
		$selected = (isset($row_data[$i]) && $genome == $row_data[$i])
			? 'selected="selected"'
			: '';
		$menu .= sprintf(
			'<option value="%s" %s>%s</option>',
			$genome,
			$selected,
			$genome
		);
	}
	$menu .= '</optgroup>';
	$menu .= "</select>";

	if ($type == 'EDIT')  {
		// we are still in the EDIT view... someone/thing submitted the form for update
		$row_data[$i] = $menu;
		$type = 'subtilis_strains_lookup';
		return true;
	}
	else {
		// we're through and want the literal data, not the dropdown


		global $wgRequest;
		$submitted_field_values = $wgRequest->getArray('field');
		$row_data[$i] = $submitted_field_values[$i];

		$type = 'subtilis_strains_lookup';
	}
	return true;
}
