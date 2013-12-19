<?php

/**
 *	"Quick-Molecules" Column-Rule for TableEdit
 *
 *  ==Explanation==
 *  This column rule will output some properties of either the dna or protein (from the Gene or Product
 *  page, respectively,) along with some optional links associated with the dna/protein.
 *
 *
 *  ==Usage==
 * 	This column rule should be used as follows:
 *
 *    Heading||internal_name|column_name|column_type|link|link|link|...
 *
 *  Where:
 *    Heading          - the heading of the column (gets displayed in the table)
 *    internal_name    - what this column is refered to by other columns (not displayed)
 *    column_name      - the string "quickmolecule"
 *    column_type      - one of:
 *                         (a.) dna
 *                         (b.) protein
 *    link(s)          - any number of pipe-delimited links (defined in quicklinks.php/Bsub_quicklinks.php)
 *
 *
 *
 */

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['TableEdit_Column_rule'] = $dir . 'class.column_rule.php';
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEdit_quickmolecules';


function efTableEdit_quickmolecules( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $wgParser, $wgTitle, $wgUser;
	if (!in_array($rule_fields[0], array( 'quickmolecule')) ) return true;

	$test_rule = new ecTableEdit_quickmolecule($te, $box, $rule_fields, $row_data, $i);

	$row_data[$i] = $test_rule->execute();

	# need to set this to prevent apply_column_rules from overriding and making a regular text box form.
	$type = 'column_rule';
	return true;
}

class ecTableEdit_quickmolecule extends TableEdit_Column_rule {

	public $column_rule = 'quickmolecule';

	function __construct( $te, $box, $rule_fields, $row_data, $col_index ) {
		parent::__construct( $te, $box, $rule_fields, $row_data, $col_index );
	}

	function parse_wikitext($text){
		global $wgParser, $wgTitle, $wgUser;


		// WTF does this line do? It FUBARs everything.....
		// DPR 2010-10-28
		# $text = $this->show_data();

		$dtext = $wgParser->parse(
			$text,
			$wgTitle,
			ParserOptions::newFromUser( $wgUser )
		)->getText();
		return $dtext;
	}


	function make_form_row() {
		global $wgParser, $wgTitle, $wgUser;

		return $this->parse_wikitext( $this->show_data() );
	}

	# overload to make the data a list
	function show_data() {

		list( $gene_name, $page_template )  = explode( ':', $this->box->page_name );
		$this->product_page = "$gene_name:Gene_Product(s)";
		$this->gene_page = "$gene_name:Gene";

		// shift off the column-rule if it's the first thing in the rules
		if ( isSet($this->rule_fields[0]) && $this->rule_fields[0] == $this->column_rule ) {
			array_shift( $this->rule_fields );
		}
		// now, shift off the type of stuff we want to show...
		$column_type = array_shift( $this->rule_fields );

		$text = ""; // to be returned
		switch ( $column_type ) {
			case 'protein':
			case 'protein_with_links':
				$text = $this->protein_with_links();
				break;
			case 'dna':
			case 'dna_with_links':
				$text = $this->dna_with_links();
				break;
			default:
				break;
		}
		return $text;
	}

	function get_results($result){
		return $arr;
	}

	public function protein() {
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select(
			'ext_TableEdit_box',
			'box_uid',
			array(
				"page_name = \"" . $this->product_page . "\"",
				"template = 'Product_phys_prop_table'"
			),
			__METHOD__
		);
		while ($x = $dbr->fetchObject ( $result )){
			$box = new wikiBox;
			$box->box_uid = $x->box_uid;
			$box->set_from_DB();
			foreach ($box->rows as $row){
				$arr[] = explode("||",$row->row_data);
			}

		}
		$text = "'''Properties'''\n";
		$text .= "* ".$arr[0][1]." aa\n";
		$text .= "* MW:".$arr[0][2]." \n";
		$text .= "* pI:".$arr[0][3]." \n";
		return $text . "\n";
	}

	public function dna() {
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select(
			'ext_TableEdit_box',
			'box_uid',
			array(
				"page_name = \"" . $this->gene_page . "\"",
				"template = 'Gene_location_table'"
			),
			__METHOD__
		);
		$arr = array();
		while ($x = $dbr->fetchObject ( $result )){
			$box = new wikiBox;
			$box->box_uid = $x->box_uid;
			$box->set_from_DB();
			foreach ($box->rows as $row){
				$tmp = explode("||",$row->row_data);
				if($tmp[0] == 'subtilis 168') $arr[] = $tmp;
			}
		}
		# need to deal with the possibility of multiple locations
		$text = "'''Properties'''\n";
		foreach ( $arr as $r ) {
			preg_match('/:\s?(\d+)\.\.(\d+)/',$r[2], $coords);
			if(isset($coords[2]) ){
				$length = 1+abs($coords[2] - $coords[1]);
				$text .= "* ".$coords[1]."..".$coords[2]." ($length bp)\n";
			}
		}
		return $text;
	}

	public function dna_with_links() {
		$text = "";
		$text .= $this->dna();
		if ( !class_exists('ecTableEditQuicklinks') ) {
			$text .= $this->format_error('The quicklinks column-rule is not installed.');
		}
		else {
			if ( count($this->rule_fields) > 0 ) {
				$text .= "'''Links'''\n\n";
				$c = new ecTableEditQuicklinks( $this->box, $this->rule_fields, $this->row_data );
				$links = $c->links_as_array();
				foreach ( $links as $link ) {
					$text .= "* " . $link . "\n";
				}
			}
		}
		return $text;
	}

	public function protein_with_links() {
		$text = "";
		$text .= $this->protein();
		if ( !class_exists('ecTableEditQuicklinks') ) {
			$text .= $this->format_error('The quicklinks column-rule is not installed.');
		}
		else {
			if ( count($this->rule_fields) > 0 ) {
				$text .= "'''Links'''\n\n";
				$c = new ecTableEditQuicklinks( $this->box, $this->rule_fields, $this->row_data );
				$links = $c->links_as_array();
				foreach ( $links as $link ) {
					$text .= "* " . $link . "\n";
				}
			}
		}
		return $text;
	}

	public function format_error( $str ) {
		return sprintf("\n<span style=\"color:red; font-weight:bold;\">%s</span>\n", $str);
	}

}