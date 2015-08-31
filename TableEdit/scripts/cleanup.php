<?php
/*
Delete unused tableEdit tables from the database.

Problem situations and bugs in TableEdit can lead to tables that are no longer in the wiki to remain in the database.
This script deletes these and their rows.

By default it does all tables, but it can be used specifying only tables that use a particular template.
*/
$params = getopt( "w:" );
set_IP($params['w']);
$maintClass = "CleanupTableEdit";

class CleanupTableEdit extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->parse_parameters();
	}

	/*
	The guts of the object... do the maintenance task;
	*/
	public function execute() {
		$limit = 0;
		if($this->getOption('limit')){
			$limit = $this->getOption('limit');
		}
		$template = $this->getOption('table');
		$result = self::getTablesToCheck($template, $limit);
		$missingBoxes = $missingPages = 0;
		foreach ($result as $x){
			#check if page exists
			$box = wikiBox::newFromBoxId($x->box_id);
			$title = Title::newFromID($box->page_uid);
			if(is_null($title) || !$title->isKnown()){
				#echo "$x->page_name doesn't exist at $box->page_uid from box $x->box_id\n";
				$title = Title::newFromText($box->page_name);
				if(is_null($title) || !$title->isKnown()){
					echo "$x->page_name doesn't exist at $box->page_uid or $box->page_name from box $x->box_id\n";
					$missingPages++;
				}else{
					echo "For page $x->page_name box has missing or incorrect page_uid: box: $box->page_uid vs title:".$title->getArticleId()."\n";
					# fix box page_uid
					$box->page_uid = $title->getArticleId();
				}
			}
			if(self::checkTableExists($title, $box)){
				#echo "$box->box_id exists on $box->page_name\n\n";
			}else{
				echo  "$box->box_id not found on $box->page_name. Deleting...\n\n";
				$box->delete();
				$missingBoxes++;
			}
		}
		echo "$missingPages boxes from missing pages\n";
		echo "$missingBoxes boxes not found on tested pages\n";
	}

	
	static function checkTableExists(Title $title, $box){
		# apply column rules	
		$tableEdit = new TableEdit;
		$wikipage = new WikiPageTE($title);
		$old_content = $wikipage->getContent();
		$tag = "<!--box uid=$box->box_uid-->";
		$tag = str_replace('.','\.', $tag);				# . escaped in regex
		$tag = str_replace('<!--','<\!--', $tag);		# ! escaped in regex
		$pattern = "/$tag(.*)$tag/is";
		preg_match($pattern, $old_content, $matches);
		$string = $tableEdit->make_wikibox($box);		
		if(isset($matches[0]) && $matches[0] != ''){
			return true;
		}else{
			#echo "$pattern\n";
		}
		return false;
	}
	
	static function getPagesFromList($page_list){
		$pagesToTouch = array();
		if(is_file($page_list)){
			# open and loop
			$infile = fopen ($page_list, 'r');
			while (!feof($infile)){
				$page_name = trim(fgets($infile, 4096));
				if (trim($page_name) == '') continue;
				$pagesToTouch[] = $page_name;
			}
		}
		return $pagesToTouch;	
	}
	
	
	static function getTablesToCheck($template = '', $limit = 0){
		# query the MW db for all pages with the template	
		# adjust tables if necessary
		$tables = array('ext_TableEdit_box');
		# adjust query if necessary
		$conds = array();
		if($template != ''){
			$conds = array("template = '$template'");
		}	
		$options = array();
		if($limit > 0){
			$options = array('LIMIT'=>$limit);
		}
		# do query
		$dbr = wfGetDB( DB_SLAVE );		
		$result = $dbr->select(
			$tables,
			'*',
			$conds,
			__METHOD__,
			# for testing: comment out in final
			$options
		);
		return $result;
	}

 
	/*
	Use the built-in functions in the Maintenance class
	
	add a block of lines of the form
	$this->addOption( $name, $description, $required = false, $withArg = false, $shortName = false ) 
	
	This allows us to use the getter method $this->getOption($name);, and also automatically adds to the help text
	See maintenance/Maintenance.php for what the arguments mean.
	
	*/
	private function parse_parameters(){
	#	$this->addOption( "pagelist", "file with pages to edit", $required = false, $withArg = true, $shortName = 'p' );	
		$this->addOption( "table", "table template to define pages to edit", $required = false, $withArg = true, $shortName = 't' );	
		$this->addOption( "limit", "limit on how many to do (for testing)", $required = false, $withArg = true, $shortName = 'l' );	
	#	$this->addOption( "config", "file with code to change settings", $required = false, $withArg = true, $shortName = 'c' );	
	}
}

require_once( RUN_MAINTENANCE_IF_MAIN );

/*
Function to set the global variable $IP and include the abstract
class Maintenance
*/
function set_IP($path){
	global $IP;
	if ( isset($path) && is_file("$path/maintenance/Maintenance.php") ){
		$IP = $path;
		require_once( $IP . "/maintenance/Maintenance.php" );
		return $path; 
	} else {
		die ("need -w <path-to-wiki-directory>");
	}
	
}