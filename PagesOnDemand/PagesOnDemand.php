<?php
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
# http://www.gnu.org/copyleft/gpl.html

/**
 * This is an extension that provides a mechanism for generating article content by title.
 * Installing this extension by itself won't affect the wiki in and of itself, instead
 * offloading the real work to modules capable of performing the article insertion.
 *
 * @addtogroup Extensions
 *
 * @author Jim Hu (jimhu@tamu.edu)
 * @author Jim Wilson (wilson.jim.r@gmail.com)
 * @copyright Copyright 2007, Jim Hu & Jim Wilson 
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'PagesOnDemand',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt; and Jim Wilson &lt;wilson.jim.r@gmail.com&gt;',
    'description'=>'Provides mechanism for generating wiki articles on demand.',
    'version'=>'0.1'
);

# Register hooks
$wgHooks['ArticleFromTitle'][] = 'wfPagesOnDemand' ;

$wgExtensionMessagesFiles['PagesOnDemand'] = dirname( __FILE__ ) . '/PagesOnDemand.i18n.php';

/**
* Gives extension modules a chance to create pages by exposing the PagesOnDemand hook.
* @param Title $title The Title of this request.
* @param Article $article The Article of this request (should usually be null).
* @return true (always)
*/
function wfPagesOnDemand( $title, $article ) {

        # Short-circuit if the article already exists
        if ( $title->exists()  || preg_match('/\//', $title->getDBkey() ) ) {
                return true;
        }

	# Run PageOnDemand hooks provided by extension modules.
	$result = wfRunHooks( 'PagesOnDemand', array( &$title, &$article ) );
	
	# If the article was created, ensure that 'action=edit' will forward to article view.
	if ( !$result && is_object($article) && $article->exists() ) {
		global $wgHooks;
		$wgHooks['AlternateEdit'][] = 'wfSkipToArticleView';
	}

	# Give other extensions a chance to run.
	return true;
}

/**
* Forwards to Article->view() - meant to be attached to 'AlternateEdit' dynamically.
* @param EditPage $editPage An instance of EditPage whose mArticle will be viewed.
*/
function wfSkipToArticleView ( $editPage ) {
	$editPage->mArticle->view();
	return false;
}
?>
