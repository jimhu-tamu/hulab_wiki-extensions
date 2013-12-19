<?php
/*
Generate views for Special:TableEdit

Version 1.1: modify to make TableEditView a collection of static functions. Object should never be instantiated.


*/


class TableEditView {


/*
Legacy commented code from daniel.

// working on this....for XML uploads.
	function add_multiple_view(){
		global $wgUser,$wgServer,$wgScriptPath;
$string = <<<END
<h3>Bulk loading</h3>
You can add data to a table in bulk by either entering it below, or using an uploaded file.
<br />
<br />
<ol>
  <li>
  	<h4>Enter Text</h4>
	Enter data in the Text area. Each row should be an a new line and fields should
	be delimited by "||" (double pipes) or tabs.
END;
		$string .= $this->form(
			Xml::openElement( 'textarea', array( 'name' => "bulk_add_form", 'cols' => 80, 'rows' => 10 )).
			''.
			Xml::closeElement( 'textarea' ).
			self::form_button(wfMsg('save'))
		);
		$string .= <<<END
  </li>
  <li>
  	<h4>Upload a File</h4>
  	<ul>
  	  <li>
		The file must first be uploaded to the wiki. You can
	    <a href="$wgServer$wgScriptPath/index.php/Special:Upload">upload files here</a> <br />
	  </li>
	  <li>
		Once a file is uploaded, you can enter it's name into the textbox below, choose it's type,
		and import into a table. Be sure to include the <tt>Image:</tt> namespace.
	  </li>
	 <ul>
END;
		$string .= $this->form(
			'<br /><b>Type: </b>' .
			Xml::openElement('select', array('name'=>'upload_type')) .
				Xml::element('option', array('value'=>'txt'), 'Plain Text') .
				Xml::element('option', array('value'=>'xls'), 'Excel Spreadsheet') .
			Xml::closeElement('select') . '<br />' .
			'<b>Filename: </b>' .
			XML::input('bulk_add_file', 40, 'Image:', array('maxlength'=>255) ) .
			'<br />' .
			self::form_button(wfMsg('load'))
		);
		$string .= "</li></ol><br /><br /><a href='$this->url'>".wfMsg('cancel')."</a>\n\n";

		return $string;
	}
*/

	static function add_multiple_view(TableEdit $te, WikiBox $box ){
		global $wgUser,$wgServer,$wgScriptPath;

		if (!$box || !is_object($box)) trigger_error("An error has occured.\n", E_USER_ERROR);

		$string = 	"<h4>Instructions</h4>
					Enter data in the Text area. Each row should be an a new line and fields should
					be delimited by \"||\" (double pipes) or tabs.";

		$sample_line = "";
		if (trim($box->headings) != "" ) {
			$sample_line = str_replace("\n", '||', $box->headings);
			$string .= 	"For example, a sample line of input looks like:<br />
						<pre>$sample_line</pre><br />";
		}

		$string .= "<h4>Enter Text</h4>";
		$string .= self::form(
			$te, 
			Xml::openElement( 'textarea', array( 'name' => "bulk_add_form", 'cols' => 80, 'rows' => 10 )).
			''.
			Xml::closeElement( 'textarea' ).
			self::form_button(wfMsg('save'))
		);
		$string .= "<br /><br /><a href='$te->url'>".wfMsg('cancel')."</a>\n\n";
		return $string;
	}


	static function box_dump_view(TableEdit $te, WikiBox $box){
		global $wgUser;
		if (!$wgUser->isAllowed('delete')) return;
		return "<pre>".print_r($box,true)."</pre>".$te->backlink."</a>";
	}

