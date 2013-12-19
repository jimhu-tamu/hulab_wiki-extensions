<?php
$TableEditDocs['en']['tableEditDocs'] = Xml::element('h2',array(),"The basic Table Editor view");
$TableEditDocs['en']['tableEditDocs'] .= "
Table editing starts from a basic navigation view displaying the table as it will show up in the wiki, but with some buttons and forms added.

The information in the table can be thought of as being in rows and columns where a row is associated with a particular heading.  Depending on the orientation of the table, a 'row' discussed below might display as a column, and vice versa.

If you don't see a feature described below, it's probably because the table is controlled from an external template, or you don't have the needed privileges for that feature.

<h3>Heading row</h3>
The table headings are displayed on the top or left of the table, depending on it's orientation. When the table is in a horizontal orientation, it's sortable by clicking on the sort boxes next to the table headings.";

if ($this->box->template == '') $TableEditDocs['en']['tableEditDocs'] .= "
<h3>Columns</h3>
<ul>
<li>Edit headings button: Takes you to the form for editing the headings</li>
<li>Column reordering buttons: Shift columns left or right to change the order of the headings</li>
</ul>";
$TableEditDocs['en']['tableEditDocs'] .= "
<h3>Data rows</h3>
Row editing buttons appear either to the left of each row of data, or both above and below each column of data.
<ul>
<li>Edit: Takes you to the row editing form for that row</li>
<li>Copy: Makes a new row that's a duplicate of the one you clicked </li>
<li>Delete:Deletes the row.  The deletion isn't permanent. You can get a deleted row back by clicking Undelete rows or Revert to Saved</li>
</ul>
<h3>Table properties</h3>
Below the table, there are two sections.  One is for editing miscellaneous properties of the table as a whole.
<ul>
<li>Add data: create a new data row</li>
<li>Rotate table: Flips the table</li>
<li>Input for Table style</li>
<li>Input for Heading style</li>
</ul>
<h3>Table editing actions</h3>
<ul>
<li>Save to page: Saves your changes back to the wiki</li>
<li>Cancel: Returns to the wiki page without saving changes</li>
<li>Revert to saved: reverts the table to the last version saved in the database (this may not be the version in the wiki)</li>
<li>Undelete rows: only shows up if you've deleted rows.</li>
<li>Delete table: deletes the table after asking if you're sure.  You may need special privileges to delete tables, depending on how the wiki is configured</li>
</ul>
";
?>