<?php
/*
TableEdit is a system to create and manage simple tables in mediawiki.  See README for more information and requirements.

The MIT License
Copyright (c) <2006-2013> <Jim Hu>
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/TableEdit/TableEdit.php" );
EOT;
        exit( 1 );
}
// credits for this extension
$wgExtensionCredits['specialpage'][] = array(
	'name' 			=> 'TableEdit',
	'author' 		=> array('Jim Hu', '[mailto:bluecurio@gmail.com Daniel Renfro]'),
	'version'		=> '1.0.13',
	'description' 	=> 'adds a forms-based table editor as a special page',
	'url' 			=> 'http://gmod.org/TableEdit'
);

// autoload classes 
$wgAutoloadClasses['wikiBox'] 				= dirname(__FILE__).'/class.wikiBox.php';
$wgAutoloadClasses['wikiBoxRow']			= dirname(__FILE__).'/class.wikiBox.php';
$wgAutoloadClasses['TableEdit'] 			= dirname(__FILE__).'/SpecialTableEdit.body.php';
$wgAutoloadClasses['TableEditView'] 		= dirname(__FILE__).'/class.TableEditView.php';
$wgAutoloadClasses['TableEdit_Loader'] 		= dirname(__FILE__).'/class.loader.php';
$wgAutoloadClasses['TableEditLinker'] 		= dirname(__FILE__).'/modules/TableEditLinks.php';
$wgAutoloadClasses['TableEditCategoryTags'] 		= dirname(__FILE__).'/modules/TableEditCategoryTags.php';

// include the messages
$wgExtensionMessagesFiles['TableEdit']       = dirname(__FILE__) . '/SpecialTableEdit.i18n.php';

// include the special page alias
$wgExtensionAliasesFiles['TableEdit']         = dirname(__FILE__) . '/SpecialTableEdit.alias.php';

// include the CategorySummary data mining system
require_once(dirname(__FILE__).'/modules/TableEditCategorySummary.php');

// register the special page
$wgSpecialPages['TableEdit'] 				= 'TableEdit';

// Register hooks
$wgHooks['ArticleSave'][] 			= 'wfNewEditTable' ;
$wgHooks['ArticleDeleteComplete'][] 		= 'wfDeleteTables';
$wgHooks['TitleMoveComplete'][]     = 'wfTableEdit_MovePage';
$wgHooks['BeforePageDisplay'][]  = 'wfTableEdit_AddHeadThings';
$wgHooks['ResourceLoaderRegisterModules'][] = 'efTableEdit_RegisterModules';

function wfTableEdit_MovePage( &$title, &$newtitle, &$user, $oldid, $newid ) {

	// Does ANYONE know how to do this *correctly*?? DPR

	$dbw =& wfGetDB( DB_MASTER );
	$sql = 'UPDATE ext_TableEdit_box
	        SET page_name = (
	        	SELECT DISTINCT(page_title)
	        	FROM page
	        	WHERE page_id = page_uid
	        )
	        WHERE page_name = "' . $title->getDBkey() . '"';
	$result = $dbw->query( $sql );

	// keep processing
	return true;
}

function wfNewEditTable( &$article, &$user, &$page_text, &$summary, $minor, $watch, $sectionanchor, &$flags){
	global $wgMessageCache, $wgTableEditMessages, $wgScript, $wgServerName, $wgHooks;

	# abort if this is a template page or if the page has not been saved yet
	$title = $article->getTitle();
	if ($title->getNamespace() == 10 || $title->getNamespace() == 8 || !$title->exists()) return true;

#	foreach( $wgTableEditMessages as $key => $value ) {
#		$wgMessageCache->addMessages( $wgTableEditMessages[$key], $key );
#	}

	# parsing functionality modified from Parser.php
	# end up with a string, $stripped, where each instance is replaced by -newTableEdit-00000001-QINU
	# where the 8 digit numbers increment, and $matches, an array of useful info, including the
	# strings to replace in $stripped and the parameters passed by the enclosed tags, if any.
	static $n = 1;
	$stripped = '';
	$matches = array();

	$taglist = "newTableEdit|newVTableEdit";
	$start = "/<($taglist)(\\s+[^>]*?|\\s*?)(\/?>)/i";
	$text = $page_text;
	while ( '' != $text ) {
		$p = preg_split( $start, $text, 2, PREG_SPLIT_DELIM_CAPTURE );
		$stripped .= $p[0];
		if( count( $p ) < 5 ) {
			break;
		}
		if( count( $p ) > 5 ) {
			// comment
			$element    = $p[4];
			$attributes = '';
			$close      = '';
			$inside     = $p[5];
		} else {
			// tag
			$element    = $p[1];
			$attributes = $p[2];
			$close      = $p[3];
			$inside     = $p[4];
		}

		$uniq_prefix = dechex(mt_rand(0, 0x7fffffff)) . dechex(mt_rand(0, 0x7fffffff));
		$marker = "$uniq_prefix-$element-" . sprintf('%08X', $n++) . '-QINU';
		$stripped .= $marker;

		if ( $close === '/>' ) {
			// Empty element tag, <tag />
			$content = null;
			$text = $inside;
			$tail = null;
		} else {
			if( $element == '!--' ) {
				$end = '/(-->)/';
			} else {
				$end = "/(<\\/$element\\s*>)/i";
			}
			$q = preg_split( $end, $inside, 2, PREG_SPLIT_DELIM_CAPTURE );
			$content = $q[0];
			if( count( $q ) < 3 ) {
				# No end tag -- let it run out to the end of the text.
				$tail = '';
				$text = '';
			} else {
				$tail = $q[1];
				$text = $q[2];
			}
		}

		$matches[$marker] = array( $element,
			$content,
			Sanitizer::decodeTagAttributes( $attributes ),
			"<$element$attributes$close$content$tail" );
	}
	$pagename = $article->mTitle->getPrefixedDBkey();
	$page_uid = $article->getID();
	$type = "";

	# gather nowiki content
	preg_match_all('/<nowiki>.*<\/nowiki>/i', $stripped, $nowiki_matches);
	$nowiki = " ".implode('',$nowiki_matches[0]);

	foreach ($matches as $key=>$match){
		# key is the stuff to replace at the end
		# $match[0] is the element

		# put back calls from inside nowiki
		if (strpos($nowiki,$key) > 0){
			$replacement = "<".$match[0];
			if (isset($match[1])){
				$replacement .= ">".$match[1]."</".$match[0].">";
			}else $replacement .= "/>";
			$stripped = str_replace($key,$replacement,$stripped);
			continue;
		}

		switch ($match[0]){
			case 'newTableEdit':
				$type = 0;
				break;
			case 'newVTableEdit':
				$type = 1;
				break;

		}
		# $match[1] is the parameters.
		$data = trim($match[1]);
		if (strpos("_".$data,"Template:") == 1){
			#assume it's a template
			$template = str_replace("Template:", "", str_replace("\n","_",$data) );
			$template = str_replace(" ", "_", $template);
			$headings = '';
		}else{
			$template = "";
			$headings = $data;
		}

		$tableEdit = new TableEdit;
		$box = new wikiBox();
		$box->page_name = $pagename;
		$box->page_uid = $page_uid;
		$box->template = trim($template);
		$box->headings = $headings;
		$box->colnum = substr_count($headings,"\n")+1;
		$box->type = $type;
		$box->save_to_DB(); #print_r($box); exit;
		$box->set_from_DB();# needed to process templates
		$replacement = $tableEdit->make_wikibox($box);

		#protectsection different versions hook in different places
		if (
			(isset($wgHooks['EditFilter']) && is_array($wgHooks['EditFilter']) && in_array('wfCheckProtectSection',$wgHooks['EditFilter'])) ||
			(is_array($wgHooks['ParserAfterTidy']) && in_array('ProtectSectionClass::stripTags',$wgHooks['ParserAfterTidy']))
			){ 
				$replacement = "<protect>".$replacement."</protect>";
			}	


		$stripped = str_replace($key,$replacement,$stripped);
	}

	$page_text = $stripped;
	return true;
}
/*
Hook to ArticleDeleteComplete:
&$article, User &$user, $reason, $id
*/
function wfDeleteTables(&$article, &$user, $reason, $id){
	preg_match_all("/(?:<protect>)?<\!--box uid=(\w+\.\d+\.\w+)-->(?:<\/protect>)?/", $article->getText(), $uids);
	foreach(array_unique($uids[1]) as $uid){
		$box = new wikiBox($uid);
		$box->set_from_DB();
		$box->delete_box_from_db();
	}
	return true;
}

function efTableEdit_RegisterModules( $resourceLoader ) {
	global $wgResourceModules;
	$wgResourceModules['ext.TableEdit'] = array(
		'scripts' => array('js/jquery.dataTables.js', 'js/init_datatables.js'),
		'styles' => array('css/main.css'),
		'localBasePath' => __DIR__,
		'remoteExtPath' => 'TableEdit'
	);
	return true;
}


function wfTableEdit_AddHeadThings( OutputPage &$out, Skin &$skin){
	$out->addModules( 'ext.TableEdit' );
	return true;

}