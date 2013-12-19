<?php
# Credits
/*
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEdit:quicklinks',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Special column type for genome quicklinks',
    'version'=>'0.1'
);


/* Register hooks  
# 'TableEditApplyColumnRules' hook is provided by the TableEdit extension.
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEditQuickLinksColumn';
#Unit tests for  
$wgHooks['UnitTestsList'][] = 'efQuicklinksRegisterUnitTests';

function efQuicklinksRegisterUnitTests( &$files ) {
    $testDir = dirname( __FILE__ ) . '/tests/';
    $files[] = $testDir . 'columnRuleTest.php';
    return true;
}



function efTableEditQuickLinksColumn( $te, $rule_fields, $box, $row_data, $i, $type ){
	global $wgParser, $wgTitle, $wgUser;
	if (!in_array($rule_fields[0], array( 'quicklinks')) ) return true;

	$qlinks = new ecTableEditQuicklinks($box, $rule_fields);

	$row_data[$i] = $qlinks->links();

	if ($type == 'EDIT' ){
		$row_data[$i] = $wgParser->parse(
			$row_data[$i],
			$wgTitle,
			ParserOptions::newFromUser( $wgUser )
		)->getText();
		$row_data[$i]= $row_data[$i].Html::hidden('field[]',$row_data[$i]);
	}
	$type = 'quicklinks';
	return true;
}
*/
class ecTableEdit_quicklinks extends TableEdit_Column_rule{

	function __construct($te, $box, $rule_fields, $row_data, $col_index){
		parent::__construct($te, $box, $rule_fields, $row_data, $col_index);
	}

	function make_form_row(){
		global $wgParser, $wgTitle, $wgUser;
 		return Html::hidden('field[]',$this->col_data).
 			$wgParser->parse(
			$this->col_data,
			$wgTitle,
			ParserOptions::newFromUser( $wgUser )
		)->getText();
	}

	# overload to make the data a list
	function show_data(){
		return $this->col_data;
	}

	function get_gene_lists(){
		$dbr = & wfGetDB( DB_SLAVE );
		$list = array();
		$result = $dbr->select(
			'page',
			'page_title',
			"page_title LIKE 'Gene_List:%'",
			__METHOD__
		);
		echo $dbr->lastQuery();
		while( $x = $dbr->fetchObject ( $result ) ) {
			$list[] = $x->page_title;
		}
		print_r($list);

		return $list;
	}

	function preprocess(){
		$this->col_data = implode( ' ', $this->links_as_array() );
		return true;
	}

	public function links_as_array() {
		list($page_cluster_name, $page_type) = explode(':',$this->box->page_name.":");

		#print_r($tmp);
		if (in_array($page_type, array('Quickview','Gene','Gene_Product(s)','Expression','Evolution'))){
			#echo "it's a gene!";
			# handle cluster names like Phage_lambda_N
			$tmp2 = explode('_', $page_cluster_name);
			$gene_name = array_pop($tmp2);
			$organism = implode('_', $tmp2);
			if ($organism == '') $organism = 'escherichia_coli';

			# get the protein sequence from the protein phys prop table
			$aaseq = $this->protein_sequence($page_cluster_name);

			# get genome accessions, start, and end
			$accession = 'NC_000913';
			if (is_file("/usr/local/fasta/$organism".".fasta")) $accession = $organism;
			$dbr = & wfGetDB( DB_SLAVE );
			$result = $dbr->select(
				array('ext_TableEdit_box','ext_TableEdit_row'),
				'row_data',
				array(
					"ext_TableEdit_box.box_id=ext_TableEdit_row.box_id",
					"template = 'Gene_location_table'",
					"page_name='$page_cluster_name:Gene'"
					),
				__METHOD__
			);
		
			$locs = array();
			while($x = $dbr->fetchObject ( $result )){
				$tmp = explode('||', $x->row_data);
				$orgkey = trim(str_replace(array('[',']'),'',$tmp[0]));
				if ($orgkey == ''){
					$orgkey = $organism;
				}
				preg_match('/\:\s*(\d+)\.\.(\d+)/',$tmp[2], $coord);# print_r($coord);
				$start = trim($coord[1]);
				$end = trim($coord[2]);
				/* Some of the row data in the sql tables 
				has spaces. We need to standardize to use
				underscores so the url finds the correct 
				fasta file
				*/
				$orgkey = str_replace(' ','_',$orgkey);
				$locs[] = urlencode("$orgkey:$start..$end");
			}
		}

		# build the links.  Ignore whatever is already in the field
		$links = array();
		foreach ($this->rule_fields as $item){
			switch ($item){
				case 'dna':
					$params = "loc=".implode(",",$locs);
					#$links[] = "[http://ecoliwiki.net/tools/display_dna.php?gene=$gene_name&$params DNA display]";
					$links[] = "[[File:dnadisplay.png|link=http://ecoliwiki.net/tools/display_dna.php?gene=$gene_name&$params]]";
					break;
				case 'protein':
					#$links[] = "[http://ecoliwiki.net/tools/display_protein.php?f=$accession&start=$start&end=$end&gene=$gene_name&aa=$aaseq Protein display]";
					if($aaseq != ''){
						$links[] = "[[File:proteindisplay.png|link=http://ecoliwiki.net/tools/display_protein.php?f=$accession&start=$start&end=$end&gene=$gene_name&aa=$aaseq]]&nbsp;&nbsp; ";
					}	
					break;
				case 'google':
					$q = '';
					if (isset($gene_name)) $q = "/search?q=$gene_name";
					$links[] = "[http://google.com$q Google]";
					break;
				case 'seqsearcher':
					if(isset($aaseq) && $aaseq != '') $links[] = "[http://ecoliwiki.net/tools/seqsearcher/index.php?sequence=$aaseq&database=6_base_or_longer_sticky.txt SeqSearcher]";
					break;
				case 'pubmed':
					if (isset($gene_name)){
						$organism = str_replace('_','+',$organism);
						$organism = str_replace('Phage','bacteriophage',$organism);
						#$links[] = "[http://www.ncbi.nlm.nih.gov/sites/entrez?db=pubmed&cmd=Search&Term=$gene_name+AND+$organism Pubmed($gene_name)]";
						$links[] = "[[File:pubmed.jpg|link=http://www.ncbi.nlm.nih.gov/sites/entrez?db=pubmed&cmd=Search&Term=$gene_name+AND+$organism]]&nbsp;&nbsp; ";
					}
					break;
				case 'textpresso':

                    /**
                     *  Adding a variable for the link to textpresso. As of late June 2011, the Hu lab has it's own URL for textpresso,
                     *    meaning that this variable below should never change unless the URL changes. 
                     *
                     *  --DPR 06-27-2011
                     */
                    $textpresso_url = 'http://textpresso.tamu.edu';

					if (isset($gene_name)) {
						#$links[] = "[".$textpresso_url."/cgi-bin/textpresso/search?q=$gene_name Textpresso($gene_name)]";
						$links[] = "[[File:textpresso.jpg|link=$textpresso_url/cgi-bin/textpresso/search?q=$gene_name]]&nbsp;&nbsp; ";
					}
					break;
				case 'porteco':
					if (isset($gene_name)) {
						$links[] = "[[File:porteco.png|link=http://porteco.org/AjaxSearch.jsp?searchString=$gene_name&s=search]]";
					}
					break;
				case 'ecocyc':
					if (isset($gene_name)) {
						$acc = self::get_gene_accession($page_cluster_name, 'EcoCyc', $dbr);
						# don't add if there is no accession
						if($acc != ''){
							$links[] = "[[File:EcoCyc.gif|link=http://biocyc.org/ECOLI/NEW-IMAGE?type=NIL&object=$acc]]";
						}	
					}
					break;
				case 'regulondb':
					if (isset($gene_name)) {
						$acc = self::get_gene_accession($page_cluster_name, 'RegulonDB', $dbr);
						# don't add if there is no accession
						if($acc != ''){ 
							$links[] = "[[File:regulondb.jpg|link=http://regulondb.ccg.unam.mx/gene?term=$acc&organism=ECK12&format=jsp&type=gene ]]";
						}
					}
					break;
				case 'br':
					$links[] = "<br/><br/>";
					break;	
				default:
					break;
			}
		}

		return $links;
	}
	
