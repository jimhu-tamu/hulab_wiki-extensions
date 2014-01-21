<?php

class wikiBox {

	var $box_id = null;							// the primary key in the database
	var $template = '';
	var $box_uid = null;							// the long string delimiting the box in the page, see new_box_uid()
	var $page_name = null;
	var $page_uid = null;
	var $type = 0;
	var $headings = null;
	var $heading_style = '';
	var $help1 = '';
	var $help2 = '';
	var $help3 = '';
	var $help4 = '';
	var $above = '';
	var $below = '';
	var $box_style = '';
	var $timestamp = null;
	var $rows = array();
	var $has_foreign_row = false;
	var $has_deleted_rows = false;
	var $column_names = array();
	var $column_rules = array();
	var $colnum = null;
	var $rownum = null;
	var $box_metadata = array();
	var $is_changed = false;					// has this box been changed since last load?
	var $skip_saving_relations = false;			// skip normal foreign-table saving?
	private $is_datatable;						// is this table a JS "dataTable" (holds info from template)

	
	// for some reason the constructor takes the box
	function __construct( $box_uid = null ){
		$this->box_uid = $box_uid;
		
		// I don't think we ever use this variable. (It is incorrectly named anyhow.)
		//   DPR 2011-04-27
		# $this->uid = (isset($wgUser)) ? $wgUser->getID() : 0;
	}

	// make a new box from a primary_key
	static function newFromBoxId( $id ) {
		$box = new self;
		$box->box_id = $id;
		$box->load();
		return $box;
	}

	// make a new box from a box_uid
	static function newFromBoxUid( $box_uid ) {
		$box = new self;
		$box->box_uid = $box_uid;
		$box->load();
		return $box;
	}

	function setPageUid( $page_uid ){
		$this->page_uid = $page_uid;
		return true;
	}

	function setId( $id ) {
		$this->box_id = $id;
		return true;
	}

	function setPageName($page_name) {
		$this->page_name = $page_name;
		return true;
	}

	function setTemplate($template){
		$this->template = $template;
		return true;
	}

	function getHeadings() {
		return $this->headings;
	}

	function set_headings_from_template(){
		$templatePage = Revision::newFromTitle(Title::makeTitle(NS_TEMPLATE, $this->template));

		if (! $templatePage){
			$this->headings = "[["  .wfMsg('template') . ":" . $this->template . "]] " . wfMsg('notFound');
		}else{
			$template_text = '<xml>'.trim($templatePage->getText()).'</xml>';
			$xml_parser = xml_parser_create();
			$parse = xml_parse_into_struct($xml_parser, $template_text, $values, $index);
			xml_parser_free($xml_parser);
			if (isset ($index['HEADING_STYLE'])) $this->heading_style = trim($values[$index['HEADING_STYLE'][0]]['value']);
			if (isset ($index['TABLE_STYLE'])) $this->box_style = trim($values[$index['TABLE_STYLE'][0]]['value']);
			if (isset ($index['BOX_STYLE'])) $this->box_style = trim($values[$index['BOX_STYLE'][0]]['value']);
			if (isset ($index['STYLE'])) $this->heading_style = trim($values[$index['STYLE'][0]]['value']);
			if (isset ($index['COLUMN_STYLE'])) $this->column_style = trim($values[$index['COLUMN_STYLE'][0]]['value']);
			if (isset ($index['TYPE'])) $this->type(trim($values[$index['TYPE'][0]]['value']));
			if (isset ($index['HELP1'])) $this->help1 = trim($values[$index['HELP1'][0]]['value']);
			if (isset ($index['HELP2'])) $this->help2 = trim($values[$index['HELP2'][0]]['value']);
			if (isset ($index['HELP3'])) $this->help3 = trim($values[$index['HELP3'][0]]['value']);
			if (isset ($index['HELP4'])) $this->help4 = trim($values[$index['HELP4'][0]]['value']);
			if (isset ($index['ABOVE'])) $this->above = trim($values[$index['ABOVE'][0]]['value']);
			if (isset ($index['BELOW'])) $this->below = trim($values[$index['BELOW'][0]]['value']);
			if (isset ($index['DATATABLE'])) $this->is_dataTable = true;

			if (isset ($index['HEADINGS'])){
				$heading_list = $values[$index['HEADINGS'][0]]['value'];
				$heading_list = explode("\n",trim($heading_list));
				$this->headings = "";
				$i = 0;
				foreach ($heading_list as $heading){
					$tmp = explode("||", $heading);
					$this->headings .= array_shift($tmp)."\n";
					if (isset($tmp[0])){
						$tmp1 = implode('||',$tmp);
						$tmp2 = explode('|', $tmp1);
						$this->column_names[$i] = array_shift($tmp2);
						$this->column_rules[$i] = $tmp2;
					}else{
						$this->column_names[$i] = $heading;
					}
					$i++;
				}
				$this->headings = trim($this->headings);
				$this->colnum = count(explode("\n",$this->headings));
			} else {
				//$this->column_names[$i] = $heading;
#				trigger_error("Trouble setting headings on " . $this->template . " table on " . $this->page_name, E_USER_WARNING);
			}
		}
		$this->colnum = count($this->headings);
	}

