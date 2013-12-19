<?php

function help() {
	echo <<<USAGE
USAGE:

% php {$_SERVER['SCRIPT_NAME']}
    --wiki      -w      Specifies a path to the wiki.
    --dry-run   -d      Do a dry-run, don't actually delete anything.
    --verbose   -v      Print more explicit messages.

USAGE;
	exit;
}


/**
 * This is a script I grabbed online.
 * Very useful for parsing commandline paramters *correctly*.
 * Parses $GLOBALS['argv'] for parameters and assigns them to an array.
 * Supports:
 *   -e
 * 	-e <value>
 * 	--long-param
 *	--long-param=<value>
 *	--long-param <value>
 *	<value>
 *
 * @param array $noopt List of parameters without values ($GLOBALS['argv'])
 * @see <http://us3.php.net/manual/en/function.getopt.php#83414>
 */
function fnParseParameters($noopt = array()) {
	$result = array();
	$params = $GLOBALS['argv'];
	// could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
	reset($params);
	while (list($tmp, $p) = each($params)) {
		if ($p{0} == '-') {
			$pname = substr($p, 1);
			$value = true;
			if ($pname{0} == '-') {
				// long-opt (--<param>)
				$pname = substr($pname, 1);
				if (strpos($p, '=') !== false) {
					// value specified inline (--<param>=<value>)
					list($pname, $value) = explode('=', substr($p, 2), 2);
				}
			}
			// check if next parameter is a descriptor or a value
			$nextparm = current($params);
			if ( !in_array($pname, $noopt)
				  && $value === true 
				  && $nextparm !== false 
				  && $nextparm{0} != '-'
			) {
				list($tmp, $value) = each($params);
			}
			$result[$pname] = $value;
		} else {
			// param doesn't belong to any option
			$result[] = $p;
		}
	}
	return $result;
}

$params = fnParseParameters( $GLOBALS['argv'] );

if ( isset($params['w']) || isset($params['wiki']) ){
	$wikipath = ( isSet($params['wiki']) ) 
		? $params['wiki']
		: $params['w'];
} else {
	help();
	exit();
}
require_once( $wikipath . "/maintenance/commandLine.inc" );

$verbose = false;
if ( isset($params['v']) || isset($params['verbose']) ){
	$verbose = ( isSet($params['verbose']) ) 
		? $params['verbose']
		: $params['v'];
}

$dry_run = false;
if ( isset($params['d']) || isset($params['dry-run']) ){
	$dry_run = ( isSet($params['dry-run']) ) 
		? $params['dry-run']
		: $params['d'];
}

// connect to the database
$dbw =& wfGetDB( DB_MASTER );

// get a list of all boxes in the database
$box_table_result = $dbw->select(
	'ext_TableEdit_box',
	'box_id'
);

$deleted_boxes = 0;
$boxes_checked = 0;

// foreach entry in the database 
while ( $row = $dbw->fetchObject($box_table_result) ) {

	// make a box object
	$box =  wikiBox::newFromBoxId( $row->box_id );

	// check if this box is used on the page it says it is
	if ( !$box->isUsed() ) {
		if ($verbose) { 
			print 'Found unused box: ' . $box->box_uid . ", deleting.\n";
		}
		if ( !$dry_run ) {
			$box->delete();
			$deleted_boxes++;
		}
		else {
			print 'Would have deleted box ' . $box->box_uid . " (".$box->page_name." - ".$box->template.")\n";
		}
	}
	else {	
		
		if ($verbose) {
			//print $box->box_uid . ' is used on ' . $box->page_name . ' as the ' . $box->template . " table\n";
		}
	
		// check that the logically page_uid = page_name
		if ( $box->parentArticle()->getTitle()->getDBkey() !== $box->page_name ) {
			if ($verbose) {
				print 'page_name does not match page_uid for box ' . $box->box_id . ", fixing\n";
				print "  \$box->page_name   = \"".$box->page_name."\"\n";
				print "  \$page->page_title = \"".$box->parentArticle()->getTitle()->getDBkey()."\"\n";
			}
			$box->page_name = $box->parentArticle()->getTitle()->getDBkey(); 
			if ( !$dry_run ) {
				$box->save();
			}
			else {
				print 'Would have set page_name to "'.$box->parentArticle()->getTitle()->getDBkey().'" for box "'.$box->box_uid."\"\n";
			}
		}
		
	}
	
	if ($verbose) {
		print "\n";
	}
	
	$boxes_checked++;
}

print $boxes_checked . " boxes were checked.\n";
print $deleted_boxes . " boxes were removed from the database.\n";