	/*
	Conflict display.  Pass two boxes,
		$box is the working copy
		$box2 is the comparison copy, from the database or from the wiki text
		$src is where $box2 comes from.

	*/
	static function conflict_view(TableEdit $te, WikiBox $box, WikiBox $box2){
		global $wgUser,$wgServer,$wgScriptPath;
		# generate row editing buttons for the two boxes.
		$edit_form2 = array();
		foreach ($box->rows as $row_index=>$row)
			if($row->is_current) $edit_form[$row_index] = self::edit_row_form($te, $row_index, $row, 'Delete');
		foreach ($box2->rows as $row_index=>$row)
			if($row->is_current) $edit_form2[$row_index] = self::edit_row_form($te, $row_index, $row, 'Copy');
		$string = '';

		# top message telling the user that a conflict has been detected
		if ($te->conflict != '') $string .= "<span style='color:red'>".wfMsg('conflict',$te->conflict)."</span><br/>";

		# box for table from wiki or db
		if ($box->template != '') $box2->template = $box->template; # restore template control for things like menus and calcs
		$string .= '<h3>'.wfMsg('savedVersion').'</h3>';
		$string .= self::box2html($te, $box2,'',$edit_form2,false,2)."";
		$string .= '<p>'.wfMsg('conflictExplain')."</p>";
		$string .= '<p>'.wfMsg('conflictHelp')."</p><br/>";
	#	$string .= self::form_button_only($ te, wfMsg('editBox2',te->conflict), array('view' => 'nav'))."<br />";

		# box for working (session) version (either loaded from the db or right before save)
		$string	.= '<h3>'.wfMsg('workingVers_'.$te->conflict).'</h3>'.wfMsg('conflictExplain2').self::box2html($te, $box,'',$edit_form)."";
		# button at bottom
		$buttons = "<table class=\"tableEdit_conflict\"><tr><td>".self::form_button_only($te, wfMsg('editBox','working'), array('view' => 'nav' ))."<td>\n";
		switch($te->conflict){
			case 'wiki':
				break;
			case 'db';
				$buttons .= "<td>".self::form_button_only($te, wfMsg('saveToPage', urldecode($te->page_name) ), array('view' => 'force_save'))."</td>";
				break;
		}
		$string .= $buttons."<td><a href='$wgServer$wgScriptPath/index.php?title=$box->page_name'>".wfMsg('cancel')."</a></td></tr></table>\n\n";
		return $string;
	}

	static function doc_view($previous_view = ''){
		include (dirname(__FILE__) ."/SpecialTableEdit.docs.php");
		return $output;
	}

	# view for editing of table headings for tables not controlled by templates
	static function edit_head_view(TableEdit $te, WikiBox $box){
		$headings = explode("\n",trim($box->headings,"\n"));
		$table_headings = '';
		$head_row = $move_row = '';
		foreach ($headings as $i => $heading){
			$table_headings .= "<th $box->heading_style>Heading ".($i+1)."</th>";
			if ($box->template == ''){
				$move_row .= "<td>".self::move_col_button($te, $i,$box, 0)."</td>";
				$head_row .= "<td>".self::form_field($i, $heading)."</td>";
			}else{
				$head_row .= "<td>".Html::hidden('field[]', $heading)."$heading</td>";
			}
		}
		$i++;
		# build the lower part of the table, which is the form without the row of < and > buttons
		$table = "<tr $box->heading_style>$table_headings</tr><tr>$head_row</tr>";
		#if ($this->style == '') $style = addslashes($box->heading_style);
		$table .= "<tr><td colspan = '$i'>".wfMsg('style').
			 XML::input('style', 40, $box->heading_style, array('maxlength'=>255) )
			.self::form_button(wfMsg('update')).'<br/>'
			.wfMsg('headingStyleExample')."</td></tr>";
		$table .= "<tr><td colspan = '$i'>
		    <table class=\"tableEdit_edit_head\">
		      <tr>
		        <td>" .
		          self::form_button(wfMsg('save')) . "<a href='".$te->url."&view=nav&act=restoreheadings'>".wfMsg('cancel')."</a>" .
		      "</td>";

		if ($box->template == '') {
			$table .=
			"<td>".self::form_button(wfMsg('addHeading'))       ."</td>"
			."<td>".self::form_button(wfMsg('deleteLastHeading'))."</td>";
		} else {
			$table .=wfMsg('cantEditHeadings', $box->template).wfMsg('canOverrideStyle');
		}
		# reassemble the table
		$table = self::form($te, $table);
		if ($move_row != '') $table = "<tr>$move_row</tr>".$table;
		$table = "<table class=\"tableEdit_edit_head2 tableEdit\" ". self::prettytable().">".$table;
		$table .= "</tr></table></td></tr></table>\n";
		return $table;
	}

