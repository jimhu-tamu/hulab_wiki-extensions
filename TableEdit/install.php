<?php
/*
	TableEdit v0.8 Schema Updater
      written by Daniel Renfro

	This updater will take an external TableEdit Database and move it into 
	the MediaWiki database schema, so you can use MW's framework to handle 
	all the querying, etc. 
	
	As of now, it makes MyISAM tables, but you can change what gets made by
	editing the *.sql files in the sql/ directory. 

*/



# ========= functions ========================================
    function parseParameters($noopt = array()) {
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
                if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-') list($tmp, $value) = each($params);
                $result[$pname] = $value;
            } else {
                // param doesn't belong to any option
                $result[] = $p;
            }
        }
        return $result;
    }
    
#
# Read and execute SQL commands from a file
#
function new_dbsource( $fname, $db = false ) {
	if ( !$db ) {
		// Try $wgDatabase, which is used in the install and update scripts
		global $wgDatabase;
		if ( isset( $wgDatabase ) ) {
			$db =& $wgDatabase;
		} else {
			// No? Well, we must be outside of those scripts, so use the standard method
			$db =& wfGetDB( DB_MASTER );
		}
	}
	$error = $db->sourceFile( $fname );
	if ( $error !== true ) {
		throw new Exception($error);
	}
}

function add_table( $name, $patch ) {
	global $wgDatabase;
	if ( $wgDatabase->tableExists( $name ) ) {
		echo "...$name table already exists.\n";
		return false;
	} else {
		echo "Creating $name table...";
		new_dbsource( $patch, $wgDatabase );
		echo "ok\n";
		return true;
	}
}

function mkdir_recursive($pathname, $mode) {
    is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
    return is_dir($pathname) || @mkdir($pathname, $mode);
}



# =========== main ==================================

// turn off annoying Notices. 
error_reporting(E_ALL & ~E_NOTICE);

// check commandline options
if (!isset($argv[1])){
	echo "USAGE:
	php5 update_schema.php [OPTIONS] 
	
	-w <path to wiki> 
		Specifies a path to the wiki. Make sure AdminSettings.php exists and 
		has the correct information.
		
	-u	MySQL user to override AdminSettings.php
	
	-p	MySQL user's password (assumes -u)
";
	exit;
}

// do the command-line flag options stuff
$options = getopt('td:w:u:p');
$params = parseParameters($options);

$te_path = dirname(__FILE__);

if (isset($params['w'])){
	$wikipath = $params['w'];
} else {
	die("Please use the -w flag to specify a path to a wiki.\n");
}
$is_test = isset($params['t']);

if(isset($params['u'])){
	$db_user = $params['u'];
	if(isset($params['p'])){
		if($params['p'] === true){
			echo "Enter password";
		   $ostty = `stty -g`;
		   system(
				   "stty -echo -icanon min 1 time 0 2>/dev/null || " .
				   "stty -echo cbreak"
					);
		   echo "$prompt: ";
		   // Get rid of newline when reading stdin
		   $db_user_password = substr(fgets(STDIN), 0, -1);
		   echo "\n";
		   system ("stty $ostty");
		} else {
			$db_user_password = $params['p'];
		}
	} else {
		die("Use -p to enter password.\n");
	}
} else {
	if (is_file("$wikipath/AdminSettings.php")) {
		require_once ("$wikipath/AdminSettings.php");
	}else{
		die("Can't find AdminSettings.php in $wikipath\n");
	}
	$db_user = $wgDBadminuser;
	$db_user_password = $wgDBadminpassword;
}
require_once ("$wikipath/maintenance/commandLine.inc");

// Do some pre-emptive checking
if( !isset( $db_user ) || !isset( $db_user_password ) ) {
	echo( "No superuser credentials could be found. Please provide the details\n" );
	echo( "of a user with appropriate permissions to update the database. See the documentation.\n" );
	exit();
}


$version 		= false;			# holds the current version of TableEdit, if found/installed
$old_version 	= false;			# is this version older than 0.8 (the major change in db schema)
$action			= false;			# what are we going to do in this script?


