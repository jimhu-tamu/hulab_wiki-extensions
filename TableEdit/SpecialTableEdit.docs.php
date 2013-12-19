<?php
/*
Context sensitive help system for TableEdit 
*/
#echo $_SESSION['TableEditView']."<br>";
$figspath = "http://trimer.tamu.edu/jh/images/TableEdit_docfigs"; 

switch($previous_view){
	case 'nav':
		$help_page = 'Help:Editing Tables';
		$inc_file = (dirname(__FILE__) . '/docs/nav.php');
		break;
	case 'edit_headings':
		$help_page = 'Help:Editing Table Headings';
		$inc_file = (dirname(__FILE__) . '/docs/edit_headings.php');
		break;
	case 'add_multiple':
		$help_page = 'Help:Adding Multiple Table Rows';
		$inc_file = (dirname(__FILE__) . '/docs/load_rows.php');
		break;
	case 'edit_row':
		$help_page = 'Help:Editing Table Rows';
		$inc_file = (dirname(__FILE__) . '/docs/edit_row.php');
		break;
	case 'conflict':
		$help_page = 'Help:Conflict Resolution for Tables';
		$inc_file = (dirname(__FILE__) . '/docs/conflict.php');
		break;
	case 'box_dump':
	case 'metadata':
		$help_page = 'Help:Special TableEdit Views for Administrators';
		$inc_file = (dirname(__FILE__) . '/docs/admins.php');
		break;
	case 'msg':
		$help_page = 'Help:TableEdit Error Messages';
		$inc_file = (dirname(__FILE__) . '/docs/messages.php');
		break;
	default:
		$help_page = 'Help:TableEdit';
		$inc_file = (dirname(__FILE__) . '/docs/default.php');

}
$output = "<p>".wfMsg('helpIsContextual')."To customize this Help document, edit the page <a href='index.php?title=$help_page'>$help_page</a></p>\n";

$h = self::get_page_html(str_replace(' ','_',$help_page) );
if ($h != ''){ 
	$output .= $h;
}else{
	include ($inc_file);
	# use the message cache to support language translations of help pages.
	global $wgMessageCache;
	#foreach( $TableEditDocs as $key => $value ) 	$wgMessageCache->addMessages( $TableEditDocs[$key], $key );
	$output .= wfMsg('tableEditDocs');
}
