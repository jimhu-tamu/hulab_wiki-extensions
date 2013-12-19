<?php

/*
	TableEdit_Loader
		author: 	Daniel Renfro
		version:	0.5
		date:		July 2008


*/
class TableEdit_Loader {

	public $wgUser;							// user object to assign stuff to
	
	protected $page_name;
	protected $page_title_obj;
	protected $page_article_obj;
	protected $page_content;
	
	protected $page_tempalte_name;
	protected $page_template_content;
	
	protected $table_template_name;
	protected $table_template_content;
	protected $box_obj;
	
	protected $start_time;						// holds timestamp of script execution
	protected $verbose = false;					// tell me more about what's happening
	protected $line_count;						// 
	protected $error_count;						// num of errors that have occured
	protected $log_errors = true;				// log failed rows of input in a file for later viewing
	protected $debug = false;
	
	public $misc_column_definitions = array();	// set this up for later. 
	
	// sets variables, etc.		
	function __construct( ){
		global $wgSitename, $wgTableEditDatabase, $wgDBadminuser, $wgDBadminpassword;
		
		// determine database schema type.
		if(class_exists('wikiBox')){
			$dbr =& wfGetDB( DB_SLAVE );
			if(!isset($wgTableEditDatabase)) {
				if($dbr->tableExists('ext_TableEdit_box')) {
					$this->schema_type = 'internal';
				}
			} elseif (isset($wgTableEditDatabase)) {
				$this->schema_type = 'external';
			} else {
				die("There was an error determining the TableEdit schema type in $wgSitename ($wiki_dir).");
			}
		} else {
			die("It seems TableEdit is not installed for $wgSitename ($wiki_dir).");
		}
		
		// set up a persistent database connection to the right database.
		if($this->schema_type == 'external'){
			$this->db = Database::newFromParams( 'localhost', $wgDBadminuser, $wgDBadminpassword, $wgTableEditDatabase);
			if( !$this->db ){
				die("Could not select $this->schema_type database \"$wgTableEditDatabase.\" (" . __METHOD__ . ")\n");
			}
			$this->box_table = 'box';
			$this->box_metadata_table = 'box_metadata';
			$this->row_table = 'row';
			$this->row_metadata_table = 'row_metadata';
		} else {
			$this->db = wfGetDB( DB_MASTER );
			$this->box_table = 'ext_TableEdit_box';
			$this->box_metadata_table = 'ext_TableEdit_box_metadata';
			$this->row_table = 'ext_TableEdit_row';
			$this->row_metadata_table = 'ext_TableEdit_row_metadata';
		}
		
		// set up other properties
		$this->start_time = time();
		
		// set the user 
		$this->wgUser = User::newFromName('Wikientrybot');
		$this->wgUser->load();
	}
	
	function setVerbose(){
		$this->verbose = true;
	}
	
	function setUser( $username){
		$this->wgUser = User::newFromName($username);
		$this->wgUser->load();
	}
	
	function debug( $bool){
		global $wgShowExceptionDetails, $wgShowSQLErrors, $wgDebugDumpSQL;
		$this->debug = true;
		if($bool === true) {
			$wgShowExceptionDetails = true;
			$wgShowSQLErrors = true;
			$wgDebugDumpSQL = true;
		}
		return;
	}
	
	function printInfo(){
		global $wgTableEditDatabase, $wgDBname, $wgSitename;
		$text =  "Sitename:      $wgSitename\n"; 
		$text .= "Schema type:   $this->schema_type\n";
		$db = ($this->schema_type == 'internal') ? $wgDBname : $wgTableEditDatabase;
		$text .= "T.E. Database: $db\n";
		$text .= "User ID:       {$this->wgUser->getID()} ({$this->wgUser->getName()})\n";
		if($this->debug){
			$text .= "Debug:         On\n";
		}
		$text .= "\n";
		print $text;		
	}
	