	public function get_gene_accession($page_cluster_name, $db, $dbr){
		$result = $dbr->select(
			array('ext_TableEdit_box', 'ext_TableEdit_row'),
			'row_data',
			array(
				"ext_TableEdit_box.page_name = '$page_cluster_name:Gene'",
				"ext_TableEdit_box.template = 'Gene_accessions_table'",			
				"ext_TableEdit_box.box_id = ext_TableEdit_row.box_id",
				"ext_TableEdit_row.row_data LIKE '%||$db%||%'"
			),
			__METHOD__		
		);
		$x = $dbr->fetchObject($result);
		if(!is_object($x)) return "";
		list($dblink, $xref, $notes) = explode('||', $x->row_data."||||");
		list($prefix, $acc) = explode(':', $xref);
		return $acc;
		
	}

	public function _sql_box_row_data($dbr,$conds) {
	    return $dbr->select(
		    array('ext_TableEdit_box','ext_TableEdit_row'),
		    'row_data',
		    array(
			    "ext_TableEdit_box.box_id=ext_TableEdit_row.box_id",
			    "template = 'Product_phys_prop_table'",
			    $conds
			    ),
		    __METHOD__
	    );
	}


	public function protein_sequence($page_cluster_name) {
	    $dbr = & wfGetDB( DB_SLAVE );
	    $result = $this->_sql_box_row_data($dbr,
		"page_name='$page_cluster_name:Gene_Product(s)'");

	    $x = $dbr->fetchObject ( $result );
	    $phys_prop = $this->product_physical_properties();

	    return $this->_protein_sequence($phys_prop,$x);
	}

	public function _protein_sequence($phys_prop,$x) {
	    $iSeqFld = $phys_prop['sequence'];
	    $aFldData = explode('||', $x->row_data);

	    $aaseq = strtolower(strip_tags($aFldData[$iSeqFld]));
	    $aaseq = preg_replace("/\[.*?\]/",'',$aaseq);
	    $aaseq = preg_replace("/[^acdefghiklmnpqrstvwy]/",'',$aaseq); #echo "$aaseq";
	    return $aaseq;
	}

	public function product_physical_properties() {
	    $title = Title::newFromText("Template:Product_phys_prop_table");
	    $article = new Article( $title );
	    $text = $article->fetchContent();
	    $xml = simplexml_load_string('<xml>'.$text.'</xml>');

	    $headings = explode("\n", trim((string) $xml->headings));
	    return array_change_key_case(array_flip($headings), CASE_LOWER);
	}


}
