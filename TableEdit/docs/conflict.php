<?php
$TableEditDocs['en']['tableEditDocs'] = Xml::element('h2',array(), "Resolving conflicts");

# common before
$doc.= <<<HELP

HELP;

#alternate text depending on the conflict. Can we tell?
switch ($this->conflict){
	case 'wiki':
$doc .= <<<HELP
This conflict view is triggered when the version of the table that is saved in the database is different from the one in the wiki page that called the Table Editor.  This usually means that someone ignored the warnings and edited the wiki directly.
HELP;
		break;
	case 'db':
$doc .= <<<HELP
Someone probably edited the table while you were working on it.
HELP;
		break;
	default:
$doc .= <<<HELP
The conflict view is triggered when either
<ul><li>The version of the table you clicked on in the wiki is inconsistent with what is in the database. This usually means that someone ignored the warnings and edited the wiki directly.</li><li>Someone saved the table to the wiki while you were working on it.</li></ul>

HELP;
}
# common after
$doc .= <<<HELP
You can resolve the conflicts by copying data rows over from the other version to your working copy.  If you were trying to save the table, you can click save to ignore the changed version.

Note that copying rows does not know how to keep the correct columns together.  If the column order has changed between the versions, you will have to do some more editing to make the sure the data is in the right places.
HELP;

$TableEditDocs['en']['tableEditDocs'] .= nl2br($doc);
?>