	/**
	 * This function will determine if there are any foreign rows and set some properties accordingly.
	 *
	 */
	function determine_foreign_properties( $i ){
		if(isset($this->column_rules[$i][0]) && $this->column_rules[$i][0] == 'foreign') {
			// tell the row it is foreign.
			$this->rows[$i]->is_foreign = true;
			if(!isset($this->has_foreign_row) || $this->has_foreign_row === false) {
				// tell the box it has foreign row(s).
				$this->has_foreign_row = true;
			}
		}
	}

	// attempt to find a template based on the headings
	// WARNING: very slow and not high fidelity
	function set_template_from_headings(){
		$headings_from_box = explode("\n", $this->headings);
		$headings_from_box = $this->prepare_array_for_comparison($headings_from_box);
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select('page', 'page_title', 'page_namespace = "10"', __METHOD__);
		//set up a hash for use later.
		if(!isset($this->table_template_headings)) {
			while($row = $dbr->fetchObject($result)) {
				$article = new Article(Title::newFromText($row->page_title, NS_TEMPLATE));
				if(preg_match("/<headings>(.*?)<\/headings>/is", $article->getContent(), $m)) {
					$this->table_template_headings[$row->page_title] = array();
					$lines = explode("\n", $m[1]);
					foreach ($lines as $line) {
						$stuff = explode("|", $line);
						$this->table_template_headings[$row->page_title][] = $stuff[0];
					}
					// this is a 2D array, so loop through the first dimension
					foreach ($this->table_template_headings as &$index) {
						$index = $this->prepare_array_for_comparison( $index );
					}
				}
			}
		}
		foreach($this->table_template_headings as $template => $headings_from_template){
			if(count(array_diff($headings_from_template, $headings_from_box)) == 0){
				$this->setTemplate($template);
				return true;
			}
		}
		return false;
	}

	// this function takes an array, trims whitespace from each element,
	// and deletes any empty strings. handy for comparisons.
	function prepare_array_for_comparison( $array ){
		$reduced_array = array();
		for ($i=0, $c=count($array); $i<$c; $i++) {
			if (trim($array[$i]) != "") $reduced_array[] = trim($array[$i]);
		}
		return $reduced_array;
	}


	/**
	 * Serializes $this and returns it. To unserialize $this, use unserialize() function.
	 * Example usage:
	 *	$serializedBox = $box->get_serialized();
	 *  $box = unserialize($serializedBox);
	 *
	 * @access public
	 * @return string serialized version of $this
	 */
	function get_serialized(){
		return serialize($this);
	}

	function make_rows_from_DB(){
		# Get the row data
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select('ext_TableEdit_row','*',"box_id = '".$this->box_id."'",__METHOD__,array('ORDER BY'=>'row_sort_order'));
	#	print_r($result);
		$rows = array();
		if (!$result){
			$rows[0] = '';
		}else{
			$i = 0;
			while( $x = $dbr->fetchObject ( $result ) ) {
				$row = new wikiBoxRow;
				$row->row_index = $i;
				$row->row_id = $x->row_id;
				$row->box_id = $x->box_id;
				$row->set_fromDb();
				$rows[] = $row;
				$this->rownum = $i;
				$i++;
			}
		}		
		$dbr->freeResult( $result );
		# make sure row data matches number of columns in headings
		# also set foreign props
		foreach ($rows as $row_index => $row){
			$tmp = explode ("||",$row->row_data);
			$headings = explode("\n",$this->headings);
			$tmp2 = array();
			foreach($headings as $i=>$heading){
				$tmp2[$i] = '';
				if (isset($tmp[$i])) $tmp2[$i] = $tmp[$i];
			}
			$row->row_data = implode("||",$tmp2);
		}
		return $rows;
	}
	function type($type){
		if(isset($type)) $this->type = $type;
		return $this->type;
	}

	function rownum(){
		return count($this->rows);
	}
	function get_row($row_index){
		if (isset($this->rows[$row_index])) return $this->rows[$row_index];
		return false;
	}

	function insert_row($data, $owner='', $style=''){
		$row = new wikiBoxRow;
		$row->box_id = $this->box_id; #echo "making new row";
		$row->row_data = $data;
		$row->owner_uid = $owner;
		$row->row_style = $style;
		$row->row_index = $this->rownum();
		$this->rownum++;
		$row->row_sort_order = $this->rownum;
		$this->rows[] = $row;
		return $row;
	}

	function delete_row($row_index){
		if (!isset($this->rows[$row_index]) || !is_object($this->rows[$row_index])) return false;
		$this->rows[$row_index]->delete_row();
		$this->rownum = count($this->rows); # delete may have failed due to permissions
		$this->has_deleted_rows = true;
		return true;
	}