	protected function error($msg){
		$this->error_count++;
		if($this->log_errors){
			fwrite(STDERR, "$this->line");
		}
		fwrite(STDERR, "Error (line {$this->line_count}): $msg\n");
		// delete the persistant variables, try again on next line
		unset(			
			  $this->page_name, $this->page_title_obj, $this->article_obj, 
			  $this->page_template_name, $this->table_template_name, $this->box_obj
			 );
	}

	/*
		Returns the unique identifier from the database of a table,
		given what page it's on and what template it's using.
	*/
	function getBoxUid($page_name, $template='', $namespace = 0, $use_page_name = false){
		$dbr =& wfGetDB( DB_SLAVE );
		$conditions = array();
		if($use_page_name === false){
			$conditions['page_uid'] = $this->getPageId($page_name, $namespace);
		} else {
			$conditions['page_name'] =  $page_name;
		}
		if ($template != '') {
			$conditions['template'] = str_replace(' ', '_', $template);
		}
		$result = $dbr->select($this->box_table, array('box_uid'), $conditions, __METHOD__);
		$row = $dbr->fetchObject($result);	
		return (!isset($row->box_uid)) ? false : $row->box_uid;
	}
	
	function newBoxUid( $page_uid ){
		global $wgServerName;
		if($page_uid == 0 || is_null($page_uid)) return;
		$box_uid = md5($wgServerName).".$page_uid.".uniqid(chr(rand(65,90)));
		return $box_uid;
	}
	
	// returns a page's unique id
	function getPageId($page_title, $namespace = NS_MAIN){
		$title = Title::newFromText($page_title, $namespace);
		return $title->getArticleID();
	}


	// calculates the union between $a and $b
	protected function array_union($a, $b) {
		$union = array_merge($a, $b); // duplicates may still exist
		$union = array_unique($union);
		return $union;
	}
	
	// checks if $a is a subset of $b
	protected function array_subset( $a, $b ) {
		return ( count( array_diff( array_merge($a,$b), $b)) == 0 ) ? true : false;
	}
	
	protected function is_not_empty_string($str){
		return ($str === "") ? false : true;
	}

	protected function compare_strings( $str1, $str2 ){
		$simil = similar_text($str1, $str2, $percent);
		$leven = levenshtein($str1, $str2, 1, 1, 1);
		//printf("\nsimilar_text returns %d chars in common (%.2f%%). ", $simil, $percent);
		//printf("levenshtein  distance of %d.\n", $leven);
		return $percent;
	}

	/*	
		- compares two row objects
		- return values:
			"exact" 	: the two rows match data regardless of whitespace
			"disjoint"	: the two rows are totally different
			"subset"	: the old row is a subset of the new row
			"different"	: they could have some common elements, but are regarded as different
	*/
	function compareRows( $old_row_obj, $new_row_obj ){
	
		// explode out row data
		$old_row_data_fields = explode ('||', $old_row_obj->row_data);
		$new_row_data_fields = explode ('||', $new_row_obj->row_data);	
		
		// trim off whitespace and delete empty fields
		for($i=0, $c=count($old_row_data_fields); $i<$c; $i++){
			$old_without_whitespace[$i] = trim($old_row_data_fields[$i]);	
		}
		$old_without_whitespace = array_filter($old_without_whitespace, array($this, 'is_not_empty_string'));
		for($i=0, $c=count($new_row_data_fields); $i<$c; $i++){
			$new_without_whitespace[$i] = trim($new_row_data_fields[$i]);	
		}
		$new_without_whitespace = array_filter($new_without_whitespace, array($this, 'is_not_empty_string'));
		
		if(count(array_intersect($old_without_whitespace, $new_without_whitespace)) == 0){
			// they are mutually exclusive
			return 'disjoint';
		} else {
			// they have same common elements
			if(count($this->array_union($old_without_whitespace, $new_without_whitespace)) == count($old_without_whitespace)){
				// they have the same number of elements
				if(implode("||", $old_without_whitespace) == implode("||", $new_without_whitespace)){
					// in the exact same order, regardless of whitespace
					return 'exact';
				} else {
					// in a different order
					return 'different';
				}
			} else {
				// they have a different number of elements
				if($this->array_subset($old_without_whitespace, $new_without_whitespace)){
					// and old is a subset of new
					return 'subset';
				} else {
					// no subset/superset relationship, just different
					return 'different';
				}
			}
		}		
	}
	
