<?php
/*
Base class for creating TableEdit column rules.

See the README for additional information about writing TableEdit column rules.

*/


# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEdit:column_rule',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Abstract class for column rules',
    'version'=>'0.1'
);

#  Autoload this class
#$wgAutoloadClasses['TableEdit_Column_rule'] = dirname(__FILE__) . '/class.column_rule.php';


abstract class TableEdit_Column_rule{
	/*
		$te,           object. Needed to get the action request
		$box,
		$rule_fields,
		$row_data,
		$col_index){

	*/
	function __construct($te, $box, $rule_fields, $row_data, $col_index){

		$this->error = "";
		$this->te = $te; 						// obj
		$this->box = $box;						// obj
		$this->rule_fields = $rule_fields;		// array
		$this->row_data = $row_data;			// array
		$this->col_data = $row_data[$col_index];			// string
		$this->col_index = $col_index; 			// need this to make a form, int
		$this->col_name = $box->column_names[$col_index];
		# convert $row_data from an array to a hash indexed on headings field names
		$this->update_row_hash();
		return true;
	}

	function update_row_hash(){
		if(!is_array($this->box->column_names)) die("<pre>col_names".print_r($this->box, true)."</pre>");
		foreach ($this->box->column_names as $i => $name){
			$value = "";
			if(isset($this->row_data[$i])) $value = $this->row_data[$i];
			$this->row_hash[$this->box->column_names[$i]] = $value;
		}
	}

	function dump(){
		echo "<pre>";print_r($this);echo $this->te->act['act']. "</pre>";

	}

	function execute(){
		# run method to do something before everything else
		$this->preprocess();

        if ( !isSet($this->te) || !isSet($this->te->act['act'])  ) {
            return $this->show_data();
        }

		$act = $this->te->act['act'];
		switch ($act){
			case 'Save':
            case 'Save Row':
            case wfMsg('save'):
            case wfMsg('save-row'):
				#echo "saving row to nav view";

				$this->col_data = $this->show_data();

                $hidden = "";
				//$hidden = Html::hidden( "field[$this->col_index]", $this->col_data );

				return $hidden.$this->col_data;
				break;

			default:
				# make form if in edit_row view and not saving back to nav.
				$text = $this->make_form_row();
				return $this->error.$text;
				echo "<pre>act: $act</pre>";
		}
		return true;
	}

	# overload this to make your form
	function make_form_row(){
		return TableEditView::form_field($this->col_index, $this->col_data,40 ,'input');

	}

	# overload this to manipulate what shows in the nav view
	function show_data(){
		return $this->col_data;
	}

	# overload this to do common things for form and show
	function preprocess(){
		return true;
	}

	# ********* Utility methods ****************

	# parse wikitext.
	function parse_wikitext($text){
		global $wgParser, $wgTitle, $wgUser;

		$text = $this->show_data();
		$dtext = $wgParser->parse(
			$text,
			$wgTitle,
			ParserOptions::newFromUser( $wgUser )
		)->getText();
		return $dtext;
	}

	function tag_parse_wikitext($text){
		global $wgParser;
		$dtext = $wgParser->recursiveTagParse($text);
		return $dtext;
	}

	# Look up a column value from another table
/*
	function get_column_data($field_name, $table = '', $page = '', $conds = array()){
		if($page == '' && $table == ''){
			return $this->row_hash[$field_name];
		}else{
			$arr =array();
			$dbr =& wfGetDB( DB_SLAVE );
			$where = array("page_name = \"$page_name\"","template = '$table'");

			$result = $dbr->select('ext_TableEdit_box','box_uid',$where,__METHOD__);
			while ($x = $dbr->fetchObject ( $result )){
				$box = new wikiBox;
				$box->box_uid = $x->box_uid;
				$box->set_from_DB();
				foreach ($box->rows as $row){
					$arr[] = explode("||",$row->row_data);
				}

			}
			return #


		}
	}
*/



}