	static function edit_row_view(TableEdit $te, WikiBox $box, wikiBoxRow $row, $skip_style = false ) {
		global $wgUser;
		$uid = $wgUser->getID();
		$owner_uid = $row->owner_uid;
		$row_data = explode('||',$row->row_data);
		$table = '';
		$table .=  Html::hidden('row_index', $row->row_index);
		$table .= "<table class=\"" . $box->template . " tableEdit_edit_row tableEdit\" " . self::prettytable() . ">";
		$headings = explode("\n",$box->headings);
		// this is the meat of the method.
		foreach ($headings as $i => $heading){
			$table .= "<tr><th {{{ROW_TOOLTIP}}}>$heading</th>"."<td ".$row->row_style." id=\"" . $box->column_names[$i] . "\">";
			$table .= $te->apply_column_rules($row_data, $box, $row, $i, 'EDIT');
			# code to replace row tooltip
			$row_tooltip = "";
			if(isset($box->row_tooltips[$heading])) $row_tooltip = 	"title='".$box->row_tooltips[$heading]."'";
			$table = str_replace('{{{ROW_TOOLTIP}}}', $row_tooltip, $table);
			$table .= "<br />";
			if(isset($row->field[$i]->is_foreign) && $row->field[$i]->is_foreign){			// if its coming from somewhere else, say so.
				$rel_box = new wikiBox;
				$rel_box->setPageName($row->field[$i]->foreign_pagename);
				$rel_box->setTemplate($row->field[$i]->foreign_table_template);
				$rel_box->set_from_DB();
				$table .= '<hr style="width:88%; text-align:center; margin: 0 auto 0 auto;">';
			   	$table .= self::text2html(
			   		sprintf("<div class=\"tableEdit_note\"><b>Note:</b> Data mirrored from '''''%s''''' on '''''%s'''''</div>",
			   			$row->field[$i]->foreign_table_heading,
			   			TableEdit::create_edit_table_link($rel_box,  $row->field[$i]->foreign_pagename, $box->page_name)
			   		)
			   	);
			}

			$table .= "</td></tr>\n";
		}
		// end meat.
		$select_owner = '';#echo"owner: $owner_uid uid:$uid<br>";
		if ($owner_uid > 0  && $owner_uid == $uid){
			$select_owner = "<select name='row_owner'>
				<option label='Public' value='0'>Public</option>
				<option label='Private' value='$wgUser->mId' selected>Private</option></select>";
		}elseif($owner_uid == $uid){
			$select_owner = "<select name='row_owner'>
				<option label='Public' value='0' selected>Public</option>
				<option label='Private' value='$wgUser->mId' >Private</option></select>";
		}elseif($wgUser->isAllowed('delete') ) {
			$select_owner = "<select name='row_owner'>
				<option label='Keep' value='$owner_uid' selected>Keep original owner</option>
				<option label='Public' value='0' selected>Public</option>
				<option label='Private' value='$wgUser->mId' >Private</option></select>";
		}
		if ( !$skip_style ) {

			$table .= "<tr><td colspan = '2'>".$select_owner;

            /*
             * Yes, the dreaded "update" button. We've gone back and forth on this
             * one and I think finally decided that the text "update" is misleading
             * and should be "refresh." See SpecialTableEdit.i18n.php for the actual
             * text that this button shows.
             *
             * DPR 2011-06-06
             */
			$table .= self::form_button(wfMsg('update')).' ';


            /*
             * The save button on the edit-row form should be more explicit about
             * saving back to the table and not saving the table itself.
             */
			$table .= self::form_button(wfMsg('save-row')).' '
				." <a href='".$te->url."&view=nav&act=restorerow&row_index=".$row->row_index."'>".wfMsg('cancel')."</a>"
				."</td></tr></table><br/>\n";
			if ($select_owner !='') $table.= wfMsg('explainOwnerRules').'<br/><br/>';

			$table .= "<tr><td colspan = '$i'> ".wfMsg('editRowStyle').
				XML::input('row_style', 40, $row->row_style, array('maxlength'=>255) )."<br/>"
				.wfMsg('rowStyleExample')."</td></tr>";
			$table = implode (' ',$te->msg).self::form($te, $table);
		}
		else {
			$table .= '</td></tr></table>';
		}

		$table .= self::text2html($box->help4);
		return $table;
	}