	function has_deleted_rows(){
		foreach($this->rows as $row) if($row->is_current===false) return true;
		return false;
	}

	function has_edited_rows(){
		foreach($this->rows as $row) if($row->is_edited === true) return true;
		return false;
	}

	function clear_rows(){
		#echo "clearing<br>";
		foreach ($this->rows as $row) $row->delete_row();
	}

	function save_clone(){
		$this->box_id = null;
		$this->box_uid = $this->new_box_uid();
		foreach ($this->rows as $row) $row->row_id = null;
		$this->save_to_db();
	}

	private function  getCorrectPageName( $page_id ) {
		if ( !is_int($page_id) ) {
			return false;
		}

		$dbr =& wfGetDB( DB_SLAVE );
		$r = $dbr->select(
			'page_title',
			'page',
			'page_id = "' . $page_id . '"'
		);
		if ($r) {
			return $r->page_title;
		}
		else {
			return false;
		}
	}


	// why isn't this just named save() ? 
	function save() {
		return $this->save_to_db();
	}

	// only want to save if new or changed
	function save_to_db(){

		global $wgUser;
		$dbw =& wfGetDB( DB_MASTER);

		$save_time = time();
		if (!isset($this->headings) && isset($this->template)) {
			$this->set_headings_from_template();
		}

		// order of $values is important...

		$values = array(
			'template'  	=>	$this->template,
			'page_name' 	=>	$this->page_name,
			'page_uid'  	=>	$this->page_uid,
			'box_uid'   	=>	$this->box_uid,
			'type'      	=>	$this->type,
			'headings'   	=>	$this->headings,
			'heading_style'	=>	$this->heading_style,
			'box_style'   	=>	$this->box_style,
			'timestamp' 	=>	$save_time
		);
		if($this->box_id == null || $this->box_id == ''){
			$values['box_id'] = null;
			if ( !isset($this->box_uid) || is_null($this->box_uid) ){
				$this->box_uid = $this->new_box_uid();
			}
			$values['box_uid'] = $this->box_uid;
			$result = $dbw->insert('ext_TableEdit_box', $values, __METHOD__);
			$this->box_id = $dbw->insertId();
			$values['box_id'] = $this->box_id;
		}
		# do the rows;

		$changed = 0;
		if ( count($this->rows) > 0 ) {
			foreach ($this->rows as $key => $row){
				if (is_int($key) && is_object($row)){
                    $row->box_id = $this->box_id;

                    if ( !is_a($row, 'wikiBoxRow') ) {
                        print_r("<br><br>Object is not a wikiBoxRow class!<br><br>");
                        print_r("<u>Data:</u><pre>");
                        var_dump( $row );
                        print_r("</pre><u>Backtrace:</u><pre>");
                        debug_print_backtrace();
                        die();
                    }

					$changed += $row->db_save_row($this->box_id); # somehow a non-wikiboxrow gets added to the row array.
				}
			}
		}
		if ($changed > 0 || $this->is_changed) {
			$result = $dbw->update('ext_TableEdit_box',$values, array("box_id = '".$this->box_id."'"), __METHOD__);
			$this->insert_box_metadata(null,'Saved by:'.$wgUser->getID(),$save_time);
		}
		foreach ($this->box_metadata as $box_metadata) $box_metadata->db_save();
		$this->timestamp = $save_time;
		return;
	}


	function load() {
		return $this->set_from_DB();	
	}

	/**
	 * Fill $this with information from the database. This method works best if $this->box_uid is set,
	 * but it will try to set the object using other properties (box_id, pagename, and template.) If a
	 * template is defined, then get the headings from it.
	 *
	 * @access public

	 */
	function set_from_DB(){
		$conds = array();
		if(isset($this->box_uid) && !empty($this->box_uid)) {
			$conds[] = "box_uid = '" . $this->box_uid . "'";
		} else {
			if(isset($this->box_id) && !empty($this->box_id)){
				$conds[] = "box_id = '" . $this->box_id . "'";
			} else {
				if(isset($this->page_name) && !empty($this->page_name)){
					$conds[] = "page_name LIKE '" . $this->page_name . "'";
				} else {
					return false;
				}
			}
		}
		if (isset($this->template) && !empty($this->template)) {
			$conds[] = "template = '" . $this->template . "'";
		}
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select('ext_TableEdit_box', '*', $conds ,__METHOD__ );
		if ($dbr->numRows($result) != 1) return false;
		$x = $dbr->fetchObject ( $result );
		$arr = get_object_vars($x);	
		foreach ($arr as $key=>$val){
			$this->$key = stripslashes($val);
		}
		# Get information about the table headings.  Headings can come from either a template page or from a field in the box table.
		# If a template is specified, it gets precedence.
		if ($this->template != '') $this->set_headings_from_template();
		$this->colnum = count(explode("\n",$this->headings));
		$this->rows = $this->make_rows_from_DB();
		# JH 20130527 daniel called this method before the rows were created, which makes me wonder if it is really used for anything
		# moved here to suppress errors.
		foreach ($this->rows as $row_index => $row){
			$this->determine_foreign_properties($row_index);
		}
		$this->set_metadata_fromDb();

		$dbr->freeResult( $result );
		return true;
	}

