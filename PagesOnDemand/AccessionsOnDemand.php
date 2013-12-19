<?php
/*
 * DemoOnDemand.php - An extension 'module' for the PagesOnDemand extension.
 * @author Jim R. Wilson (wilson.jim.r@gmail.com)
 * @version 0.1
 * @copyright Copyright (C) 2007 Jim R. Wilson
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'AccessionsOnDemand',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Not really a pages on demand - redirects to accessions',
    'version'=>'0.1'
);


# Register hooks ('PagesOnDemand' hook is provided by the PagesOnDemand extension).
$wgHooks['PagesOnDemand'][] = 'efAccessionsOnDemand';

/**
* Loads a demo page if the title matches a particular pattern.
* @param Title title The Title to check or create.
*/
function efAccessionsOnDemand( $title, $article ){
	# Short-circuit if $title isn't in the MAIN namespace.
	if ( $title->getNamespace() != NS_CATEGORY && $title->getNamespace() != NS_MAIN ) return true;
	$t = $title->getDBkey();
	if(
		preg_match('/^EcoCyc:/',$t) 	||
		preg_match('/^EcoGene:/',$t) 	||
		preg_match('/^UniProt:/',$t)

	){
		$dbr =& wfGetDB( DB_SLAVE );
		$result = $dbr->select(
			array('ext_TableEdit_box','ext_TableEdit_row','ext_TableEdit_row_metadata'),	# tables
			array('*'),       	# fields
			array(
				"row_metadata ='$t'",
				"page_uid > 0",
				"ext_TableEdit_row.row_id = ext_TableEdit_row_metadata.row_id",
				"ext_TableEdit_box.box_id = ext_TableEdit_row.box_id"
			),	# where
			__METHOD__
		);
#		echo "<pre>xx"; while( $x = $dbr->fetchObject ( $result ) ) {	print_r($x);	} exit;

		if ($dbr->numRows($result) == 1){
			$x = $dbr->fetchObject ( $result );
			$page_uid = $x->page_uid;
		#	print_r($x);
			$title = Title::newFromID($page_uid);
			$article = new Article($title);
			$article->doRedirect();
			return false;
		}

	}else{
		# handle unmapped genes
		if (in_array($t,array('abpS', 'abs', 'acpX', 'acrC', 'adhB', 'aga', 'aroI', 'azaA', 'azaB', 'azl', 'bfm', 'bglT', 'bioP', 'brnR', 'brnS', 'brnT', 'bymA','calA','calC','calD','cdsS','cpsA','cpsC','cpsD','cpsE','cpsF','cpxB','crg','csiA','csiB','csiC','csiF','cup','cxm','cysX','dadQ','dctB','del','dgkR','dif','dinY','dnaI','dppG','dvl','ebgB','envN','envP','envQ','envT','epp','esp','exbC','expA','fatA','fcsA','fexB','fhlB','fipB','fipC','flkB','gadR','garA','garB','glmX','gltE','gltH','gltR','gprA','gprB','gurC','het','hslC','hslD','hslK','hslW','ilvF','ilvR','ilvU','inm','isfA','ksgB','ksgC','ksgD','leuJ','leuR','leuY','lev','linB','lpcB','lrb','lysX','lytA','mafA','mafB','mbrB','meb','mglR','mms','mng','mraA','mul','murH','mutG','nalB','neaB','nfnA','nfrD','non','oppE','opr','ops','oriJ','pac','phxB','poaR','popD','prlZ','proT','pus','qin','qmeC','qmeD','qmeE','ras','rdgA','relX','rer','ridA','ridB','rimB','rimC','rimD','rimE','rimH','rit','rorB','sds','semA','serR','sfiC','sipC','sipD','sir','sloB','slrR','srnA','ssaE','ssaG','ssaH','ssyD','stfZ','stkA','stkB','stkC','stkD','strC','strM','stsA','suhA','sup','tabC','tanA','tanB','tdi','thdA','thdC','thdD','tlnA','tnm','tolD','tolE','tolI','tolJ','tsmA','ups','uro','uvh','uvs'))){
			$title = Title::newFromText("Genes not mapped to a genome sequence");
			$article = new Article($title);
			$article->doRedirect();

		}else{
			# look for gene from nonexistent gene, product, expression page. Should find if there was a link from a synonym, for example
			$tmp = explode(':',$t);
			if (isset($tmp[1]) && in_array($tmp[1], array('Gene','Gene_Product(s)','Expression','Evolution')) ) {
				$title = Title::newFromText($tmp[0]);
				if (is_object($title) && $title->exists()) {
					$article = new Article($title);
					$article->doRedirect();
					return false;
				}
			}

		}
	}
	# look for three letter acroynms
	if (preg_match('/^[a-z]{3}$/i',$t)){
		$t2 = strtolower($t)."_genes";
		$title = Title::newFromText($t2,NS_CATEGORY);
		if (is_object($title) && $title->exists()){
			#echo "<pre>"; print_r($title->getArticleID()); exit;
			$page_uid = $title->getArticleID();
			$title = Title::newFromID($page_uid);
			if (is_object($title)) {
				$article = new Article($title);
				$article->doRedirect();
				return false;
			}
		}else{
			$title = Title::newFromText($t,NS_MAIN);
		}
	}

	# All done (returning false to kill PoD's wfRunHooks stack)
	return true;
}
?>
