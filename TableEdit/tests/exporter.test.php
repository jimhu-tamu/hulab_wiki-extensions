<?php
// usage
if (!isset($argv[1])){
	echo "php5 {$_SERVER['SCRIPT_NAME']} [ options ]
     -w [ path to wiki]             The wiki you are interested in.
     -t [ type CSV,XML,IFALT ]		Type of dump of the table.
";
     
	exit;
}

// explicit requires
$path = "/usr/local/phpwikibots/trunk/";
require_once($path . "epoch.php");

// get some options from the commandline
$tools = new EW_Common_Tools;
$params = $tools->parseParameters($GLOBALS['argv']);
if (isset($params['w'])){
	$wikipath = $params['w'];
} else {
	die("Please use the -w flag to set a path to the wiki.\n");
}
$type = (isset($params['t']))
	? $params['t']
	: null;

require_once("$wikipath/maintenance/commandLine.inc");


// get a new page 
$page = new EW_Element( "lacZ:Quickview" );
if (!$page) {
	trigger_error("Bad page for " . $row->page_name, E_USER_WARNING);
	continue;
}

$exporter = TableEdit_Exporter::getInstance( $page->tables['New_Quickview_Table'], $type);
print $exporter->export();

	
?>