	static function exception_view(TableEdit $te, $e){
		#hold exceptions in another file
		include (dirname(__FILE__) ."/SpecialTableEdit.Ex.php");
		return $output;

	}
	#
	static function metadata_view(TableEdit $te, WikiBox $box){
		global $wgUser;
		$table = '';
		if (!$wgUser->isAllowed('delete')) return;
		$table .= XML::element('h3',null,'Box Metadata');
		$table .= "\n<table class=\"tableEdit_metadata1\" ".self::prettytable().">";
		foreach ($box->box_metadata as $key => $metadata){
			if ($metadata->is_current === true){

			// print a better message about metadata that has a invalid timestamp
			$timestamp = ( $metadata->timestamp == 0 )
				? 'No available timestamp.'
				: date("Y-m-d h:i:s a", $metadata->timestamp) ;

			$table .= "<tr><td>"
				.self::form($te,
					Html::hidden('meta_type','box')
					.Html::hidden('meta_id',$key)
					.XML::input ('metadata',40,$metadata->metadata)
					. '<br />' . $timestamp . '<br />'
					.self::form_button(wfMsg('save'))
					.self::form_button(wfMsg('delete'))
				)."</td><tr>\n";
			}
		}
		# add button
		$table .= "</table>\n"
			.self::form(
				$te,
				''
				.Html::hidden('meta_type','box')
				.XML::input ('metadata',40)
				.self::form_button(wfMsg('add')
				)
			)."<br/><hr/>\n";
		# row metadata
		$table .= XML::element('h3',null,'Row Metadata');
		$table .= "<table class=\"tableEdit_metadata2\" ".self::prettytable().">\n";
		$table .= "<tr><th>".str_replace("\n","</th><th>",trim($box->headings))."</th><th>row metadata</th></tr>\n";
		foreach ($box->rows as $row){
			$table .= "<tr valign='top'><td>".str_replace('||','</td><td>', $row->row_data)."</td><td>";

			$table .= 'row_id: ' . $row->row_id . '<br /><hr />';


			// sort the metadata by timestamp...easier to read (dpr 2010-09-25)
			$metadata_by_timestamp = array();
			$metadata_without_timestamp = array();

			foreach ( $row->row_metadata as $rm ) {
				if ( $rm->timestamp != 0 ) {
					$metadata_by_timestamp[$rm->timestamp] = $rm;
				}
				else {
					array_push( $metadata_without_timestamp, $rm );
				}
			}
			ksort( $metadata_by_timestamp );

			// print out the metadata-with-timestamps...
			foreach ($metadata_by_timestamp as $timestamp => $metadata ){

				if (isset($metadata->is_current) && $metadata->is_current === true) {
					// build up the metadata to show in the last column
					$html = "";
					$html .= '<div style="font-size:smaller;">';
					$html .= 'row_metadata_id: ' . $metadata->row_metadata_id . '<br />';
					$html .= 'timestamp: '. date("Y-m-d h:i:s a", $timestamp);
					$html .= '</div>';
					$html .= XML::input('metadata',40,$metadata->metadata);
					$html .= Html::hidden('row_index',$row->row_index);
					$html .= Html::hidden('meta_id',$key);
					$html .= Html::hidden('meta_type','row');
					$html .= self::form_button(wfMsg('save'  ));
					$html .= self::form_button(wfMsg('delete'));

					// throw it onto the table inside a form
					$table .= self::form($te, $html) . "<br />";
				}
			}

			// print out the metadata-without-timestamps...
			foreach ($metadata_without_timestamp as $metadata ){

				if (isset($metadata->is_current) && $metadata->is_current === true) {
					// build up the metadata to show in the last column
					$html = "";
					$html .= '<div style="font-size:smaller;">';
					$html .= 'row_metadata_id: ' . $metadata->row_metadata_id . '<br />';
					$html .= 'timestamp: <i>Not available</i>';
					$html .= '</div>';
					$html .= XML::input('metadata',40,$metadata->metadata);
					$html .= Html::hidden('row_index',$row->row_index);
					$html .= Html::hidden('meta_id',$key);
					$html .= Html::hidden('meta_type','row');
					$html .= self::form_button(wfMsg('save'  ));
					$html .= self::form_button(wfMsg('delete'));

					// throw it onto the table inside a form
					$table .=self::form($te, $html) . "<br />";
				}
			}

			$table .= self::form(
				$te,
				 Html::hidden('view','metadata')
				.Html::hidden('meta_type','row')
				.Html::hidden('row_index',$row->row_index)
				.XML::input('metadata',40)
				.self::form_button(wfMsg('add'))
				)."</td></tr>";
		}
		$table .= "</table>";
		$table .= "<table class=\"tableEdit_metadata3\"><tr>"
			."<td>".self::form_button_only($te, wfMsg('revertMeta'))."</td>"
			."<td>".$te->backlink."</td>";
		$table .= "</tr></table>";
		return $table;
	}