	public function getLockMessage() {
		return ( $this->is_locked ) 
			? $this->message_from_last_lock
			: "";
	}

	public function getLockUser() {
		return ( $this->is_locked )
			? $this->user_who_locked_this_row
			: $this->user_who_last_unlocked_this_row;
	}


	/**
	 *  This method PERMANENTLY deletes a box, all its rows, all metadata and 
	 *    everything associated with this box.
	 */
	function delete() {
		// remove this box's metadata
		if(is_null($this->box_id)){
			trigger_error("can't delete table with uid:$this->box_uid. box_id not found");
			return;
		}
		foreach ( $this->box_metadata as $m ) {
			$m->delete();
		}		
		// remove the rows and the row-metadata
		foreach ( $this->rows as $r ) {
			$r->delete_row();
		}
		// delete the box
		$dbw =& wfGetDB( DB_MASTER );
		return $dbw->delete(
			'ext_TableEdit_box',
			array( 'box_id = ' . $this->box_id ),
			__METHOD__
		);
	}


	function new_box_uid(){
		global $wgServerName;
		return md5($wgServerName).".".$this->page_uid.".".uniqid(chr(rand(65,90)));
	}

	static function short_uid_from_box_uid( $box_uid ) {
		if (!$box_uid) return;
		preg_match("/(\w+)\.(\d+)\.(\w+)/", $box_uid, $matches);
		if (!$matches) return;
		return $matches[3];
	}

	function get_short_uid() {
		return $this->short_uid_from_box_uid( $this->box_uid );
	}

	# return true if all rows belong to user or are public
	function user_owned(){
		global $wgUser;
		foreach ($this->rows as $row){
			if ($row->owner_uid != 0 && $row->owner_uid != $wgUser->getID()) return false;
		}
		return true;
	}

	function delete_box_from_db() {
        // I rewrote this function as delete() without noticing this one. I'm using
        //    mine and making this one point there instead.    -DPR 2011-04-27
	    /*	
        foreach ($this->rows as $row){
			$row->delete_row();
			$row->db_save_row();
		}
		foreach ($this->box_metadata as $box_metadata){
			$box_metadata->delete();
			$box_metadata->db_save();
		}
		$dbw =& wfGetDB( DB_MASTER);
		return $dbw->delete('ext_TableEdit_box', array("box_id = '".$this->box_id."'"),__METHOD__);
        */
        return $this->delete();
	}

	function set_metadata_fromDb(){
		$this->box_metadata = array();
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select('ext_TableEdit_box_metadata','*',array("box_id = '".$this->box_id."'"),__METHOD__);
		if (!$result || count($dbr->numRows($result)) == 0){
			return true;
		}
		while( $x = $dbr->fetchObject ( $result ) ) {
		#	echo '<pre>';print_r($x);echo '</pre>';
			$this->insert_box_metadata($x->box_metadata_id,$x->box_metadata, $x->timestamp);
		}
		$dbr->freeResult( $result );
		return true;
	}
	function insert_box_metadata($meta_id= '' ,$metadata='', $timestamp = ''){
		$meta = new wikiBoxMetadata;
		$meta->box_id = $this->box_id;
		$meta->box_metadata_id = $meta_id;
		$meta->metadata = $metadata;
		$meta->timestamp = $timestamp;
		$this->box_metadata[] = $meta;
		return true;
	}
	# adds and empty column to one end
	function append_column($label, $type = 'push'){
		$headings = explode("\n",$this->headings);
		if ($type == 'unshift') array_unshift($headings, $label);
		else $headings[] = $label;
		$this->headings = implode("\n",$headings);
		foreach ($this->rows as $row){
			$data = explode("||",$row->row_data);
			if ($type == 'unshift') array_unshift($data, '');
			else $data[] = '';
			$row->row_data = implode('||',$data);
		}
		$this->colnum++;
		return true;
	}

	#removes column and its data! returns an array of the data
	function remove_column($type = 'pop'){
		$headings = explode("\n",$this->headings);
		if ($type == 'shift') array_shift($headings, '');
		else array_pop($headings);
		$this->headings = implode("\n",$headings);
		$removed = array();
		foreach ($this->rows as $row){
			$data = explode("||",$this->headings);
			if ($type == 'shift') $removed[] = array_shift($data, '');
			else $removed[] = array_pop($data);
			$row->row_data = implode('||',$data);
		}
		$this->colnum--;
		return $removed;
	}