	function manageRedirects(){
		// get some useful info ...
		list($page_name, $page_type) = explode(':', $this->page_name);
		$tmp_article_obj = new Article(Title::newFromText($page_name));
		$redirect_page = explode(':', $tmp_article_obj->followRedirect());
		// ...and remake properties.
		$this->page_name = $redirect_page[0] . ':' . $page_type;
		$this->page_title_obj = Title::newFromText($this->page_name);
		$this->page_article_obj = new Article($this->page_title_obj);
	}
	
	// main function - takes a file name for an ifalt-type file
	function loadFromFile( $filename ){	
		if(is_file($filename)){
			$fh = fopen($filename, 'r');
			if(!$fh) die("Error opening $file for reading.\n");
		}

		if($this->verbose) print "Loading from TEXT...\n";
		while(!feof($fh)){
			$this->line = fgets($fh);
			if(trim($this->line) == "") continue;
			$this->line_count++;
			
			if($this->line_count == 1){
				if(preg_match("/<loader>/", $this->line)){
					$die_text = "This file looks like XML, but is missing the XML declaration.\n" . 
								"   (Something like  <?xml version=\"1.0\" ?"."> should work.) \n";
					die($die_text);
				}
			}
			if($this->verbose){
				$loop_start_time = time();
			}
			print ($this->verbose) ? "\n$this->line_count: " :  "\nWorking on line $this->line_count, ";
			$data = $this->parseLine($this->line);
			
			if( !$data->row_data && strtolower($data->update_type) != "clear"){
				$this->error('no row data');
				continue;
			}

   
		    if (preg_match( '/<br(\s+\/)?'.'>/', $data->row_data, $matches  )) {
			    $data->row_data = str_replace($matches[0], "\n", $data->row_data);
   	    	}

            $data->row_data = str_replace('\n', "\n", $data->row_data);




			// if we see a new page, repopulate the variables dealing with the page
			if( !isset($this->page_name) || $this->page_name != $data->page_name){
				if($data->page_name == ""){
					$this->error("problem with page_name field");
					continue;
				}
				// make some persistent variables
				$this->page_name = $data->page_name;
				$this->page_title_obj = Title::newFromText($this->page_name);
				if(!is_object($this->page_title_obj) || is_null($this->page_title_obj)){
					$this->error("bad page title.");
					continue;
				}
				$this->page_article_obj = new Article($this->page_title_obj);
				$this->page_article_obj->loadContent();
			
				// check if it exists and is valid
				if( !$this->page_article_obj->exists() ) {				
					// is it a redirect?	
					list($page_name, $page_type) = explode(':', $this->page_name);
					$tmp_article_obj = new Article(Title::newFromText($page_name));
					if($tmp_article_obj->isRedirect($tmp_article_obj->getContent())){
						// it is a redirect, do some page_name handling
						if($this->verbose) print "\t(redirected from {$data->page_name}) ";
						$this->manageRedirects();
					} else {
						$this->error($this->page_name . " doesn't exist.\n");
						continue;
					}
				}
				// get the content from the page
				$this->page_content = $this->getWikiText($this->page_title_obj);
				if($this->page_content == ""){
					$this->error("No text returned from page $data->page_name, it probably doesn't exist.");
					continue;
				}
				if($this->verbose) print "\tpage $this->page_name\n";
			}
			if($this->page_article_obj->getID() == 0){
				$this->error("article ID is 0");
				continue;
			}
			
			// if we see a new page_template, repopulate everything associated with this
			if( !isset($this->page_template_name) || $this->page_template_name != $data->page_template){
				if($data->page_template == ""){
					$this->error("problem with page_template field");
					continue;
				}
				$this->page_template_name = $data->page_template;
				$this->page_template_content = $this->getWikiText(Title::newFromText($this->page_template_name, NS_TEMPLATE));
				if($this->page_template_content == ""){
					$this->error("no text returned from page_template");
					continue;
				}
			}

			// if we see a new table_template, repopulate all this junk, too...
			if( !isset($this->table_template_name) || $this->table_template_name != $data->table_template){
				if($data->table_template == ""){
					$this->error("problem with table_template field");
					continue;
				}
				$this->table_template_name = $data->table_template;
				$this->table_template_content = $this->getWikiText(Title::newFromText($this->table_template_name, NS_TEMPLATE));
				if($this->table_template_content == ""){
					$this->error("no text returned from table_template");
					continue;
				}
			}
			if($this->verbose) print "\ttable $this->table_template_name,\n";				


			// Get the box_uid from the DB using page_uid...
			$box_uid = $this->getBoxUid($this->page_name, $this->table_template_name);
			if (is_null($box_uid) || $box_uid === false) {
				// ... didn't find anything, fall back on the page_name ... 
				$box_uid = $this->getBoxUid($this->page_name, $this->table_template_name, 0, true);
				if(is_null($box_uid) || $box_uid === false){
					// ... didn't work, try and grab it from the page
					$box_uid = $this->find_the_right_box($this->page_content, $this->table_template_name);
					if(is_null($box_uid) || $box_uid === false){
						$this->error('couldn\'t get box information');
						continue;
					}
				}
			}
	
	
	
	
			// create a new box object	
			$this->box_obj = new wikiBox();	
			$this->box_obj->setTemplate($this->table_template_name);
			$this->box_obj->setPageName($this->page_name);
			$this->box_obj->box_uid = $box_uid;
			//$this->box_obj->setPageUid($page_uid);
			$this->box_obj->set_headings_from_template();
			$ret = $this->box_obj->set_from_DB();
			if (!$ret) {
				$this->error("ERROR\t{$page->page_name}\tCouldn't set box_obj from parameters.\n");
				continue;
			}
			$old_box_wikitext = $this->getTableFromWikitext($this->page_content, $this->box_obj);
			
			// create an object to represent the new row
			$this->new_row = new wikiBoxRow;
			$this->new_row->row_data = $data->row_data;
			if($data->metadata != ""){
				$this->new_row->insert_row_metadata("", $data->metadata);
			}
						
			$te = new TableEdit;
			// run through each field and apply the tableEdit column rules. 
			$new_row_fields = explode('||', $this->new_row->row_data);
			for($i=0, $c=count($new_row_fields); $i<$c; $i++){
				$new_row_fields[$i] = $te->apply_column_rules($new_row_fields, $this->box_obj, $this->new_row, $i, "Save");
			}

			$this->new_row->row_data = join('||', $new_row_fields);
		
			if(isset($data->has_misc_features) && $data->has_misc_features == true) {
				if ( !$this->do_misc_features($data->misc_features) ) $this->error(__METHOD__ . " returned false.");
			}

			if(!$this->verbose) print "table '$this->table_template_name' on page '$this->page_name'.";
			switch(strtolower($data->update_type)){
				case 'clear':
					// clear all the old rows out of this table. 
					if($this->verbose) print "\tsaw a 'clear,' \n";
					$this->clearOldRows($this->box_obj, $this->start_time);
					if($this->verbose) print "& cleared old rows from table.";					
					break;
				case 'append':
					// force append
					if($this->verbose) print "\tsaw an 'append,' ";
					$this->appendRow($this->new_row);
					break;
				case 'merge': 	// fallthrough
					if($this->verbose) print "\tsaw a 'merge,' \n";
					$this->mergeRows($this->new_row);
					break;
                default:
                    if ($this->verbose) {
                        print "No \"update type\" set, defaulting to append.\n";
                    }
                    $this->appendRow( $this->new_row );
			} // end update-type switch

			// make new wikitext for box, and put into the page where the older one was. 
			if($this->verbose) print "\tmaking new wikitext table, ";	
			//var_dump($this->box_obj);die();
			$this->doSaveBox();
			// make the new box with the TableEdit class' make_wikibox() to get associated TableEdit hooks to run			
			$new_box_wikitext = $te->make_wikibox($this->box_obj);

			$this->page_content = str_replace($old_box_wikitext, $new_box_wikitext, $this->page_content);



			if (!$this->debug) {
				$this->page_article_obj->doEdit($this->page_content, "Data loaded by TableEdit_Loader.", EDIT_FORCE_BOT | EDIT_UPDATE);
				print ($this->verbose) ? "saved to page.\n" : "\n" ;
			}
			unset($this->box_obj);		// just in case

			if($this->verbose){
				$loop_length = time() - $loop_start_time;
				print "\t($loop_length sec)";
			}
		} // end while
		
		$script_exec_length = (time() - $this->start_time);
		print "\n...done";
		print ", in $script_exec_length seconds.\n";
	}
	
	
	