	/*
	
	*/
	static function msg_view(TableEdit $te, $msg = '', $buttons = array()){
		#echo 'message view';
		if ($msg != '') $te->msg[] = $msg;
		$string = '';
		foreach ($te->msg as $msg) $string .= "<p>$msg</p>";
		$string .= "<table class=\"tableEdit_msg\"><tr>";
		foreach ($buttons as $view=>$label){
			$string .= "<td>".self::form_button_only($te, wfMsg($label), array('view' => $view));
		}
		$string .= "<td>"."<a href='".$te->url."&view=".$te->previous_view."&back=1'>".wfMsg('back')."</a>"."</td></tr></table>";
		return $string;
	}

	# this is the base view showing the table as it will appear on the page
	# but with edit buttons
	function nav_view(TableEdit $te, WikiBox $box ){
		global $wgUser,$wgServer,$wgScriptPath;
		if(!is_object($box)) return "";
		// show an edit headings button if there is no template
		$editheadings = '';
		if ($box->template == '')  {
			$editheadings = self::form_button_only($te, wfMsg('editHeadings') );
		}

		// loop through the rows and add the buttons to the left side
		$edit_form = array();
		foreach ($box->rows as $row_index=>$row){
			if (is_object($row) && $row->is_current) {
				$edit_form[$row_index] = self::edit_row_form($te, $row_index, $row);
			}
		}

		// build up the HTML to return
		$string = '';
		if ($box->help2 != '') $string = self::text2html($box->help2);

		// display the box
		$string .= self::box2html($te, $box, $editheadings, $edit_form, true).'<br clear="all"/>';


		// add bottom buttons
		$addtype = 'row';
		if ($box->type == 1) $addtype = 'column';
		$string .= '<table class=\"tableEdit_nav1\">';
		$string .="<tr><td>".self::form_button_only($te, wfMsg('addData',wfMsg($addtype)))."</td>";
		$string .="<td>".self::form_button_only($te, wfMsg('addMultiple'))."</td>";
		/*   save as *.xls   */
		//$string .= '<td>' . self::form_button_only($te, wfMsg('saveAsXLS')) . '</td>';
		$string .= '</tr></table><hr />';

		if ( (isset($box->template) && $box->template == "" ) || $wgUser->isAllowed('userrights')) {
			$string .= "<table class=\"tableEdit_nav2\"><tr>";
			if ($box->type <= 1 && $box->template == '') $string .= "<td>".self::form_button_only($te, wfMsg('rotate') )."</td>";
			#styles
			$string .= '<td>'.wfMsg('boxStyle').'<br/>'
				.self::form(
					$te,
					 XML::input('box_style', 40, $box->box_style, array('maxlength'=>255) ) .'<br/>'
					.wfMsg('boxStyleExample').'</td>'
					. '<td>'.wfMsg('headingStyle').'<br/>'
					.XML::input('style', 40, $box->heading_style, array('maxlength'=>255) )
					.self::form_button(wfMsg('saveStyles')).'<br/>'
					.wfMsg('headingStyleExample') ).'</td>';
			$string	.= "</tr></table><hr/>";
		}

		# bottom row of action controls
		$string .="<br/><table class=\"tableEdit_nav3\"><tr><td> "
			.self::form_button_only($te, wfMsg('saveToPage', $box->page_name), array('view' => 'save'))."</td>";
		$string .= "<td><a href='$wgServer$wgScriptPath/index.php?title=".urlencode($box->page_name)."'>".wfMsg('cancel')."</a></td>";
		$undelete = $warn = $revert = '';
		if($box->has_deleted_rows()) $undelete = self::form_button_only($te, wfMsg('undeleteRows'));
		$warn = '';
		if($box->is_changed || $box->has_deleted_rows()){
			$revert = self::form_button_only($te, wfMsg('revertToSaved'));
			$te->msg[] = "<span style='color:red'>".wfMsg('changesNotSavedUntil')."</span>";
		}

		$string .= "<td width = '50%' align = 'right'>$revert</td>"
			."<td>$undelete</td>"
			."<td width = '30%' align = 'right'>".self::form_button_only($te, wfMsg('deleteTable'), array('view'=>'delete'))."</td>"
			."</tr></table>";

		foreach($te->msg as $message) $warn .= $message.'<br />';
		if($box->help3 != '') $string .= "<hr />".self::text2html($box->help3);
		return $warn.$string;

	}

