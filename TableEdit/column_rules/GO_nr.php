<?php
# Column rules for TableEdit templates related to GO annotations

# Credits
$wgExtensionCredits['other'][] = array(
    'name'		=>'TableEdit:go_nr',
    'author'		=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'	=>'Special column type for go annotation completeness',
    'version'		=>'0.1'
);


# Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEditGOnrColumn';

function efTableEditGOnrColumn( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $wgParser, $wgTitle, $wgUser;
	if (!in_array($rule_fields[0], array( 'go_nr')) ) return true;

	$col_type = $box->column_names[$i]; 
	$col = new ecTableEditGOnr($box, $row_data, $i, $col_type, $rule_fields, $type);

	$row_data[$i] = $col->content();

	if ($type == 'EDIT' && $col_type != 'with'){
		//echo "here";
		$row_data[$i] = $wgParser->parse(
			$row_data[$i],
			$wgTitle,
			ParserOptions::newFromUser( $wgUser )
		)->getText();
		$row_data[$i]= $row_data[$i].Html::hidden('field[]',$row_data[$i]);
	} 
	$type = 'go_nr'; #echo "</pre>";
	return true;
}

class ecTableEditGOnr{

	function __construct($box, $data, $col, $col_type, $rules, $type){
		$this->box = $box;
		$this->rdata = $data; 
		$this->col = $col; 
		$this->col_type = $col_type; 
		array_shift($rules);
		$this->rules = $rules;
		$this->type = $type;
		return true;
	}
	function content(){

		return "<GO_nr />";
		$dbr =& wfGetDB( DB_SLAVE );
		# get the box_uid from inside the tags
		$page_name = $this->box->page_name;
		$tmp = explode(':',$page_name);
		$gene_name = $tmp[0];
		$product_page = "$gene_name:Gene_Product(s)";
		$result = $dbr->select('ext_TableEdit_box','box_uid',array("page_name = \"$product_page\"","template = 'GO_table_product'"),__METHOD__);
		//echo "here";
		$annotations = array();
		$text = "";
		while($x = $dbr->fetchObject ( $result )) {
			$box = new wikiBox;
			$box->box_uid = $x->box_uid;
			$box->set_from_DB();
			foreach ($box->rows as $row){
				$data = explode('||', $row->row_data); 
				$evidence = trim($data[array_search('evidence', $box->column_names)]);
				$tmp = explode(':', $evidence);
				$evidence = $tmp[0];
				$qualifier = trim($data[array_search('qualifier', $box->column_names)]);
	
				if (!in_array($qualifier, array('NOT','Obsolete GO term','Under review','Deprecated')) &&
					!in_array($evidence, array('IEA','ND','NR',''))
					) $annotations [trim($data[array_search('aspect', $box->column_names)])]
									[trim($data[array_search('go_id', $box->column_names)])
									." ".trim($data[array_search('go_term', $box->column_names)])]
					[] = $evidence;
			}
			foreach ($annotations as $asp => $terms){
				switch ($asp){
					case 'C':
						$text .= "'''Cellular Component'''\n";
						break;
					case 'F':
						$text .= "'''Molecular Function'''\n";
						break;
					case 'P':
						$text .= "'''Biological Process'''\n";
						break;
				}
				foreach ($terms as $term=>$ev) $text .= "* $term ([http://www.geneontology.org/GO.evidence.shtml ".implode(',',array_unique($ev))."])\n";
			}
		}
	#	$text = print_r($annotations, true);
		return $text; 
	}
}
?>
