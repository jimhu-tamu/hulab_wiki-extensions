<?php
/*
Exception handling for TableEdit 
*/
#echo $_SESSION['TableEditView']."<br>";
global $wgServer, $wgScriptPath,$wgRequest;
# use the message cache to support language translations of help pages.
$exMsg = $e->getMessage() ;
$buttons = '';
if (!isset($te->page_name)){ 
	$te->page_name = $wgRequest->getText('pagename');
}	
switch($exMsg){
	case 'pageNotFound':
		/* This occurs if the passed page_name cannot be used to create a title object
		because page_name='' will call just the documentation, this will happen only
		if the page_name string has illegal characters in it... which should not happen
		very often.  Possible source - copying a table from one wiki to another where 
		wgLegalTitleCharacters has a different character set*/
		break;
	case 'boxNotFound':
		/*no id passed on the first load...
		someone edited the link? */
		break;
	case 'setfromDBfailed':
		/**/
		break;
	case 'tableDupFound':
		/*box was copied within a page, or pasted more than once in a new page*/
		$buttons = TableEditView::form_button_only(wfMsg('fixDups'),array('view'=>'fix_duplicate_tables'));
		break;
	case 'page_id_mismatch':
		/**/
		break;
	case 'page_name_mismatch':
		/**/
		break;
	case 'x':
		/**/
		break;
	default:
		$output .= wfMsg($exMsg);
#		$this->print_obj($handler);
		
}
$output = wfMsg($exMsg);
$output .= $buttons;
$output .= "<br /><a href='$wgServer$wgScriptPath/index.php?title=$te->page_name'>".wfMsg('cancel')."</a>";