	# direction +1 or -1 assume +1 if not passed
	function shift_cols($col, $direction){
		if ($col == 0 && $direction < 0) return;
		if ($col+1 >= $this->colnum && $direction > 0) return;
		$headings = $this->headings;
		$arr = explode("\n", $headings);
		$this->headings = implode("\n",$this->reorder_array($arr,$col,$direction));
		$new_rows = array();
		foreach ($this->rows as $row){
			$row_data = explode('||', $row->row_data);
			$row->row_data = implode('||',$this->reorder_array($row_data, $col, $direction));
			$new_rows[] = $row;
		}
		$this->rows = $new_rows;
		return;
	}

	function reorder_array($array, $key, $direction){
		$items_before = array();
		if ($key > 0) $items_before = array_slice($array,0,$key);
		$items_after = array();
		if ($key <= $this->colnum) $items_after = array_slice($array,$key+1);
		if ($direction > 0){
			$items_before[] = array_shift($items_after);
		}else{
			$item = array_pop($items_before);
			array_unshift($items_after, $item);
		}
		$items_before[] = $array[$key];
		$arr = array_merge($items_before,$items_after);
		return $arr;
	}

	/**
	 *	These method is useful only when reading data, as you lose the order
	 *	when you split into an associative array.
	 *
	 */
	function get_fields_with_headings( $row ){
		if ( !isset($this->headings) || !is_object($this) ) {
			return;
		}
		$heading = explode("\n", $this->headings);
		$field = explode('||', $row->row_data);
		for ( $i=0,$c=count($heading); $i<$c; $i++ ) {
			if ( isSet($heading[$i]) && isSet($field[$i]) ) {
				$arr[trim($heading[$i])] = $field[$i];
			}
		}
		return $arr;
	}

	/**
	 *	Checks to see if this box is used somewhere in the wiki. 
	 *  Used for consistency checking of the database by some maintenance scripts.
	 *
	 *  Simply checks for the box_uid somewhere in the page. Not the greatest method,
	 *  but it will work for now.
	 */
	function isUsed() {
		if ( !isSet($this->page_uid) || !$this->page_uid ) {
			//trigger_error( 'no page_id set for this box ('.$this->box_uid.')', E_USER_WARNING );
			return false;
		} 
		$article = Article::newFromID( $this->page_uid ); 
		if ( !$article ) {
			//trigger_error( 'could not make article object from page_id ('.$this->page_uid.')', E_USER_WARNING );
			return false;
		}
		if ( !$article->exists() ) {
			//trigger_error( 'page does not exist for box ('.$this->box_id.')', E_USER_WARNING );
			return false;
		}
		if ( strpos($article->getContent(), $this->box_uid) !== false ) {
			return true;
		}
		return false;
	}

	function parentArticle() {
		if ( isSet($this->isUsedOn) && $this->isUsedOn !== false ) {
			return $this->isUsedOn;
		}
		if ( $this->isUsed() ) {
			$a = Article::newFromID( $this->page_uid );
			$a->loadContent();
			$this->isUsedOn = $a;
			return $this->isUsedOn;
		}
		else {
			return false;
		}
	}


}

class wikiBoxRow{

	public $is_locked;
	public $is_foreign;

	# note that row_id is the row_id in the database.  This may not be the same as the row index in box->rows[row_id]
	function __construct( $box_id = 0, $row_id = null ) {
		$this->box_id = $box_id;
		$this->matched = '';
		$this->row_id = '';
		$this->row_data = '';
		$this->row_style = '';
		$this->row_sort_order = '';
		$this->timestamp = time();

		$this->is_current = true; 				// set to false when deleting a row
		$this->is_edited = false;				// hasn't been edited yet.

		$this->row_index = '';
		$this->row_metadata = array();
		$this->row_data_original = '';

		$this->field = array();					// holds information about foreign rows. see TableEdit::apply_column_rules for more info.
	}

	public static function newFromId( $row_id ) {
		$r = new self;
		$r->row_id = $row_id;
		$r->set_fromDb();
		return $r;
	}

    public function processAuthorshipHistory() {

    	if ( !$this->row_id ) {
    		return false;
    	}

        $dbr =& wfGetDB( DB_SLAVE );
        $result = $dbr->select(
            'ext_TableEdit_row_metadata',
            array('row_metadata', 'timestamp'),
            'row_id = ' . $this->row_id,
            __METHOD__
        );
        $authorship = array();
        while ( $version = $dbr->fetchObject($result) ) {
        	if ($version->timestamp == 0 || is_null($version->timestamp) ) {
        		continue; // trash non-timestamped metadata (maybe a bad idea)
        	}
        	if ( preg_match('/Saved by:(\d+)/i', $version->row_metadata, $m) ) {
        		$authorship[ $version->timestamp ] = $m[1];
        	}
        }
        // sort by the keys (timestamps) in forward order
        ksort($authorship);
        return ( $authorship )
        	? $authorship
        	: false;
    }

