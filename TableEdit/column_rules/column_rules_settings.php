<?php
#
# Register column rules using this global array
#
$dir = dirname(__FILE__) . '/';

$te_column_rules = array(
	# each item should be a and a rule_name as key and file address as value
	# child classes should be named ecTableEdit_<tag>
	'test_rule' => $dir . 'test_rule.php',
	'dbxref' => $dir . 'dbxref.php',
#	'expression_conditions' => $dir . 'expression_conditions.php',
	'multifield' => $dir . 'multifield.php',
	#new GO annotation tool replaces multple tags
	'GoTermLookup' => $dir . 'go_annotation2.php',
	'AspectLookup' => $dir . 'go_annotation2.php',
	'go_annotation' => $dir . 'go_annotation2.php',
	'quicklinks'    => $dir . 'quicklinks.php',
	//'coordinates' => $dir . 'featureloc.php',
	//'coordinates' => $dir . 'coordinates.php',
	'units' => $dir . 'units.php',
	'myrule' => $dir . 'myrule.php',
	'datetime'     	=> $dir.'datetime.php'
);


$te_column_rule_preprocessing = array(
		'select' => $dir . 'go_evidence_cacao.php',
	);

$wgAutoloadClasses['TableEdit_Column_rule'] = $dir . 'class.column_rule.php';
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEdit_run_column_rules';
$wgHooks['TableEditBeforeApplyColumnRules'][] = 'efTableEdit_preprocess_column_rules';

/*
	Hook call from SpecialTableEdit.body.php
	wfRunHooks( 'TableEditApplyColumnRules', array( &$this, $rule_fields, &$box, &$row_data, $i, &$type) );
	
	name of the rule is $rule_fields[0] 
	
*/
function efTableEdit_run_column_rules( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $te_column_rules;
	# short circuit if column rule not registered
	if (!array_key_exists($rule_fields[0], $te_column_rules)) return true;
		
	# load the appropriate class
	require_once ($te_column_rules[$rule_fields[0]]);

	#column rule class names must begin with ecTableEdit_ to prevent class name conflicts with other extensions.
	$class = 'ecTableEdit_'.$rule_fields[0];
	#kludge to deal with go_annotation replacing goterm and aspect lookups
	if(in_array($rule_fields[0], array('GoTermLookup','AspectLookup'))){
		$class = 'ecTableEdit_go_annotation';
	} 
	$rule = new $class($te, $box, $rule_fields, $row_data, $i);

	$row_data[$i] = $rule->execute();
	#$test_rule->dump();
	
	# catch errors where a rule returns null
	if(!isset($row_data[$i])){ 
		$row_data[$i] = 'error: column rule returned null';
		# short circuit to allow apply_column_rules to override and and make a regular text box form
		# this should take care of the problems during debugging when a bad column rule shifts the data
		# to the previous column.
		return true;
	}	



	# need to set this to prevent apply_column_rules from overriding and making a regular text box form.
	$type = 'column_rule';
	return true;

}

function efTableEdit_preprocess_column_rules( $te, &$rule_fields, $box, $row_data, $i, $type ){
	global $te_column_rule_preprocessing;
	# do preprocessing if registered - note that this can apply to 
	# column rules that are from the defaults, so this should be before the short-circuit
	if (isset($rule_fields[0]) && array_key_exists($rule_fields[0], $te_column_rule_preprocessing)){
		include ($te_column_rule_preprocessing[$rule_fields[0]]);		
	}
		return true;
}