	# extra stuff at the end of each form visible only to admins
	/**
	 * 	This method prints some information for admins at the bottom of the page. It sometimes gets called with
	 *  $this->box, and sometimes with $this->row, so don't assume you know what object is coming in...do some
	 * 	testing.
	 *
	 * 	@param      object  	object from SpecialTableEdit.body.php
	 * 	@return     string 		What to display on the page.

	 */
	static function extras(TableEdit $te, WikiBox $box ){
		global $wgUser, $wgServer, $wgScriptPath;
		$string = '';
		#extra admin buttons
		if ($wgUser->isAllowed('userrights')){
			$string = "<br /><div class=\"tableEdit_extras\" \"><h3>Admin</h3>";
			if( $te->act['view'] != 'metadata') {
				$string .= self::form_button_only($te, wfMsg('viewMeta'), array('view'=>'metadata','row_index' => @$te->row_req['index']));
			}
			if ($te->act['view'] != 'box_dump') {
				$string .= self::form_button_only($te, wfMsg('dumpBox'),array('view'=>'box_dump'));
			}
			if ( isset($box->template) && $box->template != "" ) {
				$string .= sprintf('<b>Template:</b> <a href="%s%s/index.php?title=Template:%s">%s</a><br />', $wgServer, $wgScriptPath, $box->template, $box->template);
			}

			$string .= "</div>";
		}
		return $string;
	}


/**
 * This view is for when someone edits a field in a 'foreign'-type table and adds data. We need to ask the user
 * if they want to use their current data (in which case both tables on both pages need updating) or if they want
 * to revert to using the data that was pulled from the foreign table/page (in which case only the current table
 * needs to be saved.)
 * @param object $box box object from SpecialTableEdit.body.php
 * @return string What to display on the page.

 */
	function reconcile_foreign_table_view(WikiBox $box ){
		echo '<pre>' . var_export($box, true) . '</pre>';
		$string = wfMsg('reconcile_foreign_table', "<i style=\"font-size:larger\">that other table there</i>");
		$string .= '<br /><br />';
		$string .= self::form_button("Revert");
		$string .= self::form_button("Update");
		return $string;
	}

# ========================= Form building methods ==============================
	# wrap every view inside self::form.  This should include any hidden variables needed
	# either in $content, or passed as a separate array
	static function form(TableEdit $te, $content, $hiddens = array()){
		global $wgServer,$wgScriptPath;
		$form = "\n"
			. XML::openElement('form', array('method' =>'post'))."\n"
			. Html::hidden('id',$te->box_req['box_id'])."\n"
			. Html::hidden('conflict',$te->conflict)."\n"
			. Html::hidden('pagename',$te->page_name)."\n"
			. Html::hidden('ff','reloaded')."\n"
			. Html::hidden('browser_tab',$te->browser_tab)."\n"
			. $content;
		if (!array_key_exists ('view',$hiddens)) $form .= Html::hidden('view',$te->act['view']);
		foreach ($hiddens as $name => $value) $form .=  Html::hidden($name, $value);
		$form .= XML::closeElement('form')."\n";
		return $form;
	}

	# for one of a series of input boxes or textareas on a form
	public static function form_field($index, $value, $size=40, $type='textarea', $editable = true){
		$readonly = ($editable) ? '' : 'readonly';
		if ($type == 'textarea') {
			$string = Xml::openElement( 
				'textarea', 
				array( 
					'name' => "field[$index]", 
					'cols' => $size, 
					'rows' => 4 , 
					$readonly=>$readonly
					) 
			);
			$string .= $value;
			$string .= Xml::closeElement( 'textarea' );
		} elseif($type == 'hidden'){
			$string = Html::hidden("field[$index]", $value);
		} else {
			$string = XML::input("field[$index]", $size, $value, array('maxlength'=>255))."\n";
		}
		return	$string;
	}


	public static function form_button( $label ){
		return XML::submitButton( $label, array('name'=>'act') );
	}

	# creates a form that is just a button
	static function form_button_only(TableEdit $te, $submit, $extra = array()){
		return self::form($te, self::form_button($submit),$extra);
	}

	#makes the row of buttons next to a table display Edit, Copy, Delete
	static function edit_row_form(TableEdit $te, $row_index, $row, $type=''){
		global $wgUser;
		# row owner values:
		#	0 public
		#	>0 owner uid
		$content =
			 Html::hidden('row_index', $row_index)
			.Html::hidden('row_data' , $row->row_data)
			.Html::hidden('row_style', $row->row_style);
		if ($te->conflict !='') $content .= Html::hidden('conflict',$te->conflict);
		$edit 	= self::form_button(wfMsg('edit'  ) );
		$copy 	= self::form_button(wfMsg('copy'  ) );
		$delete = self::form_button(wfMsg('delete') );
		switch ($type){
			case "Copy":
				$edit = $delete = '';
				break;
			case "Delete":
				$edit = $copy = '';
				break;
		}
		$owner_uid = $row->owner_uid;
		$form = '';
		if($owner_uid == 0 || $owner_uid == $wgUser->getID() || in_array('bureaucrat', $wgUser->getEffectiveGroups())){
			$form .= self::form($te, "$content $edit $copy $delete");
			if($owner_uid == 0)	$form .= " public";
			return $form;
		}
		return self::form($te, "$content $copy") . '<i>protected</i>';
	}