	/**
	 * Returns the first user to edit this row who isn't a Bot. This the criteria for being the owner/editor
	 * of a row/annotation. Returns ONLY the user_id, trashes the associated timestamp
	 *
	 * @return int
	 */
    public function originalRowAuthor( $ignore = array() ) {
        // if we got an integer parameter, add it to the array of ignores
        if ( is_string($ignore) || is_integer($ignore) ) {
            $ignore = array( $ignore );
        }
        // make sure the integer user_id of 0 is considered a bot
        if ( !in_array( '0', $ignore ) ) {
            array_push( $ignore, '0'  );
        }
        // get all the authors
    	$authors = $this->processAuthorshipHistory();
    	if ( !$authors ) {
    		return false;
    	}
    	// loop through , return the first non-bot/non-ignored user_id
		foreach ( $authors as $user_id ) {
			if ( in_array($user_id, $ignore) ) {
				continue;
			}
			return $user_id;
		}
    }

    /**
     * Checks to see if this row has been edited by a human.
     * 
     * @params int|string|array  $ignores   An integer user_id of the bot or an array of integer user_ids
     *                               to ignore when considering a human-edit.    
     *
     */
    public function hasHumanEdit( $ignore = array() ) {
        // if we got an integer parameter, add it to the array of ignores
        if ( is_string($ignore) || is_integer($ignore) ) {
            $ignore = array( $ignore );
        }
        // make sure the integer user_id of 0 is considered a bot
        if ( !in_array( '0', $ignore ) ) {
            array_push( $ignore, '0'  );
        }
		if ( isset($this->row_metadata) && count($this->row_metadata) > 0 ) {
			foreach ( $this->row_metadata as $meta ) {
				if ( preg_match("/Saved\s+by:(\d+)/", $meta->metadata, $m) ) {
					if ( !in_array($m[1], $ignore)) {
						return true;
					}
				}
			}
		}
		return false;
	}


	public function isLocked() {
		return $this->is_locked;
	}

	public function lock( $user_id = 0, $reason = "" ) {
		global $wgUser;

		if ( is_string($user_id) ) {
			$user_id = (int) $user_id;
		}
		$this->owner_uid = $user_id;
		$metadata = sprintf( 
			"locked:%d; reason:%s", 
			$this->owner_uid,
			$reason 
		);
		$timestamp = time();
		$this->insert_row_metadata( null, $metadata, $timestamp );
		$this->is_locked = 1;
		return $this->db_save_row();
	}

	public function unlock( $user_id = 0 ) {
		if ( !$this->is_locked ) {
			return false;
		}
		if ( is_string($user_id) ) {
			$user_id = (int) $user_id;
		}
		if ( $this->owner_uid !== $user_id ) {
			return false;
		}
		$metadata = sprintf( 
			"unlocked:%d;", 
			$user_id
		);
		$timestamp = time();
		$this->insert_row_metadata( null, $metadata, $timestamp );
		$this->is_locked = 0;
		return $this->db_save_row();
	}

	function load() {
		return $this->set_fromDb();
	}

	function set_fromDb(){
		if ($this->row_id === null) return;
		#check both box_id and row_id in case of screwups
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select(
			'ext_TableEdit_row',
			'*',
			array(
				"row_id = '".$this->row_id."'"
			),
			__METHOD__
		);
		if (!$result || $dbr->numRows($result) == 0){
			$this->is_current = false;
			return false;
		}
		if (count($dbr->numRows($result)) > 1) return; #("Error:box_id should be unique");;

		$x = $dbr->fetchObject( $result );
        $arr = get_object_vars($x);		
        if ( $arr ) {
            foreach ($arr as $key=>$val){
                $this->$key = $val;
            }
        }
		// dpr 2010-10-04
		if ( isSet($x->row_locked) ) {
			$this->is_locked = $x->row_locked;
		}

		$this->row_data = preg_replace('/\\r/','', $this->row_data);
		$this->row_data_original = $this->row_data;
		$this->is_current = true;
		$this->relations = $this->query_relations_table($this);
		$this->set_metadata_fromDb();

		$dbr->freeResult( $result );
		return true;
	}

	/**
	 *
	 * @param object $row A row-object to look for in the database.
	 * @param string	$type 	'to', 'from' or 'both'
	 *
	 * Returns an object with two arrays as properties, 'to' and 'from.' Kept in static context in case
	 * this needs to be called externally.
	 */
	function query_relations_table( $row, $type = 'both'){
		if(is_null($row->row_id) || empty($row->row_id)) return false;
		$dbr =& wfGetDB( DB_SLAVE );
		$conds = array();
		switch(trim($type)){
			case 'to':
				$conds[] = "ext_TableEdit_relations.to_row = '" . $row->row_id . "'";
				break;
			case 'from':
				$conds[] = "ext_TableEdit_relations.from_row = '" . $row->row_id . "'";
				break;
			case 'both':
				$conds[] = "ext_TableEdit_relations.to_row = '" . $row->row_id . "' OR ext_TableEdit_relations.from_row = '" . $row->row_id . "'";
				break;
		}
		// see if we can find anything in the `relations` table dealing with this table.
		$res = $dbr->select(
			'ext_TableEdit_relations',
			'*',
			$conds,
			__METHOD__
		);
		$rel = array();
		if ($dbr->numRows($res) >= 1) {
			while($x = $dbr->fetchObject($res)) $rel[] = $x;
		} else return false;
		return $rel;
 	}