	public function do_misc_features( $array ){
		foreach ($array as $name => $feature) {
			// Here is where you can add code to define what to do for things in the 7th column. 	
			if ($name == 'row_style')	$this->new_row->row_style = trim($feature);
			
			print "added row style\n";
			return true;
		}
	}
	
	function find_the_right_box( $wikitext, $template ){
		$template = str_replace(' ', '_', $template);
		$pattern = "/id=(.*?)&.*?template=$template/xm";	
		preg_match($pattern, $wikitext, $matches);
		return (isset($matches[1])) ? $matches[1] : false;
	}
	
	// returns the unparsed wikitext from a page
	function getWikiText($title_obj){
		if( !is_object($title_obj) ) return false;
		$revision = Revision::newFromTitle($title_obj);
		if(is_null($revision)){
			$this->error("could not fetch revision from {$title_obj->getDBkey()}");
			return;
		} else {
			return $revision->getText();
		}
	}
	
	
	// return an array of row indices 
	function getWikiboxRows($box_obj, $ownerid = 0, $metadata = ''){
		$row_ids = array();
		foreach ($box_obj->rows as $row_index => $row){
			if ($row->owner_uid == $ownerid){
				$row_ids[] = $row_index;
			}
		}
		$result = array_unique($row_ids);
		return $result;
	}
	