// attempt to get the TableEdit version
global $wgExtensionCredits, $wgTableEditDatabase;
foreach ($wgExtensionCredits['specialpage'] as $extension) {
	if ($extension['name'] == "TableEdit") {
		if (isset($extension['version'])) {
			$version = $extension['version'];
		}
	}
}
if ($version) {
	if (preg_match("/^(\d+).(\d+)(.\d+)?/", $version, $m)){
		if ($m[1] == "0" && preg_match("/^[01234567]/", $m[2])) {
			$old_version = true;
			die("---WARNING-------------
	
The version for the installed TableEdit is $version.
This installation probably has an old database schema that is
external to the MediaWiki installation. You might want to 
migrate the data by hand. It looks like your tableEdit data is
installed in the MySQL database \"$wgTableEditDatabase\". 
");
		}
	}
}

// Attempt to connect to the database as a privileged user
// This will vomit up an error if there are permissions problems
$dbclass = 'Database' . ucfirst( $wgDBtype ) ;
$wgDatabase = new $dbclass( $wgDBserver, $db_user, $db_user_password, $wgDBname, 1 );

if( !$wgDatabase->isOpen() ) {
	die( "A connection to the database could not be established. Check the values of \$wgDBadminuser and \$wgDBadminpassword.\n");
} 

// Attempt to connect to the TableEdit database as a privileged user. 
if( !isset($wgTableEditDatabase) && !$version ){
	echo "It seems TableEdit is not installed for $wgSitename in $wikipath.\n";
	echo "Install empty tables in the MW schema? (Y/N)  ";
	$install_new = strtolower(trim(fgets(STDIN)));
	if ($install_new == 'n') {
		die("Install cancelled.\n\n");
	}
	$action = 'install';
} 
else if ( isset($wgTableEditDatabase) || $old_version ) {
	echo "It seems TableEdit is installed in it's own database ($wgTableEditDatabase)\n";
	echo "and needs to integrated into the MediaWiki database for $wgSitename.\n\n";
	echo "If you know you have an \"integrated\" schema, you might want to look\n";
	echo "in your LocalSettings.php for the \$wgTableEditDatabase variable and delete it.\n\n";
	echo "Merge tables into the MW schema? (Y/N)  ";
	$install_new = strtolower(trim(fgets(STDIN)));
	if ($install_new == 'n') {
		die("Install cancelled.\n\n");
	}
	$action = 'merge';
}
else if ( !isset($wgTableEditDatabase) && $version && !$old_version) {
	echo "It seems TableEdit is already installed!\n";
	echo "Try and upgrade TableEdit? (Y/N)  ";
	$install_new = strtolower(trim(fgets(STDIN)));
	if ($install_new == 'n') {
		die("Update cancelled.\n\n");
	}
	$action = 'update';		
}
else {
	die("Something odd happened with TableEdit.\n You can check http://gmod.org/wiki/TableEdit for help.\n");
}

$table_mapping = array(
	'box' 				=> 'ext_TableEdit_box',
	'row' 				=> 'ext_TableEdit_row',
	'box_metadata' 		=> 'ext_TableEdit_box_metadata',
	'row_metadata'  	=> 'ext_TableEdit_row_metadata',
	'relations'			=> 'ext_TableEdit_relations'
);

/*
	Now everything is set up and we can use the native MediaWiki functions to do our updating.
*/   

switch ($action) {
	case 'install':
		$count = 0;
		foreach($table_mapping as $old_table => $new_table){
			if( !$is_test ){
				$ret = add_table( $new_table, "$te_path/sql/{$old_table}.sql" );
			}
			if ($ret) $count++;
		}
		echo "Done! {$count} of " . count($table_mapping) . " tables installed.\n";	
		break;
	case 'merge':
		echo("Updating $wgTableEditDatabase => $wgDBname ... \n");
		$is_made = mkdir_recursive("$te_path/tmp", 0777);
		$chmod = chmod("$te_path/tmp", 0777);
		if( !$is_made || !$chmod) throw new Exception("Could not create writable tmp directory.");

		foreach($table_mapping as $old_table => $new_table){
			if ( !$wgDatabase->tableExists($new_table) ) {
				add_table( $new_table, "$te_path/sql/{$old_table}.sql" );
				$teDatabase->selectDB($wgTableEditDatabase);
				if($teDatabase->tableExists($old_table)){
					$res = $teDatabase->select(array($old_table), array("count({$old_table}_id)"));
					$row = $teDatabase->fetchRow($res);
					$x = $row["count({$old_table}_id)"];
					echo("Moving $x records from {$wgTableEditDatabase}.{$old_table} to {$wgDBname}.{$new_table}...");
					$sql = "SELECT * FROM {$old_table} INTO OUTFILE '$te_path/tmp/{$old_table}.dump.sql'";
					if( !$teDatabase->query($sql)){
						throw new Exception("The admin user $db_user does not have sufficient privileges.");
					} else {
						// dumped to file. read into new table
						$sql = "LOAD DATA INFILE '$te_path/tmp/{$old_table}.dump.sql' INTO TABLE {$new_table}";
						if($wgDatabase->query($sql)) {
							echo("ok.\n");
						} else {
							throw new Exception("Could not read temp file.");
						}
						unlink("$te_path/tmp/{$old_table}.dump.sql");
					}
				}
			} else {
				echo("$new_table already exists ");
				$res = $wgDatabase->select(array($new_table), array("count({$old_table}_id)"));
				$row = $wgDatabase->fetchRow($res);
				$y = $row["count({$old_table}_id)"];
				echo("with $y records in it. Skipping $new_table.\n");
			}
		}
		rmdir("$te_path/tmp");
		echo("\nDone!\n");
		echo(" Don't forget to edit your LocalSettings.php to remove \$wgTableEditDatabase,\n");
		echo(" and delete the old TableEdit database if you're sure you don't need it anymore.\n");		
		break;
	case 'update':
		echo "As of now there is no code written to update the existing database tables for\n";
		echo "TableEdit. If you have access to the MySQL database you can do this by hand. Sorry.\n";
		die();
		break;
	default:
		die ("wtf?");
		break;
}

?>