	 /**
	 *	Inserts a row into the ext_TableEdit_relations database table.
	 *
	 */
 	function insert_relation($from_row, $from_field, $to_row, $to_field) {
		$dbr =& wfGetDB( DB_SLAVE );
 		$values = array(
			'from_row' 	  => $from_row,				// not NULL
			'from_field'  => $from_field,
			'to_row' 	  => $to_row,				// not NULL
			'to_field' 	  => $to_field
		);
		return $dbr->insert('ext_TableEdit_relations', $values, __METHOD__);
 	}

	function delete_relation( $rel_id ){
		$dbr =& wfGetDB( DB_SLAVE );
		return $dbr->delete('ext_TableEdit_relations', array("rel_id = '" . $rel_id . "'"), __METHOD__);
	}


	function save() {
		return $this->db_save_row();
	}

	public function exists() {
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->selectRow(
			'ext_TableEdit_row', 
			'row_id',
			'row_id = ' . $this->row_id, 
			__METHOD__
		);
		return ( $result ) ? true : false ;
	}


	/**
	 * @todo 1. check row to see if changed, save on changed
	 *		 2. should we keep track of what relations changed here? or let the user/coder do it?
     */
	function db_save_row(){
		global $wgUser;

		$changed = 0;
		# $this->row_id set when data previously pulled from database
		# for a row only set in temp space, should be undef
		$dbw =& wfGetDB( DB_MASTER );

		$timestamp = time();

		if ($this->row_data == '') return 0; # don't save rows with no data or || delimiters
		if (!isset($this->row_style)) $this->row_style = '';
		if (!isset($this->owner_uid)) $this->owner_uid = 0;			// a user_id of 0 means "public"

		wfRunHooks( 'TableEditBeforeDbSaveRow', array(&$this)  );

		if ( !$this->row_id ) {
			// a new row..insert!

			if ( $this->is_current == true ) {

				$a = array(
					'box_id'        	=>	$this->box_id,
					'owner_uid'       	=>	$this->owner_uid,
					'row_data'      	=>	$this->row_data,
					'row_style'     	=>	$this->row_style,
					'row_sort_order'	=>	$this->row_sort_order,
					'row_locked'        =>  ($this->is_locked) ? 1 : 0, // "cast" to bool
					'timestamp'     	=>	$timestamp
				);
				// insert into the row-table
				$result = $dbw->insert('ext_TableEdit_row',$a,__METHOD__);

				// insert the metdata
				$this->row_id = $dbw->insertId();
				$this->insert_row_metadata( null, 'Saved by:' . $wgUser->getID(), $timestamp );
				foreach ( $this->row_metadata as $metadata ) {
					$metadata->row_id = $this->row_id;
				}

				$changed = 1;
			}

		} elseif ( $this->is_current === true ) {

			if ( $this->is_edited || $this->row_data != $this->row_data_original ) {

				# it's in the DB and it's current, update it, but only if the row data has changed
				$a = array(
					'owner_uid'     	=>	$this->owner_uid,
					'row_data'      	=>	$this->row_data,
					'row_style'       	=>	$this->row_style,
					'row_sort_order'	=>	$this->row_sort_order,
					'row_locked'        =>  ($this->is_locked) ? 1 : 0, // "cast" to bool
					'timestamp'     	=>	$timestamp
				);

				$result = $dbw->update(
					'ext_TableEdit_row',
					$a,
					array(	"row_id = '" . $this->row_id . "'"),
					__METHOD__
				);
				$this->insert_row_metadata( null, 'Saved by:'.$wgUser->getID(), $timestamp );
				$changed = 1;
			}


		} else {

			if ( $this->is_locked ) {
				trigger_error( 'Deleting a locked row, (row_id= ' . $this->row_id . ')', E_USER_WARNING );
			}

            #it's in the DB but it's not current.  Delete it from the DB

		    wfRunHooks( 'TableEditBeforeRowDelete', array(&$this)  );
			$result = $dbw->delete(
				'ext_TableEdit_row',
				array("row_id = '".$this->row_id."'"),
				__METHOD__
			);
		    wfRunHooks( 'TableEditAfterRowDelete', array(&$this, $result)  );
			$changed = 1;

		}
		foreach ($this->row_metadata as $row_metadata) {
			$changed += $row_metadata->db_save();
		}

		wfRunHooks( 'TableEditAfterDbSaveRow', array(&$this)  );

		return $changed;
	}

    // shortcut to deleting a row.
    public function delete() {
        foreach ( $this->row_metadata as $m ) {
            $m->delete();
        }
        $this->is_current = false;
        $this->db_save_row();
    }


