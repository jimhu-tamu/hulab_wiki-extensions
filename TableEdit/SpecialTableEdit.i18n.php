<?php

$messages = array();

$messages['en'] = array(

    'tableedit'         => 'TableEdit',

	'add'           	=> 'Add',
	'addData'       	=> 'Add $1',
	'addMultiple'      	=> 'Add multiple',
	'addHeading'    	=> 'Add heading',
	'addXLS'			=> 'Add Excel spreadsheet',
	'boxNotFound'      	=> "Can't find the table to edit",
	'boxStyle'         	=> 'Table style',
	'boxStyleExample'  	=> "(e.g align='right')",
	'back'          	=> 'Back to TableEdit',

	'canOverrideStyle' 	=> "However, you can change the style of the heading display for this table without affecting others.",
	'cantDeleteTable' 	=> 'Sorry, you don\'t have sufficient rights to delete this table',
	'cantEditHeadings' 	=> "The headings for this box are controlled by the template page $1.  "
							."Editing the template may affect tables on multiple pages.",
	'changesNotSavedUntil' 	=> "Changes are not saved permanently until you save the table back to the wiki page",
	'column'        	=> 'column',
	'confirmDelete' 	=> 'Are you sure you want to delete the table?',
	'confirmRevert' 	=> 'Are you sure you want to revert to the version of the table in the database?  Changes made in this session will be lost.',
	'conflict'      	=> 'A conflict has been detected between the version of this table in the wiki and the copy you are editing.',
	'conflictExplain'  	=> 'The rows shown above are different from the working copy.'.
							'  This includes rows that have been edited and rows that are missing in the copy you are editing.',
	'conflictExplain2' 	=> 'The highlighted rows are different from the copy of the table from the wiki.  This includes rows that have been edited and rows that you added.'  
							.'  You can delete rows from the working copy in this view before continuing.',
	'conflictHelp'  	=> 'Copy desired rows to the working version until you have what you need, then continue editing.',
	'copiedOrTranscluded' => "This table comes from a different page; cancelling or saving changes will send you to the original source page. Use the back button on your browser to return to the page you were viewing",
	'copy'          	=> 'Copy',
	'deleteLastHeading'	=> 'Delete last heading',
	'deleteTable'    	=> 'Delete Table',
	'dumpBox'       	=> 'View box data',
	'editHeadings'  	=> 'Edit Headings',
	'editRow'			=> 'Edit Row',
	'editBox'       	=> 'Edit $1 copy',
	'editBox2'      	=> 'Edit $1 copy',
	'editRowStyle'  	=> "Edit row style:",
	'explainOwnerRules'	=> "Public rows can be edited or deleted by any user who can edit<br>"
							."Private rows can be edited or deleted by their creator, or by admins",
	'fixDups'        	=> "Fix duplicate tables",
	'force_revert'   	=> "Revert",
	'headingStyle'  	=> "Heading style",
	'headingStyleExample'=> " (e.g. 'bgcolor = #ccccff' to make the heading background light blue)",
	'helpTableEdit'    	=> "TableEdit Help",
	'helpIsContextual' 	=> "TableEdit Help pages are different depending on what you are currently doing.",
	'insufficientRights'=> "<p style='color:red'><b>You don't have sufficient rights on this wiki to edit tables.  "
							."Perhaps you need to log in. Changes you make in the Table editor will not be saved back to the wiki</b></p>"
							."<p>See <a href = '{{SCRIPTPATH}}/index.php/Help:Help'>Help</a> for Help on this wiki.  See <a href = '{{SCRIPTPATH}}/index.php/Special:TableEdit'>the documentation</a> for how to use the table editor",
	'is_not_number'   	=> 'is not a number',
	'load'          	=> 'Load file',
	'newHeading'    	=> 'New Heading',
	'newTableHere ' 	=> 'Create Table Here',
	'notFound'       	=> 'not found',
	'notText'       	=> 'is not a text file',
	'pageIDNotFound'	=> 'Page corresponding to the requested id not found',
	'page_id_mismatch'	=> 'This table came from another page.',
	'pageNotFound'  	=> 'Something is wrong, can\'t find the page',
	'pleaseDontEditHere'=> "\n<!--\n******************************************************************************************\n* \n*"
							."   ** PLEASE DON'T EDIT THIS TABLE DIRECTLY.  Use the edit table link under the table. ** \n* \n****************************************************************************************** -->",
	'reconcile_foreign_table' => "You have changed data that is coming from another table. Would you like to <b>Revert</b> to using the 
								  data coming from $1, or <b>Update</b> both tables with the new information?",
	'reload'	    	=> 'Reload',
	'reloadFF'	    	=> 'Firefox users: if editing is misbehaving please click ',
	'revert'	    	=> 'Revert',
	'revertMeta'    	=> 'Revert Metadata to Saved',
	'revertToSaved' 	=> 'Revert Table to Saved',
	'rotate'           	=> 'Rotate Table',
	'row'            	=> 'row',
	'rowDeleted'    	=> 'Row deleted',
	'rowStyleExample'	=> " (e.g. 'bgcolor = #ccccff' to make the row background light blue)",
	'save'          	=> 'Save',
    'save-row'          => 'Save Row',
	'saveAsXLS'			=> 'Save as Excel spreadsheet',
	'savedVersion'     	=> 'Saved version',
	'saveMsg'       	=> '$1 by $2 via TableEdit',
	'saveStyles'     	=> 'Save styles',
	'saveToPage'    	=> "Save Table to wiki page: $1",
	'savedVersion'    	=> 'Rows in conflict with the working copy:',
	'setfromDBfailed'	=> 'Unable to set the table from the database',
	'style'         	=> 'Style ',
	'tableDeleted'  	=> 'Table deleted ',
	'tableEdited'   	=> 'Table edited ',
	'tableDupFound' 	=> "The unique identifier for this table appears more than once on this page."
							."  Someone probably copied the table and pasted it in a different part of the page.  Table Edit can't resolve which one to edit.  Click the button below to try to fix the problem, or contact an administrator for help fixing this page.",
	'tableEditEditLink'	=> 'edit table',
	'template'         	=> 'Template',
	'titleProblem'  	=> "Sorry! There was a problem identifying the correct page for this table.  "
							."Try going back and recreating it after saving the page.  See <a href = '{{SCRIPTPATH}}/index.php/Special:TableEdit'>the documentation</a> for how to use the table editor",
	'undeleteRows'     	=> "Undelete rows",
	'unclosedTag'		=> "Error: Unclosed $1 tag",
	'update'        	=> "Refresh",
	'uploadRows'       	=> "Add multiple rows/columns from an uploaded file",
	'viewMeta'      	=> "View/Edit metadata",
	'workingVers_wiki' 	=> "Working copy:",
	'workingVers_db' 	=> "Working copy:",
	'wrongOwner'    	=> "Sorry, you don't own that data.",
	
	'addXLS'			=> 'Add Excel spreadsheet',
	'saveAsXLS'			=> 'Save as Excel spreadsheet',
	'wrongType'			=> "The file specified is not of the specified type."

);

