<?php
#
# Register column rules using this global array
#
$dir = dirname(__FILE__) . '/';

# each item should be a and a rule_name as key and file address as value
# child classes should be named ecTableEdit_<tag>
# defining this way allows other code modules to add column rules to the array even if they 
# execute before TableEdit.php
$te_column_rules['test_rule']    = $dir . 'test_rule.php';
$te_column_rules['dbxref' ]      = $dir . 'dbxref.php';
$te_column_rules['multifield']   = $dir . 'multifield.php';
#new GO annotation tool replaces multiple tags
$te_column_rules['GoTermLookup'] = $dir . 'go_annotation2.php';
$te_column_rules['AspectLookup'] = $dir . 'go_annotation2.php';
$te_column_rules['go_annotation']= $dir . 'go_annotation2.php';
$te_column_rules['quicklinks' ]  = $dir . 'quicklinks.php';
//'coordinates'   => $dir . 'coordinates.php',
$te_column_rules['units']        = $dir . 'units.php';
$te_column_rules['myrule']       = $dir . 'myrule.php';
$te_column_rules['datetime']     =  $dir . 'datetime.php';
$te_column_rules['obo_term']     =  $dir . 'obo_term.php';
#$te_column_rules['rule1'] ='/Volumes/home/shabnam/trunk/2013tutorial/wiki-extensions/shabnam/rule1.php';

$wgAutoloadClasses['TableEdit_Column_rule'] = $dir . 'class.column_rule.php';
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEdit_run_column_rules';

/*
	Hook call from SpecialTableEdit.body.php
	Hooks::run( 'TableEditApplyColumnRules', array(  $this, $rule_fields, $box, $row->row_id, $row_data, $i, &$type );
	
	name of the rule is $rule_fields[0] 
	
*/
function efTableEdit_run_column_rules( $te, $rule_fields, $box, $row_id, &$row_data, $i, &$type ){
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
	$rule = new $class($te, $box, $rule_fields, $row_id, $row_data, $i);

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
