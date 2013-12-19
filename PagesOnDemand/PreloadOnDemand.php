<?php
/*
 * PreloadOnDemand.php - An extension 'module' for the PagesOnDemand extension.
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.1
 * @copyright Copyright (C) 2008 Jim R. Wilson
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'PreloadOnDemand',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Apply pages on demand to inputbox pages.',
    'version'=>'0.1'
);


# Register hooks ('PagesOnDemand' hook is provided by the PagesOnDemand extension).
$wgHooks['PagesOnDemand'][] = 'efLoadPreloadOnDemand';

/**
* Gets information from Inputbox and sets up so that user should redirect to completed page, not edit page.
* @param Title title The Title to check or create.
*/
function efLoadPreloadOnDemand( $title, $article ){
	global $wgRequest, $wgUser;
	# Short-circuit if $title isn't in the MAIN namespace or doesn't match the DEMO pattern.
	$preload = $wgRequest->getVal('preload');
	if ( !$wgUser->isAllowed('edit') || $title->getNamespace() != NS_MAIN || !isset($preload) ) {
		return true;
	}

    $template = Revision::newFromTitle(Title::makeTitle(NS_TEMPLATE, str_replace('Template:','',$preload)) );
    if (! $template){
		return true;
	}else{
	    $text = $template->getText();
	}
	# remove <noinclude> elements
	$text = preg_replace( '/<noinclude>.*?<\/noinclude>/s', '', $text );
	# honor (but remove) <includeonly> tags
	$text = preg_replace( '/<\/?includeonly>/s', '', $text );

	# Create the Article, supplying the new text
	$article = new Article($title);
	$article->doEdit( $text, "New $preload Page", EDIT_NEW );

	# All done (returning false to kill PoD's wfRunHooks stack)
	return false;
}
?>
