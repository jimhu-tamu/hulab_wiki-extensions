<?php
/*
 * GOpageOnDemand.php - An extension 'module' for the PagesOnDemand extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.2
 * @copyright Copyright (C) 2008 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php
 
 v 0.2 change regex to match for GONUTS
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'Category:GO_OnDemand',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Create Category pages with PagesOnDemand mechanism',
    'version'=>'0.2'
);


# Register hooks ('PagesOnDemand' hook is provided by the PagesOnDemand extension).
$wgHooks['PagesOnDemand'][] = 'efLoadGOtermPageOnDemand';

/**
* Loads a demo page if the title matches a particular pattern.
* @param Title title The Title to check or create.
*/
function efLoadGOtermPageOnDemand( $title, $article ){
	global $wgSitename;
	# Short-circuit if $title isn't in the MAIN namespace or doesn't match the DEMO pattern.
	if ( $title->getNamespace() != NS_CATEGORY || !preg_match('/^GO:\d+/', $title->getDBkey() ) ) {
		return true;
	}
	
	# version for EcoliWIki
	if (strpos(' '.$wgSitename,'EcoliWiki') ||
		strpos(' '.$wgSitename,'SubtilisWiki') 
		){
		# Create the Article's new text - could be more complicated, but this is just a demo
		$text = "{{GO term stub|". $title->getDBkey().'}}';
		# add references section after the stub template
		$text .= "\n\n==References==\n{{RefHelp}}\n<references/>";
		# test whether there are any members for this category.  If not, abort
		
		$cat = Category::newFromTitle($title);
		$members = $cat->getMembers();
		if($members->count() == 0) return true;
		
		# Create the Article, supplying the new text
		$article = new Article($title);
		$article->doEdit( $text, 'New GO term category page', EDIT_NEW );
	}elseif(strpos(' '.$wgSitename,'GONUTS')){
		# this should not create pages, but is used to redirect to the correct page when
		# the term name has changed.
		$dbr =& wfGetDB( DB_SLAVE );
		$term_full = $title->getDBkey();
		preg_match('/(GO:\d+)/',$term_full,$matches);
	#	print_r($matches);echo "$term_full here"; exit;
		$result = $dbr->select(
			array('page'),                   	# tables
			array('page_id','page_title'),       # fields
			array("page_title LIKE '".strtoupper($matches[0]) ."%'","page_namespace = 14"),	# where
			__METHOD__
		);
		if ($dbr->numRows($result) == 1){
			$x = $dbr->fetchObject ( $result );
			$page_uid = $x->page_id;
		#	print_r($x);
			$title = Title::newFromID($page_uid);
			$article = new Article($title);
			if(is_object($article)) $article->doRedirect();
			return false;
		}

	}
	# All done (returning false to kill PoD's wfRunHooks stack)

	if ( !$article ) {
            return false;
	}

	$article->doRedirect();
	return false;
}