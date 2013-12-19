<?php
/*
 * TableEditLinks.php - An extension 'module' for the TableEdit extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.1
 * @copyright Copyright (C) 2007 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEditLinks',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Add links to table entries for TableEdit.',
    'version'=>'0.2'
);

/**
This abstract class provides a number of generic methods to replace common
external links used by biological databases.

*/


abstract class TableEditLinker{
	
	protected $tables = array();
	protected $text = "";
	protected $dbr;
	protected $start;
	protected $title;
	
	function __construct(&$text){
		$this->text = $text;
		preg_match_all('/<\!--box uid=.*-->([\s\S]*)<\!--box uid=.*-->/U', $text, $this->tables);
	}
	
	function execute(){
		foreach ($this->tables[0] as $table){
			$old = $table;
			preg_match('/title=Special:TableEdit\&id=(.*?)\&/',$table, $ids);
			$box = new wikiBox();
			$box->box_uid = $ids[1];
			$box->set_from_DB();
			$table = $this->generic_links($table);
			$table = $this->table_specific_links($table, $box->template);
			$this->text = str_replace($old, $table, $this->text);
		}		
		return $this->text;
	}

	/*
	overload this
	*/

	function generic_links($table){
		return $table;
	}

	/*
	overload this
	*/

	function table_specific_links($table, $template){
		return $table;
	}

	
	static function pmid_links($table){	
		# Do links to PMID pages
		$table = str_replace("PMID:\n",'PMID:', $table);
		$table = preg_replace('/PMID:([ _]|%20)*/','PMID:', $table);
		$pattern = "/(?<![\[\'\"=])PMID:\d+/"; # uses "lookbehind" to find bare-PMIDs

		preg_match_all($pattern, $table, $matches);	
		foreach (array_unique($matches[0]) as $i => $match){
			$replacement = "[[$match]]<ref name='$match' />";
			# replace bare PMIDs
			$table = preg_replace("/(?<![\|\'\"=])$match(?![\]\'\"])/", "$replacement", $table);
			# add ref tag to linked PMIDs w/o ref tag
			$table = preg_replace("/\[\[$match\]\](?!\<ref)/", "$replacement", $table);
		}
		return $table;
	}	
	
	/*
	2013-08-20 change to use Annotation::get_go_term
	*/
	
	static function gonuts_links($table){
		$stripped_table = preg_replace('/\[.*\]/','', $table);
		# Do links to GONUTS
		$pattern = "/GO:\d+/";
		preg_match_all($pattern, $stripped_table, $matches);
		foreach (array_unique($matches[0]) as $match){
				$page = $match."_!_".str_replace(" ","_", Annotation::get_go_term($match));	
				$replacement = "[http://gowiki.tamu.edu/wiki/index.php/Category:$page $match]";
				$table = preg_replace("/([\|\s])$match/", "$1$replacement", $table);
			#}
		}		
		return $table;
	}

	static function go_ref_links($table){	
		$stripped_table = preg_replace('/\[.*\]/','', $table);
		// do GO_REF links to the GO explanation page
		$pattern = "/GO_REF:\d+/";
		preg_match_all($pattern, $stripped_table, $matches);
		foreach(array_unique($matches[0]) as $match){
			$replacement = "[http://www.geneontology.org/cgi-bin/references.cgi#$match $match]";
			$table = preg_replace("/$match/", "$replacement", $table);
		}
		return $table;
	}	