	static function move_col_button(TableEdit $te, $key, WikiBox $box, $type = ''){
		$content = 	Html::hidden('col_index',$key) . Html::hidden('view',$te->act['view']);
		if ($type === '') $type = $box->type;
		switch ($type){
			case 1:
				$submit = '';
				$submit .=  ''.self::form_button('^').'';
				$submit .=  ''.self::form_button('v').'';
				break;
			default:
				$submit = '<table class="tableEdit_colButton" width = 100% style="border: 0px;border-collapse: collapse;" ><tr '.$box->heading_style.'>';
				$submit .=  '<td align=left> '.self::form_button('<').'</td>';
				$submit .=  '<td align=right>'.self::form_button('>').'</td>';
				$submit .= '</tr></table>';
		}
		return self::form($te, $submit.$content);
	}

	public static function prettytable(){
		return 'border="2" cellpadding="4" cellspacing="0" style="margin: 1em 1em 1em 0; border: 1px #aaa solid; border-collapse: collapse;"';
	}


	# makes base wikibox for nav view, conflict view and save to page.  All need some post-processing.
	static function make_pre_wikibox(TableEdit $te, WikiBox $box, $boxnum = 1 ){
		$table = '';
		$box_style = self::prettytable($box);
		$headings = explode("\n", $box->headings);
		foreach ($headings as $key => $value) $headings[$key] = "{{{__HEAD_".$key."__}}}".$value;

		// list the attributes of the table. (See Sanitizer::setupAttributeWhitelist() for a list of allowed attributes.)
		$attr = array(
			'id'		=> wikiBox::short_uid_from_box_uid( $box->box_uid ),
			'class'		=> array(
				'tableEdit',
				$box->template
			),
		);

		// if you find a class definition in the style, break it up and add it to the list of classes
		if ( preg_match('/class\s*=\s*[\'"]([^"]*)[\'"]/', $box->box_style, $m) ) {
			$defined_classes = preg_split("/\s+/", $m[1]);
			foreach ($defined_classes as $class) {
				$attr['class'][] = $class;
			}
		}

		// look through the list of templates to make into dataTables
		if (isset($box->is_dataTable) && $box->is_dataTable) {
			$attr['class'][] = 'dataTable';
		}

		// build up the attributes into a string we can insert into the wiki
		$attributes = "";
		foreach ($attr as $attribute => $value) {
			$text = "";
			if ($attribute !== "" && !is_null($attribute) && $value !== "" && !is_null($value)) {
				if ( $attribute == 'class') {
					foreach ($value as $x) $text .= sprintf(' %s', $x);
				}
				else {
					$text = $value;
				}
				$attributes .= sprintf(' %s="%s" ', $attribute, $text);
			}
		}
		// build the wiki-markup table
		switch ($box->type){
			case 1:
				// this is the "column-table" type - headings run down the left side of the table, top to bottom; you add columns
				$table .= "\n{| " . $box->box_style . " $attributes \n{{{__TOP__}}}\n";
				$tablerows = array();
				$i = 0;
				foreach ($headings as $heading){
					$i++;
					$tablerows[$i] = "|-\n!align=left $box->heading_style |$heading\n";
				}
				$i++;
				foreach ($box->rows as $row_index=>$row){
					if(isset($row->is_current) && $row->is_current){
						if($boxnum != 2 || $row->matched === false){
							$data = explode('||',$row->row_data);
							$row_style = '';
							if (isset($row->row_style)) $row_style = $row->row_style;
							if (	isset ($te->act['view']) && 
									$te->act['view'] == 'conflict' && 
									$row->matched === false){ 
								$row_style .= ' bgcolor=yellow';
							}	
							for ($j=1; $j<$i; $j++)	$tablerows[$j] .= "|$row_style|\n".@$data[$j-1]."\n";
						}
					}
				}
				foreach ($tablerows as $tablerow){
					$table .= "$tablerow";
				}
				$cols = $box->rownum() + 1;
				$table .= "{{{__BOTTOM__}}}\n|}\n";
				break;
			default:
				// this is the "row-table" type - headings run across the top of the table left to right; you add rows
				$table .= "\n{| ".$box_style." $attributes \n";
				$head = "|-";
				if ($box->heading_style != '') $head .= " $box->heading_style";
				$head .= "\n!{{{UL_STYLE}}}|{{{EDITHEADINGS}}}{{{__LEFT_H__}}}".implode("!!",$headings)."\n";

				$table .= $head;
				foreach ($box->rows as $row_index => $row){
					if (is_object($row) && $row->is_current){
						$row_style = '';
						if (isset($row->row_style)) $row_style = $row->row_style;
						if (isset($te->act['view']) && $te->act['view'] == 'conflict' && $row->matched === false) $row_style .= ' bgcolor=yellow';
						if($boxnum != 2 || $row->matched === false){
							$table .="|- $row_style\n|\n";
							$padding = '';
							while (count(explode('||',$row->row_data.$padding)) < $box->colnum){
								$padding .= " || ";
							}
							$data = "{{{__LEFT_".$row_index."__}}}||".$row->row_data." $padding\n";
							$table .= str_replace('||',"\n|\n",$data);
						}
					}
				}
				$table .= "\n|}\n";
		}
		$table = str_replace("\'","'",$table);
		$table = str_replace('\n',"\n",$table); #$this->debug[__METHOD__] = $table;
		return $table;
	}

