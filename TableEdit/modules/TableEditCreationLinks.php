<?php
/*
 * TableEditCreationLinks.php - An extension 'module' for the TableEdit extension.
 * These examples are used by EcoliWiki
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.1
 * @copyright Copyright (C) 2007 Jim Hu
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'TableEditCreationLinks',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Modify link text for new table creation.',
    'version'=>'0.1'
);


# Register hooks ('PagesOnDemand' hook is provided by the PagesOnDemand extension).
$wgHooks['TableEditCreateTableLink'][] = 'wfTableEditCreationLinks';

/**
* Loads a demo page if the title matches a particular pattern.
* @param Title title The Title to check or create.
*/
function wfTableEditCreationLinks( $template, $message ){
	switch ($template){
		case 'Test':
			$message = 'This is a test';
			break;
		case 'GO_table_reference':
		case 'GO table reference':
			$message = 'Add GO annotations';
			break;
	
	}
	return true;
}
?>