	static function dbxref_links($table){
		$things_seen = array();
		$stripped_table = preg_replace('/\[.*\]/','', $table);
		$patterns = array(
			# SP_KW Swissprot Kewords
			"/SP_KW:(KW-\d+)/"  	=> '[http://www.uniprot.org/keywords/%s %s]',
			"/UniProtKB-KW:(KW-\d+)/"  	=> '[http://www.uniprot.org/keywords/%s %s]',
			"/UniProtKB-SubCell:(SL-\d+)/"  	=> '[http://www.uniprot.org/locations/%s %s]',
			# InterPro
			"/InterPro:(IPR\d+)/" 	=> "[http://www.ebi.ac.uk/interpro/DisplayIproEntry?ac=%s %s]",
			# UniProtKB
			"/UniProtKB:([A-Za-z0-9]+)/" => "[http://www.uniprot.org/keywords/%s %s]",
			# CheBI
			"/CHEBI:(\d+)/"  => "[http://www.ebi.ac.uk/chebi/searchId.do?chebiId=/%s %s]",
			# HAMAP
			"/HAMAP:([A-Za-z]{2}_\d+)/" => "[http://ca.expasy.org/unirule//%s %s]",
			#EcoCyc
			"/EcoCyc:(\S+)/" => "[http://biocyc.org/ECOLI/NEW-IMAGE?type=NIL&object=%s %s]",
			#EcoGene
			"/EcoGene:(EG\d+)/" => "[http://ecogene.org/gene/%s %s]",
			#Echobase
			"/EchoBASE:(EB\d+)/" => "[http://www.york.ac.uk/res/thomas/Gene.cfm?recordID=%s %s]",
			#RegulonDB
			"/RegulonDB:(ECK\d+)/" => "[http://regulondb.ccg.unam.mx/gene?term=%s&organism=ECK12&format=jsp&type=gene %s]",
			#Asap
			"/ASAP:(ABE-\d+)/" => "[https://asap.ahabs.wisc.edu/asap/feature_info.php?FeatureID=%s %s]"
		);
		foreach($patterns as $pattern => $replacement_template){
			preg_match_all($pattern, $stripped_table, $m);
			for($i=0, $c=count($m[0]); $i<$c; $i++){
				if(!in_array($m[0][$i], $things_seen)){
					$things_seen[] = $m[0][$i];
					$replacement = sprintf($replacement_template, $m[1][$i], $m[0][$i]);
					$table = preg_replace("/{$m[0][$i]}/", "$replacement", $table);
				}
			}

		}

		// do EC# links
		$pattern = "/EC:
					   (\d+
						  (?:\.\d+
								 (?:\.\d+
										(?:\.\d+)?
								 )?
						  )?
					   )
					/xms";
		preg_match_all($pattern, $stripped_table, $m);
		for($i=0, $c=count($m[0]); $i<$c; $i++){
			if(!in_array($m[0][$i], $things_seen)){
				$things_seen[] = $m[0][$i];
				$replacement = "";
				if(substr_count($m[1][$i], '.') <= 2){
					// if it's not a 4-digit EC number (with 2 or less dots,) link to the search page
					$replacement = "[http://ca.expasy.org/cgi-bin/get-entries?EC={$m[1][$i]}.*&view=full&num=100 {$m[0][$i]}]"; 
				} else {
					// else, link directly to it's page on expasy
					$replacement = "[http://www.expasy.org/cgi-bin/nicezyme.pl?{$m[1][$i]} {$m[0][$i]}]";
				}
				$table = preg_replace("/{$m[0][$i]}/", "$replacement", $table);
			}
		}
		return $table;
	}
	
	static function get_fields_from_row($row){
		$fields = explode("\n|\n",$row);
		array_shift($fields);
		return $fields;
	}
	
	public static function fix_title($string){
		$string = strip_tags($string);
		$string = str_replace('[','(',$string);
		$string = str_replace(']',')',$string);
		$string = str_replace('{','(',$string);
		$string = str_replace('}',')',$string);
		$string = str_replace("\'","'",$string);
		$string = str_replace("->","-",$string);
		$string = self::replace_sbml($string);
		$string = self::replace_misc($string);
		return utf8_encode($string);
	}

