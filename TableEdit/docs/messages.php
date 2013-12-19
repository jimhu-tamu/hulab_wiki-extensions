<?php
$doc = Xml::element('h2',array(), "Error Messages");
$doc .= "<table>"
."<tr><td>".wfMsg('boxNotFound')."</td><td>Can't find the table</td></tr>"
."<tr><td>".wfMsg('pageNotFound')."</td><td>Can't find the page</td></tr>"
."<tr><td>".wfMsg('tableDupFound')."</td><td>There are two or more copies of this table with the same table ID.  TableEdit can't handle the ambiguity about which one to edit.</td></tr>"
."<tr><td>".wfMsg('page_id_mismatch')."</td><td>The id number of the page is different from the page where the table was originally saved</td></tr>"
."<tr><td>".wfMsg('page_name_mismatch')."</td><td>The page calling the table is not the same as the page where the table was originally saved</td></tr>"
."<tr><td>".wfMsg('setfromDBfailed')."</td><td>TableEdit failed to set the table from the database</td></tr>"
."<tr><td>".wfMsg('not allowed')."</td><td>You don't have the necessary permissions for that action</td></tr>"
."</table>";

$TableEditDocs['en']['tableEditDocs'] = nl2br($doc);
?>