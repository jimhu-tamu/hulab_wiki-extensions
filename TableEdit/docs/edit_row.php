<?php

$TableEditDocs['en']['tableEditDocs'] = Xml::element('h2',array(), "Editing row data");
$doc = 
"<p>The row editing view provides a form where you can enter or edit information for a specific row in the table.  You can also set a style for the row. </p>

<p>Tables controlled by templates can have pulldown menus and database lookups in the edit view.</p>

<p>TableEdit prevents you from saving row data with unclosed html tags.  In the examples below, the correct markup is:
&lt;ref name='name' /&gt;</p>

<ul>
<li>&lt;ref name='name'&gt; (missing a closing /)</li>
<li>&lt;ref name='name&gt; (missing a closing quote mark)</li>
<li>&lt;ref name='name' (missing a closing slash and &gt;)</li>
</ul>
<p>In all these cases, the improper markup could affect other rows or items below the table in the wiki page.  TableEdit prevents you from saving rows with these errors.</p>

<p>Note that this means that if you want to use \"less than\" you need to use a character code: &amp;lt; </p>
";

$TableEditDocs['en']['tableEditDocs'] .= $doc;

?>