	# converts prewikibox to html version by joining with edit controls
	static function box2html(TableEdit $te, WikiBox $box, $editheadings = '', $edit_form = array(), $move_buttons = false, $boxnum = 1){
		global $wgTitle, $wgParser, $wgUser;
		$table = self::make_pre_wikibox($te, $box, $boxnum);
		# fix and parse output
		$table = str_replace("\'","'", $table);
		$table = str_replace('\n',"\n", $table);
		$table = str_replace('{{{UL_STYLE}}}',"class = 'unsortable'", $table);

		preg_match('/class\s*=\s*"(.*?)"/', $table, $matches);
		if (isset($matches[1]) ) {
			$replace = $matches[1] . ' tableEdit_view';
			$table = str_replace ( $matches[1], $replace, $table );
		}

		$output = $wgParser->parse(
			$table,
			$wgTitle,
			ParserOptions::newFromUser( $wgUser )
		);
		$table = $output->getText();

		if ($box->template !='') $move_buttons = false;
		$headings = explode("\n",trim($box->headings));
		foreach ($headings as $key => $value){
			if($move_buttons === true) $table = str_replace("{{{__HEAD_".$key."__}}}", self::move_col_button($te, $key,$box), $table);
			else $table = str_replace("{{{__HEAD_".$key."__}}}",'',$table);
		}

		if (empty($edit_form))
		if(isset($box->rows) && is_array($box->rows)) {
			foreach ($box->rows as $row_index=>$row) if($row->is_current) $edit_form[$row_index] = '';
		}
		# put back forms
		if(isset($box->type) && $box->type == 1){
			$edit_row = "<tr><td>$editheadings</td><td>".implode("</td><td>", $edit_form)."</td></tr>";
			$table = str_replace(">\n{{{__TOP__}}}\n"," class = 'unsortable'>", $table);
			$table = str_replace('{{{__BOTTOM__}}}',$edit_row, $table);
		}else{
			$table = str_replace("{{{EDITHEADINGS}}}", "$editheadings",$table);
			$table = str_replace("{{{__LEFT_H__}}}", "</th><th>",$table);
			foreach ($edit_form as $row_index=>$form) $table = str_replace("{{{__LEFT_".$row_index."__}}}", $form, $table);
		}
		$te->debug[__METHOD__] = $table;
		return $table;
	}

	static function get_page_html($page_title){
		global $wgUser, $wgParser;
		$t =  Title::newFromText($page_title);
		if (!is_object($t)) return "";
		$rev = Revision::newFromTitle($t);
		if (!is_object($rev)) return "";
		$wikitext = $rev->getText();
		$parser_out = $wgParser->parse(
			$wikitext,
			$t,
			ParserOptions::newFromUser( $wgUser )
		);
		return $parser_out->getText();
	}

	static function text2html($text=''){
		global $wgUser, $wgParser, $wgTitle;
		$parser_out = $wgParser->parse(
			$text,
			$wgTitle,
			ParserOptions::newFromUser( $wgUser )
		);
		return $parser_out->getText();
	}
}