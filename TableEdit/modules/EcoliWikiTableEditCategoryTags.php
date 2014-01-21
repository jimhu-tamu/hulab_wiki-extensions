<?php
/*
 * EcoliWikiTableEditCategoryTags - An extension 'module' for the TableEdit extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.1
 * @copyright Copyright (C) 2013 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'			=>'EcoliWikiTableEditCategoryTags',
    'author'		=> array(
		'Jim Hu &lt;jimhu@tamu.edu&gt;',
	),
    'description'	=>'Add category tags for table entries for TableEdit in EcoliWiki.',
    'version'		=>'0.1'
);


# Register hooks
$wgHooks['TableEditBeforeSave'][] = 'EcoliWikiTableEditCategoryTags::add_tags';

class EcoliWikiTableEditCategoryTags extends TableEditCategoryTags{

	public static function add_tags(TableEdit $te, $table, wikiBox $box){		
		$tagarr = self::do_taglist($table, $box);	
		$table .= "<noinclude>\n".implode("\n",array_unique($tagarr))."\n</noinclude>\n";
		return true;
	}

	public static function do_taglist($table, $box){
		$tagarr = parent::do_taglist($table, $box); 
		$lc = LookupCollection::getInstance();
		switch($box->template){
			case "Product_motif_table";
				global $wgPfamDataDir;
				if (isset($wgPfamDataDir) && is_dir($wgPfamDataDir)){ 
					$pf = $lc->get_pfamlookup($wgPfamDataDir);
				}
				$tagarr = self::pfam($table, $tagarr, $pf);
				break;
			case "MoleculesPerCellTable":
				foreach ($box->rows as $row){
					$field = self::get_fields_from_row($row);
					if($field[0] != '' && preg_match("/\d/", $field[2]) &&  $field[3] != ''){
						$tagarr[] = "[[Category:".$field[0]." expression level data]]";
						$tagarr[] = "[[Category:Expression level data]]";
						break;
					}
				}
				break;
		}
		return $tagarr;
	}	
	
	

}