<?php
$TableEditDocs['en']['tableEditDocs'] = Xml::element('h2',array(), "TableEdit for wiki admins");
$TableEditDocs['en']['tableEditDocs'].= <<<HELP

<ul>
<li><a href='#metadata'>Metadata</a></li>
<li><a href='#box_view'>Viewing the box object</a></li>
</ul>
<a id='metadata'>
<h3>Metadata</h3>
TableEdit can associate metadata with tables (boxes) or with rows.  Metadata is not displayed with the table, but can be mined by external scripts that use the TableEdit database tables for data mining.  There can be multiple metadata entries for either a table as a whole or a specific row in the table.

For both box and row metadata, the metadata view shows you existing metadata and allows you to add, delete, or edit items from the metadata list.

<a id='box_view'>
<h3>Viewing the box object</h3>
The box object view is just a dump of the object properties of the current table being edited. This view is mainly there for debugging.
HELP;
?>