	function matchMetadata ($row_obj, $metadata){
		$dbr =& wfGetDB( DB_SLAVE );
		$cond = array('row_metadata'=>mysql_real_escape_string($metadata), 'row_id'=>$row_obj->row_id);
		$result = $dbr->select($this->box_table, array('*'), $conds, __METHOD__);
		if ($result){
			return true;
		}	
		return false;
	}
	
	function insertRowMetadata($row_obj, $metadata){
		$dbr =& wfGetDB( DB_SLAVE );
		if ($metadata == '' || !isset($row_obj->row_id) || $this->matchMetadata($row_obj, $metadata))  return; # it's already there
		$result = $dbr->insert($this->box_table, array('row_id'=>$row_obj->row_id, 'metadata'=>$metadata), __METHOD__);
		$ret = ($result) ? true : false;
		return $ret;
	}

	/*
		Searches some wiki text for a particular box, returns the portion of the page
		that matches (the portion inbetween the box tags.)
	*/
	function getTableFromWikitext($wikitext, $box_obj){
		if(!is_object($box_obj)) return false;
		if(!isset($box_obj->box_uid)) return false;
		$tag = "<!--box uid=$box_obj->box_uid-->";
		$tag = str_replace('.','\.', $tag);				# . escaped in regex	
		$tag = str_replace('<!--','<\!--', $tag);		# ! escaped in regex
		$pattern = "/$tag(.*)$tag/is";	
		preg_match($pattern, $wikitext, $matches);
		#var_dump($pattern, $wikitext, $matches);
		return (isset($matches[0])) ? $matches[0] : false;
	}
	
