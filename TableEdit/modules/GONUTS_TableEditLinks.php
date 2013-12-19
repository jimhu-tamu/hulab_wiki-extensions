<?php
/*
 * GONUTS_TableEditLinks.php - An extension 'module' for the TableEdit extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.2
 * @copyright Copyright (C) 2007, 2013 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'			=>'GONUTS_TableEditLinks',
    'author'		=> array(
							'Jim Hu &lt;jimhu@tamu.edu&gt;', 
							'[mailto:bluecurio@gmail.com Daniel Renfro]'
						),
    'description'	=>'Add links to table entries for TableEdit in GONUTS.',
    'version'		=>'0.2'
);


# Register hooks ('PagesOnDemand' hook is provided by the PagesOnDemand extension).
$wgHooks['ParserAfterStrip'][] = 'efTableEditLinks';
$wgHooks['TableEditCheckConflict'][] = 'efTableEditGONUTSStripLinks';

/**
* Loads a demo page if the title matches a particular pattern.
* @param Title title The Title to check or create.
*/
function efTableEditLinks( &$parser, &$text, &$strip_state ){
	$l = new TableEditGONUTSLinker($text);
	$text = $l->execute();
	return true;
}

class TableEditGONUTSLinker extends TableEditLinker{
	
	function generic_links($table){
		$table = self::pmid_links($table);
		$table = self::gonuts_links($table);
		$table = self::go_ref_links($table);
		$table = self::dbxref_links($table);
		return $table;
	}
	
	
	function table_specific_links($table, $template = ''){

		// ==== table-specific regexes ============================	
		switch ($template){
			case "Annotation_Headings":
				// IEA rows
				$rows = array();
				$tmp = explode("\n|-",$table);
				foreach ($tmp as $row){
					if (strpos($row,"\nIEA:") > 0) $row = "style='background:#ddffdd;' ".$row;
					$rows[] = $row;
				}
				$table = implode("\n|-",$rows);					
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
				$page = $match."_!_".str_replace(" ","_", Annotation::get_go_term($match));	
				$page = self::fix_title($page);
				$replacement = "[[Category:$page]][[:Category:$page|$match]]";			
				$table = preg_replace("/([\|\s])$match/", "$1$replacement", $table);
		#		}
			}catch (Exception $e){
				# don't crash when not connected to the internet or when api call fails
			}
		}		
		return $table;
	}

# end class
}

function efTableEditGONUTSStripLinks( $article, $table, $box ){

	#strip GO links
	$table = preg_replace("/\[\[Category:.*?\]\]\[http:\/\/gowiki.tamu.edu\/wiki\/index.php\/Category:(GO:\d+)_!_.*?\]/","$1",$table);
	#strip old GO links
	$table = preg_replace("/\[\[Category:.*?\]\]\[\[:Category:.*?\|(GO:\d+)\]\]/","$1",$table);
	#strip PMID links
	$table = preg_replace("/\[\[(PMID:\d+)\]\]<ref name=\".*?\"s+\/>/","$1",$table);
	#strip GO_REF links
	$table = preg_replace("/\[http:\/\/www\.geneontology\.org\/cgi-bin\/references.*? (GO_REF:\d+)\]/", "$1", $table);
	
	$table = str_replace("[http://www.ebi.ac.uk/GOA/compara_go_annotations.html GOA:compara]", "GOA:compara", $table);
	$table = str_replace("[http://www.ebi.ac.uk/GOA/InterPro2GO.html GOA:interpro]", "GOA:interpro", $table);
	
	if(isset($box->template) && $box->template == "Annotation_Headings"){
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
	
	return true;
}
