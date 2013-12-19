<?php
/*
 * StrainPageOnDemand.php - An extension 'module' for the PagesOnDemand extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.1
 * @copyright Copyright (C) 2011 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'StrainPageOnDemand',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Create Category pages for EcoliWiki Strains with PagesOnDemand mechanism',
    'version'=>'0.1'
);


# Register hooks ('PagesOnDemand' hook is provided by the PagesOnDemand extension).
$wgHooks['PagesOnDemand'][] = 'efLoadStrainPageOnDemand';

/**
* Loads a demo page if the title matches a particular pattern.
* @param Title title The Title to check or create.
*/
function efLoadStrainPageOnDemand( $title, $article ){
	global $wgSitename,$wgUser;
	# Short-circuit if $title isn't in the CATEGORY namespace or doesn't match the DEMO pattern.
	if ( $title->getNamespace() != NS_CATEGORY || !preg_match('/^Strain:/', $title->getDBkey() ) ) {
		return true;
	}
	if (!$wgUser->isLoggedIn()) return true;
	# version for EcoliWIki
	if (strpos(' '.$wgSitename,'EcoliWiki') ||
		strpos(' '.$wgSitename,'SubtilisWiki') 
		){

			# Create the Article, supplying the new text
			# get the text from the desired template
			$template = Revision::newFromTitle(Title::makeTitle(NS_TEMPLATE, 'Strain'));
			if (! $template){
				$text = "Template not found\n\n$refstring\n\n$abstract\n\n$linkstring";
			}else{
				$text = $template->getText();
				#strip out noinclude sections
				$text = preg_replace( '/<noinclude>.*?<\/noinclude>/s', '', $text );		
			}
	
			$article = new Article($title);
			# create the article to get a page_id
			$article->doEdit( '', 'New Page', EDIT_NEW | EDIT_FORCE_BOT );
			# resave the article to get TableEdit to add
			$article->doEdit( $text, 'Resave Page', EDIT_UPDATE | EDIT_FORCE_BOT | EDIT_SUPPRESS_RC );
		}
		
	# All done (returning false to kill PoD's wfRunHooks stack)

	if ( !$article ) {
            return false;
	}

	$article->doRedirect();
	return false;
}
?>