	public static function replace_misc($line){
		#function replaces misc. problematic markup
		#	$line = iconv("","UTF-8",$line); # force lines to utf-8.  Commented out on 11/6/06 since Mike Cherry filters these now on the other end.
		$line = str_replace('& ','&amp; ',$line);
		$line = str_replace('<5>','<sup>5</sup>',$line);
		$line = str_replace('<2+>','<sup>2+</sup>',$line);
		$line = str_replace('<up>','<sup>',$line);
		$line = str_replace('</up>','</sup>',$line);
		$line = str_replace('<down>','<sub>',$line);
		$line = str_replace('</down>','</sub>',$line);
		$line = str_replace('&lt;up&gt;','<sup>',$line);
		$line = str_replace('&lt;/up&gt;','</sup>',$line);
		$line = str_replace('&lt;down&gt;','<sub>',$line);
		$line = str_replace('&lt;/down&gt;','</sub>',$line);
		#remove/replace high order chars
		$chrs = array (chr(150), chr(147), chr(148), chr(146));
		$repl = array ("-", "\"", "\"", "'");
		$line = str_replace($chrs, $repl, $line);
		$line = str_replace('','mu',$line);
		return $line;
	}

	public static function replace_sbml($line){
		$line = str_replace('&alpha;',		'alpha',		$line);
		$line = str_replace('&Alpha;',		'Alpha',		$line);
		$line = str_replace('&beta;',		'beta',			$line);
		$line = str_replace('&Beta;',		'Beta',			$line);
		$line = str_replace('&gamma;',		'gamma',		$line);
		$line = str_replace('&Gamma;',		'Gamma',		$line);
		$line = str_replace('&delta;',		'delta',		$line);
		$line = str_replace('&Delta;',		'Delta',		$line);
		$line = str_replace('&epsilon;',	'epsilon',		$line);
		$line = str_replace('&Epsilon;',	'Epsilon',		$line);
		$line = str_replace('&zeta;',		'zeta',			$line);
		$line = str_replace('&Zeta;',		'Zeta',			$line);
		$line = str_replace('&eta;',		'eta',			$line);
		$line = str_replace('&Eta;',		'Eta',			$line);
		$line = str_replace('&theta;',		'theta',		$line);
		$line = str_replace('&Theta;',		'Theta',		$line);
		$line = str_replace('&iota;',		'iota',			$line);
		$line = str_replace('&Iota;',		'Iota',			$line);
		$line = str_replace('&kappa;',		'kappa',		$line);
		$line = str_replace('&Kappa;',		'Kappa',		$line);
		$line = str_replace('&lamba;',		'lambda',		$line);
		$line = str_replace('&Lamba;',		'Lambda',		$line);
		$line = str_replace('&mu;',			'mu',			$line);
		$line = str_replace('&Mu;',			'Mu',			$line);
		$line = str_replace('&nu;',			'nu',			$line);
		$line = str_replace('&Nu;',			'Nu',			$line);
		$line = str_replace('&xi;',			'xi',			$line);
		$line = str_replace('&Xi;',			'Xi',			$line);
		$line = str_replace('&omicron;',	'omicron',		$line);
		$line = str_replace('&Omicron;',	'Omicron',		$line);
		$line = str_replace('&pi;',			'pi',			$line);
		$line = str_replace('&Pi;',			'Pi',			$line);
		$line = str_replace('&rho;',		'rho',			$line);
		$line = str_replace('&Rho;',		'Rho',			$line);
		$line = str_replace('&sigma;',		'sigma',		$line);
		$line = str_replace('&Sigma;',		'Sigma',		$line);
		$line = str_replace('&tau;',		'tau',			$line);
		$line = str_replace('&Tau;',		'Tau',			$line);
		$line = str_replace('&upsilon;',	'upsilon',		$line);
		$line = str_replace('&Upsilon;',	'Upsilon',		$line);
		$line = str_replace('&phi;',		'phi',			$line);
		$line = str_replace('&Phi;',		'Phi',			$line);
		$line = str_replace('&chi;',		'chi',			$line);
		$line = str_replace('&Chie;',		'Chi',			$line);
		$line = str_replace('&psi;',		'psi',			$line);
		$line = str_replace('&Psi;',		'Psi',			$line);
		$line = str_replace('&omega;',		'omega',		$line);
		$line = str_replace('&Omega;',		'Omega',		$line);
		return $line;
	}	
}
