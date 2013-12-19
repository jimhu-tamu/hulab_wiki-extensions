<?php
/*
 * TableEditCategoryTags.php - An extension 'module' for the TableEdit extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.1
 * @copyright Copyright (C) 2013 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEditCategoryTags',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Add links to table entries for TableEdit.',
    'version'=>'0.1'
);

/**
This abstract class provides a number of generic methods to collect places where category links should be added
based on text in tables.

Classes extending this should hook to TableEditBeforeSave. Whenever any tableEdit table changes, we
regenerate a set of Categories at the end of the TableEdit wikitext.
*/


abstract class TableEditCategoryTags{
	
	function __construct(TableEdit $te, $table, wikiBox $box){

	}
	
	public static function add_tags(TableEdit $te, $table, wikiBox $box){		
		$tagarr = self::do_taglist($table, $box);	
		$table .= "<noinclude>\n".implode("\n",array_unique($tagarr))."\n</noinclude>\n";
		return true;
	}

	/*
	overload this to use or not use common tag methods
	$box is passed to allow child classes to query the box properties in 
	addition to the text
	return array of category tags
	*/
	public static function do_taglist($table, $box){
		$lc = LookupCollection::getInstance();
		$tagarr = array();
		$stripped_table = preg_replace('/\[.*\]/','', $table);
		$tagarr = self::go_terms($stripped_table, $tagarr, $lc);
		return $tagarr;
	}
	
	/*
	Make tags for GO terms, using OntlologyDBI. Fall back to GONUTS web service.
	params
		$table = text of a table
		$tagarr = array of existing category tags; go term tags will be appended to this
	returns
		$tagarr with added tags
	*/	
	static function go_terms($table, $tagarr = array(), LookupCollection $lc = null){
		# Find GO terms
		$pattern = "/GO:\d+/";
		preg_match_all($pattern, $table, $matches);
		# short circuit if no matches
		if(!empty($matches)){
			foreach (array_unique($matches[0]) as $go_id){
				if(!is_null($lc)){
					$term = $lc->get_obo_term($go_id);
					$page = $go_id."_!_".$term->name;
				}else{
					$page = self::go_terms_ws($go_id);						
				}
				$tagarr[] = "[[Category:$page]]";
			}
		}	
		return $tagarr;
	}
	
	/*
	Make tags for GO terms, using GONUTS web service.
	params
		$go_id
	returns
		category tag or empty string
	
	GONUTS web service Sample API query
	http://gowiki.tamu.edu/wiki/api.php?action=query&list=allcategories&acprefix=GO:0000003&format=json
	*/

	public static function go_terms_ws($go_id){
		$api_result = new simplexml_load_file("http://gowiki.tamu.edu/wiki/api.php?action=query&list=allcategories&acprefix=$go_id&format=xml",
			'SimpleXMLElement', LIBXML_NOCDATA); 
		if(isset($api_result->query->allcategories->c[0])){
			$page = str_replace(" ","_", $api_result->query->allcategories->c[0]);
			return $page;
		}	
		return "";
	}

	/*
	Get pfam clan descriptions from pfam local file
	fall back on web service if no directory is set
	*/	
	public static function pfam($table, $tagarr = array(), PfamLookup $pf = null){
		$pattern = "/(PF\d+)(.*)/";
		preg_match_all($pattern, $table, $matches);
		if(!empty($matches)){
			foreach (array_unique($matches[1]) as $match){
				if(isset($pf)){
					$desc = trim($pf->get_clans_field($match)); 
				}else{
					$desc= self::pfam_ws($match);
				} 
				$tagarr[] = "[[Category:Pfam $match $desc]]";
			}
		}	
		return $tagarr;
	}
	
	/*
	Get pfam clan description from pfam web service	
	sample query
	http://pfam.janelia.org/family/PF02929/?output=xml	
	*/
	public static function pfam_ws($match){
		trigger_error(__METHOD__." is trying Pfam web service");
		$api_result = simplexml_load_file("http://pfam.janelia.org/family/$match/?output=xml", 
			'SimpleXMLElement', LIBXML_NOCDATA);
		return  trim($api_result->entry->description);
	}	
}
