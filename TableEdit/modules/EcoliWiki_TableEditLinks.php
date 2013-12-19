<?php
/*
 * TableEditLinks.php - An extension 'module' for the TableEdit extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.2
 * @copyright Copyright (C) 2007 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'			=>'EcoliWiki_TableEditLinks',
    'author'		=> array(
		'Jim Hu &lt;jimhu@tamu.edu&gt;',
		'[mailto:bluecurio@gmail.com Daniel Renfro]'
	),
    'description'	=>'Add links to table entries for TableEdit in EcoliWiki.',
    'version'		=>'0.2'
);


# Register hooks
$wgHooks['ParserBeforeStrip'][] = 'efTableEditLinks';
$wgHooks['TableEditCheckConflict'][] = 'efTableEditStripLinks';
$wgHooks['UnitTestsList'][] = 'efTableEditLinksRegisterUnitTests';

function efTableEditLinksRegisterUnitTests( &$files ) {
    $testDir = dirname( __FILE__ ) . '/tests/';
    $files[] = $testDir . 'tableEditLinksTest.php';
    return true;
}

/**
* Loads a demo page if the title matches a particular pattern.
* @param Title title The Title to check or create.
*/
function efTableEditLinks( &$parser, &$text, &$strip_state ){
	$l = new TableEditEcoliWikiLinker($text);
	$text = $l->execute();
	return true;
}

class TableEditEcoliWikiLinker extends TableEditLinker{

	function generic_links($table){
		// ==== table-agnostic regexes ============================
		$table = self::pmid_links($table);
		$table = self::go_ref_links($table);
		$table = self::dbxref_links($table);
		$table = self::gonuts_links($table);
		return $table;

	}

	function table_specific_links($table, $template = ''){

		// ==== table-specific regexes ============================
		/*
			These next sets need a slightly more powerful regular expression. This complicates
			the code - the array_unique() call gets turned into a check against an array of things
			that we've already seen ($things_seen). This keeps from recursively replacing things...
			which can get ugly very quickly.
		*/
		$stripped_table = preg_replace('/\[.*\]/','', $table);
		$things_seen = array();
		
		#array of table rows
		$rows = explode("\n|-",$stripped_table);
		# Shift off the tableEdit header.
		array_shift($rows);
		# If horizontal, Shift off the headings row.
		if(strpos($rows[0], '!!') > 0) array_shift($rows);
		# pop off the TableEdit footer
		array_pop($rows);
		
		switch ($template){
			case "GO_table_product":
				break;

			case "Product_interactions_table":
				// Do links to EcoliWiki GeneProduct pages
				$pattern = "/Protein\s+\|\s+([a-zA-Z][a-z]{2}[A-Z]{0,1})(\<|\n)/m";
				preg_match_all($pattern, $stripped_table, $m);
				for($i=0, $c=count($m[1]); $i<$c; $i++){
						if(!in_array($m[1][$i], $things_seen)){
							$things_seen[] = $m[1][$i];
							$gene = $m[1][$i];
							$gene{0} = strtolower($gene{0});
							$replacement = "[[$gene:Gene_Product(s)|{$m[1][$i]}]]";
							$table = preg_replace("/{$m[1][$i]}/", "$replacement", $table);
						}
				}
				// Do links to Partner GeneProduct pages
				$pattern = "/(\w+):EBI-\d+/";
				preg_match_all($pattern, $stripped_table, $m);
				for($i=0, $c=count($m[0]); $i<$c; $i++){
						if(!in_array($m[0][$i], $things_seen)){
								$things_seen[] = $m[0][$i];
							   if(preg_match("/[^iI]\w{5}/", $m[1][$i])){
										$replacement = "[http://www.uniprot.org/uniprot/{$m[1][$i]} {$m[1][$i]}]";
							   } else {
										$replacement = "[[{$m[1][$i]}:Gene_Product(s)|{$m[1][$i]}]]";
							   }
							   $table = preg_replace("/{$m[1][$i]}/", "$replacement", $table);
						}
				}
				// Do links to IntAct pages
				$pattern = "/EBI-\d+/";
				preg_match_all($pattern, $stripped_table, $m);
				for($i=0, $c=count($m[0]); $i<$c; $i++){
						if(!in_array($m[0][$i], $things_seen)){
								$things_seen[] = $m[0][$i];
								$replacement = "[http://www.ebi.ac.uk/intact/search/do/search?searchString={$m[0][$i]} {$m[0][$i]}]";
							   $table = preg_replace("/{$m[0][$i]}/", "$replacement", $table);
						}
				}
				break;
			case "MoleculesPerCellTable":
				foreach ($rows as $row){
					$field = self::get_fields_from_row($row);
					if($field[0] != '' && preg_match("/\d/", $field[2]) &&  $field[3] != ''){
						$table .= "[[Category:".$field[0]." expression level data]]"."[[Category:Expression level data]]";
						break;
					}
				}
				break;
			case 'Gene_sequence_features_table':
				foreach ($rows as $row){
					$field = self::get_fields_from_row($row);
					$replacement = '[[' . $field[1] . ']]';
					$table = str_replace( $field[1], $replacement, $table);
				}
				break;
/*
			case 'Gene_location_table':
				foreach ($box->rows as $row){
					$field = explode("||",$row->row_data);

					if( !in_array($field[0], $things_seen) && $field[0] != "" ){
						$things_seen[] = $field[0];

						if ( preg_match('/\[\[.*\]\]/', $field[0]) ) {
							// it's already got double brackets
							continue;
						}

						$replacement = '[[' . $field[0] . ']]';
						$table = preg_replace( "/^" . $field[0] . "/ms", $replacement, $table  );
						#$table = str_replace( $field[0], $replacement, $table);
					}

				}
				break;	*/
			case 'Product_motif_table':
				foreach ($rows as $row){
					$field = self::get_fields_from_row($row); 
					#preg_match("/pfam.*PF\d+ (PF\d+.*)\]/", $field[2], $matches);print_r($matches);
					preg_match("/(PF\d+)(.*)/", $field[2], $matches); 
					if (isset ($matches[1])){
						$replacement = "[[Category:Pfam:".$field[2]."]]" . "[http://pfam.janelia.org/family/".$matches[1]." ".$field[2]."]";
						$table = str_replace( $field[2], $replacement, $table);
					}
				}
				break;
			case 'Gene_resource_table':
				// If we find that this gene is covered by a Kohara Phage,
				//   go ahead and put it in that Phage's category.
				foreach ( $rows as $r ) {
					list( $resource, $type, $source, $notes ) = explode( '||', "$r||||||" );
					if ( $type == 'Kohara Phage' ) {
						if ( preg_match( '/\[\[:(.*?)\|/', $resource, $matches ) ) {
							$replacement = $resource . '[['.$matches[1].']]';
							$table = preg_replace( '/\Q'.$resource.'\E/', $replacement, $table );
						}
					}
				}
				break;

		} # end switch
		return $table;
	}
	