	// returns an array of box_uids (with a valid page_uid) that use specific table template
	function getTables($page_title, $template){
		$dbr =& wfGetDB( DB_SLAVE );
		$conds = array('page_name'=> $page_title);
		$conds[] = 'page_uid != 0';
		if ($template != '') $conditions['template'] = $template;
		$result = $dbr->select($this->box_table, array('box_uid', 'page_uid'), $conds, __METHOD__);
		if($dbr->numRows($result) == 0){
			return;
		}
		$arr = array();
		while($row = $dbr->fetchRow($result)){
			if($row['page_uid'] != ""){
				$arr[] = $row['box_uid'];
			}
		}
		return $arr;
	}
	
	function parseLine($line){
		$line = trim($line);
		$tmp = explode("\t",$line);
		$data = new StdClass;
		if (isset($tmp[6])) {
			// Follows the specification at [http://us2.php.net/parse_str]
			$data->has_misc_features = true;
			parse_str(trim($tmp[6]), $data->misc_features); # parse column 7
		}
		if (isset($tmp[0])) {
			$data->page_name = trim($tmp[0]);
		}
		if (isset($tmp[1])) {
			$data->page_template 	= trim($tmp[1]);
		}
		if (isset($tmp[2])) {
			$data->table_template = trim($tmp[2]);
		}
		$data->row_data = (isset($tmp[3])) ? trim($tmp[3]) : "";
		$data->metadata = (isset($tmp[4])) ? trim($tmp[4]) : "";
		$data->update_type = (isset($tmp[5])) ? trim($tmp[5]) : "";

		foreach($data as $key => &$value){
			if(!is_array($value)){
				if(strtolower($value) == 'null'){
					$value = "";
				}
			}
		}
		//var_dump($data);
		return $data;
	}
	
	// save the box we're working with 
	public function doSaveBox(){
		if (!$this->debug) $this->box_obj->save_to_db();
	}
	
	public function appendRow($new_row_obj){
		if ($this->verbose) print "\tappended row, \n";		
		$this->box_obj->rows[] = $new_row_obj;
		return;
	}

	public function mergeRows($new_row_obj){	
		// compare the new row to each of the existing rows
		$results = array();
		foreach ($this->box_obj->rows as $old_row) {
			$results[] = $this->compareRows($old_row, $new_row_obj);
		}
		if (in_array('exact', $results)) {
			// skip, this row is already in the box
			return;
		} elseif (in_array('subset', $results)) {
			for($i=0, $c=count($results); $i<$c; $i++){
				if($results[$i] == 'subset'){
					foreach($this->box_obj->rows[$i]->row_metadata as $old_row_metadata_obj){
						$new_row_obj->metadata[] = $old_row_metadata_obj;
					}
					$this->box_obj->delete_row($i);
				}
			}
			$this->box_obj->rows[] = $new_row_obj;
			return;
		} else {
			$this->appendRow($new_row_obj);
 		}
	}
	
	public function foldInValues($subset_row_obj, $superset_row_obj){
		// fold in the new fields
		$subset_arr = explode("||", $subset_row_obj->row_data);
		$superset_arr = explode("||", $superset_row_obj->row_data);
		for($i=0, $c=count($superset_row_obj); $i<$c; $i++){
			if(is_null($subet_arr[$i]) && !is_null($superset_arr[$i])){
				$merged_arr[$i] = $superset_arr[$i];
			} else {
				$merged_arr[$i] = $subset_arr[$i];
			}
		}
		$subset_row_obj->row_data = implode('||', $merged_arr);
		return $subset_row_obj;
	}
	
	function clearOldRows($box, $time){
		// compare timestamps and delete row if older
		if(!$this->debug){
			foreach($box->rows as $row){
				if($time > $row->timestamp){
					$row->delete_row();
				}
			}
		}
		return;
	}
}

?>
