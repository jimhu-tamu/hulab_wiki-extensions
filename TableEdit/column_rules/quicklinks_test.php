<?php
# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEdit:quicklinks',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Special column type for genome quicklinks',
    'version'=>'0.1'
);


# Register hooks ('TableEditApplyColumnRules' hook is provided by the TableEdit extension).
$wgHooks['TableEditApplyColumnRules'][] = 'efTableEditQuickLinksColumn';

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

class ecTableEditQuicklinks{

	function __construct($box, $rule_fields){
		$this->box = $box;
		$this->rule_fields = $rule_fields;
		return true;
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

	function links(){
		$dbr = & wfGetDB( DB_SLAVE );
		$tmp = explode(':',$this->box->page_name);

		#print_r($tmp);
		if (isset($tmp[1]) && in_array($tmp[1], array('Quickview','Gene','Gene_Product(s)','Expression','Evolution'))){
			#echo "it's a gene!";
			$page_cluster_name  = $tmp[0];
			$tmp2 = explode('_', $tmp[0]);
			$gene_name = array_pop($tmp2);
			$organism = implode('_', $tmp2);
			if ($organism == '') $organism = 'escherichia_coli';

			# get the protein sequence from the protein phys prop table
			$result = $dbr->select(
				array('ext_TableEdit_box','ext_TableEdit_row'),
				'row_data',
				array(
					"ext_TableEdit_box.box_id=ext_TableEdit_row.box_id",
					"template = 'Product_phys_prop_table'",
					"page_name='$page_cluster_name:Gene_Product(s)'"
					),
				__METHOD__
			);
			$x = $dbr->fetchObject ( $result );
			$tmp = explode('||', $x->row_data);
			$aaseq = strtolower(strip_tags($tmp[0]));
			$aaseq = preg_replace("/\[.*?\]/",'',$aaseq);
			$aaseq = preg_replace("/[^acdefghiklmnpqrstvwy]/",'',$aaseq); #echo "$aaseq";

			# get genome accessions, start, and end
			$accession = 'NC_000913';
			if (is_file("/usr/local/fasta/$organism".".fasta")) $accession = $organism;
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
				$locs[] = htmlentities("$orgkey:$start..$end");
			}
		}

		# build the links.  Ignore whatever is already in the field
		$links = array();
		foreach ($this->rule_fields as $item){
			switch ($item){
				case 'dna':
					#$links[] = "[http://ecoliwiki.net/tools/display_dna.php?f=$accession&start=$start&end=$end&gene=$gene_name DNA display]";
					$params = "loc=".implode(",",$locs);
					$links[] = "[http://ecoliwiki.net/tools/display_dna.php?gene=$gene_name&$params DNA display]";
					break;
				case 'protein':
					$links[] = "[http://ecoliwiki.net/tools/display_protein.php?f=$accession&start=$start&end=$end&gene=$gene_name&aa=$aaseq Protein display]";
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
						$links[] = "[http://www.ncbi.nlm.nih.gov/sites/entrez?db=pubmed&cmd=Search&Term=$gene_name+AND+$organism Pubmed($gene_name)]";
					}
					break;
				case 'textpresso':
					if (isset($gene_name)) {
						$links[] = "[http://trimer.tamu.edu/cgi-bin/textpresso/dev/search?q=$gene_name Textpresso($gene_name)]";
					}
					break;

				default:
					break;
			}
		}
		#echo "<pre>";print_r($links);		echo "</pre>";
		return implode ('&nbsp;&nbsp;&nbsp; ',$links);
	}

}