	#delete and undelete only work on the temporary row info, not on the db!
	function row_data(){
		return $this->row_data;
	}

	function user_can_edit(){
		global $wgUser;
		if ($this->owner_uid == 0) return true;
		if ($this->owner_uid == $wgUser->getID()) return true;
		if ($wgUser->isAllowed('delete')) return true;
		return false;
	}
	# only delete if user can
	function delete_row(){
		if ($this->user_can_edit() === false) return false;
		$this->is_current = false;
		foreach ($this->row_metadata as $row_metadata) $row_metadata->delete();
		return true;
	}

	# only undelete if user can; this shouldn't be a problem, but just in case
	function undelete_row(){
		if ($this->user_can_edit() === false) return;
		$this->is_current = true;
	}

	function set_metadata_fromDb(){
		$this->row_metadata = array();
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select('ext_TableEdit_row_metadata','*',array("row_id = '".$this->row_id."'"),__METHOD__);
		if (!$result || count($dbr->numRows($result)) == 0){
			return true;
		}
		while( $x = $dbr->fetchObject ( $result ) ) {
		#	echo '<pre>';print_r($x);echo '</pre>';
			$this->insert_row_metadata($x->row_metadata_id, $x->row_metadata, $x->timestamp, $x->row_metadata);
		}
		$dbr->freeResult( $result );
		return true;
	}

	function insert_row_metadata( $meta_id= '' , $metadata='', $timestamp = '', $original = '' ) {
		$meta = new wikiBoxRowMetadata;
		$meta->row_id = $this->row_id;
		$meta->row_metadata_id = $meta_id;
		$meta->metadata = $metadata;
		$meta->timestamp = $timestamp;
		$meta->original = $original;
		$this->row_metadata[] = $meta;
		return true;
	}

	function hasMatchingMetadata( $regex ) {
		if ( count($this->row_metadata) == 0 ) {
			return false;
		}
		foreach ( $this->row_metadata as $m ) {
			if ( preg_match( $regex, $m->metadata ) ) {
				return true;
			}
		}
		return false;
	}

}

class wikiBoxRowMetadata extends tableEditMetadata{
	function __construct() {
		$this->table_name = 'ext_TableEdit_row_metadata';
		$this->primary_key_field = 'row_metadata_id';
		$this->foreign_key_field = 'row_id';
		$this->metadata_field = 'row_metadata';
		parent::__construct();
		return true;
	}
	
    function delete(){
		$dbw =& wfGetDB( DB_MASTER );
		return $dbw->delete(
			$this->table_name,
			array( $this->primary_key_field . ' = ' . $this->row_metadata_id ),
			__METHOD__
		);
	}
}

class wikiBoxMetadata extends tableEditMetadata{

	function __construct() {
		$this->table_name = 'ext_TableEdit_box_metadata';
		$this->primary_key_field = 'box_metadata_id';
		$this->foreign_key_field = 'box_id';
		$this->metadata_field = 'box_metadata';
		parent::__construct();
		return true;
	}

	function delete(){
		$dbw =& wfGetDB( DB_MASTER );
		return $dbw->delete(
			$this->table_name,
			array( $this->primary_key_field . ' = ' . $this->box_metadata_id ),
			__METHOD__
		);
	}
}

class tableEditMetadata{

	function __construct(){
		$this->is_current = true;
	}

	function db_save(){
		$changed = 0;
		$primary_key = $this->primary_key_field;
		$foreign_key = $this->foreign_key_field;
		$metadata = $this->metadata_field;
		$dbw =& wfGetDB( DB_MASTER );
		if ($this->metadata == '') return 0; # don't save empty
		if ( !isset($this->$primary_key) || $this->$primary_key == '' ) {
			$a = array(
				$foreign_key   	=>	$this->$foreign_key,
				$metadata      	=>	$this->metadata,
				'timestamp'    	=>	time()
				);
			$result = $dbw->insert($this->table_name,$a,__METHOD__);
			$this->$primary_key = $dbw->insertId();
			$changed = 1;
		}
		elseif ( $this->is_current === true ) {
			# it's in the DB and it's current, update it
			if (!isset ($this->original) || $this->original != $this->metadata){
				$a = array(
					$metadata      	=>	$this->metadata,
					'timestamp'    	=>	time()
					);

				$result = $dbw->update($this->table_name, $a, array($primary_key." = '".$this->$primary_key."'"), __METHOD__);
				$changed = 1;
			}
		}
		else {
			#it's in the DB but it's not current.  Delete it from the DB
			$result = $dbw->delete( $this->table_name, array($primary_key." = '".$this->$primary_key."'"), __METHOD__);
			$changed = 1;
		}
		return $changed;
	}
	
	function save() {
		return $this->db_save();
	}

	
}

function NML ($data) {
    print_r(date('l jS \of F Y h:i:s A')."<br><u>NML Debug:</u><pre>");
    var_dump( $data );
    die();
}