	static function gonuts_links($table){
		$stripped_table = preg_replace('/\[.*\]/','', $table);
		# Do links to GONUTS
		$pattern = "/GO:\d+/";
		preg_match_all($pattern, $stripped_table, $matches);
		foreach (array_unique($matches[0]) as $match){
			try{
				$xml = file_get_contents("http://gowiki.tamu.edu/wiki/api.php?action=query&list=allcategories&acprefix=$match&format=xml");
				$api_result = new SimpleXMLElement($xml); 
				if(isset($api_result->query->allcategories->c[0])){
					$page = str_replace(" ","_", $api_result->query->allcategories->c[0]);
					$replacement = "[[Category:$page]][http://gowiki.tamu.edu/wiki/index.php/Category:$page $match]";
					$table = preg_replace("/([\|\s])$match/", "$1$replacement", $table);
				}
			}catch (Exception $e){	
				# do nothing
				#trigger_error($xml);
			}
		}
		return $table;
	}

}

function efTableEditStripLinks( $article, $table, $box ){

	#strip GO links
	$table = preg_replace("/\[\[Category:.*?\]\]\[http:\/\/gowiki.tamu.edu\/wiki\/index.php\/Category:(GO:\d+)_!_.*?\]/","$1",$table);
	#strip old GO links
	$table = preg_replace("/\[\[Category:.*?\]\]\[\[:Category:.*?\|(GO:\d+)\]\]/","$1",$table);
	#strip PMID links
	$table = preg_replace("/\[\[(PMID:\d+)\]\]<ref name=\".*?\"s+\/>/","$1",$table);
	#strip GO_REF links
	$table = preg_replace("/\[http:\/\/www\.geneontology\.org\/cgi-bin\/references.*? (GO_REF:\d+)\]/", "$1", $table);

	if(isset($box->template) && $box->template == "GO_table_product"){
		#strip SP-KW links
		$table = preg_replace("/\[http:\/\/www\.uniprot\.org\/keywords.*?\s(SP_KW:KW-\d+)\]/", "$1", $table);
		#strip InterPro links
		$table = preg_replace("/\[.*? (InterPro:IPR\d+)\]/", "$1", $table);
		#strip UniProt links
		$table = preg_replace("/\[http:\/\/www\.uniprot\.org\/uniprot.*? (UniProtKB:\w+)\]/", "$1", $table);
		#strip UniProt links
		$table = preg_replace("/\[http:\/\/www\.ebi\.ac\.uk\/chebi\/searchId.*? (CHEBI:\w+)\]/", "$1", $table);
		#strip EC links
		$table = preg_replace("/\[http:\/\/www\.expasy\.org\/cgi-bin\/nicezyme\.pl\?([\.\d]+)(?:\.-)? (EC:\\1)\]/", "$2", $table);
		$table = preg_replace("/\[http:\/\/ca\.expasy\.org\/cgi-bin\/get-entries\?EC=([\.\d]+)\.\*\&view=full\&num=100 (EC:\\1)\]/", "$2", $table);
		# strip HAMAP links
		$table = preg_replace("/\[http:\/\/ca\.expasy\.org\/unirule\/([A-Za-z]{2}_\d+) (HAMAP:\\1)]/", "$2", $table);
	}

	if(isset($box->template) && $box->template == "Product_interactions_table"){
    	#strip partner gene product links
        $table = preg_replace("/\[http:\/\/www\.uniprot\.org\/uniprot\/(.*?) \\1]/", "$1", $table);
        $table = preg_replace("/\[\[(.*?):Gene_Product\(s\)\|\\1\]\]/", "$1", $table);
        #strip IntAct links
        $table = preg_replace("/\[http:\/\/www\.ebi\.ac\.uk\/intact\/search\/do\/search\?searchString=(EBI-\d+)\s\\1\]/", "$1", $table);
	}

	return true;
}
