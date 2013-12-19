<?php
$figspath = "http://trimer.tamu.edu/jh/images/TableEdit_docfigs";
$TableEditDocs['en']['tableEditDocs'] = "
<!--Note - image paths in this help file are hard coded and should be changed.-->
<h2>Using TableEdit</h2>
<ul>
<li><a href='#basic_usage'>Basic usage</a></li>
<li><a href='#basic_templates'>Basic templates</a></li>
<li><a href='#advanced_templates'>Advanced templates</a></li>
<li><a href='#additional_docs'>Additional documentation</a></li>
</ul>
<h3>Basic usage</h3><a id='basic_usage'></a>
<table border=1  align=right><tr><td><img src ='$figspath/fig1.jpg' alt = 'fig 1'><br/>Figure 1</td>
</tr></table>
<p>TableEdit provides a somewhat more friendly way to edit simple tables in mediawiki.  TableEdit tables show up in a page with their own Edit link, which calls this page.  Users who have editing privileges can then add rows and columns, rename headings, and change the style of the headings row.</p>
<h4>Adding a TableEdit table to a page</h4>
<h5>Step 1: Add a newTableEdit tag where you want the table</h5>

<p>To add a new table that can be edited by TableEdit, there are three options:
<ul>
<li>Add an empty table.  Put &lt;newTableEdit/> in the text, where you want the table to appear </li>
<li>Add a table with defined headings (Figure 1) put:<br>
&lt;newTableEdit>Column heading 1<br>Column heading 2<br>Column heading 3<br>etc...&lt;/newTableEdit><br>
You can edit the columns later if needed.</li>
<li>Use a predefined template. See <a href='#basic_templates'>Basic templates</a> below </li>
</ul>
</p>
<br clear='right'/><table border=1  align=right><tr><td><img src ='$figspath/fig2.jpg' alt = 'fig 2'><br/>Figure 2</td></tr></table>
<h5>Step 2: Save the page</h5>
An empty table with your headings appears when we save the page (Figure 2)
Click on the <b>Edit table</b> link and use the table editor.

<h5>Step 3: Edit the table</h5>
<br clear='right'/><table border=1  align=right>
<tr><td><img src ='$figspath/fig3.jpg' alt = 'fig 3' width = '500px'><br/>Figure 3</td></tr>
<tr><td><img src ='$figspath/fig4.jpg' alt = 'fig 4' width = '500px'><br/>Figure 4</td></tr>
<tr><td><img src ='$figspath/fig5.jpg' alt = 'fig 5' width = '500px'><br/>Figure 5</td></tr>
<tr><td><img src ='$figspath/fig6.jpg' alt = 'fig 6' width = '500px'><br/>Figure 6</td></tr>
<tr><td><img src ='$figspath/fig7.jpg' alt = 'fig 7' ><br/>Figure 7</td></tr>
</table>
<h4>Example</h4>
<ol>
<li>We create a table with some headings enclosed in newTableEdit tags</li>
<li>Click on the Edit Table  link to go to the Table Edit special page<br/></li>
<li>Click on <b>Add Data</b> to add a row (Figure 3)<br/>
I don't know who the governor is...let's leave that for someone else to add.</li>
<li>Save the new row in  the edit page (Figure 4).  Note that it has not been saved back to the wiki page yet</li>
<li>Let's add a column and make the heading background light blue. Click on Edit Headings.  Add a heading and name it.<br/>
Then set the heading style.</li>
<li>Save back to the editor again.</li>
<li>Save back to the wiki<br/></li>
</ol>
Some other things you can do with these tables:
<ul>
<li>Rotate them so the headings are on the left</li>
<li>Use templates inside tables, or as styles</li> 
</ul>
<br clear = 'all'/>


<h3>Basic Table templates</h3><a id='basic_templates'></a>
<table border=1  align=right>
<tr><td><img src ='$figspath/fig8.jpg' alt = 'fig 8' width = '500px'><br/>Figure 8</td></tr>
</table>
<p>For table formats that you plan to use on more than one page, you can define a template.  To add a table based on a template.  Put:
&lt;newTableEdit>Template:TemplateName &lt;/newTableEdit> The template should be a Template page in the wiki that has a list of headings, where each heading is on a separate line.  These will not be editable in the Special page, but changing the template page will change the headings on all tables that use that template.  The changes won't be apparent until someone tries to edit the table and save it back to the page.</p>
<p>Figure 8 shows a simple Template.  To use this, you would put &lt;newTableEdit>Template:TestBox &lt;/newTableEdit> in your wiki page</p>



<h3>Advanced templates</h3><a id='advanced_templates'></a>
<h4>Using tags to specify more properties</h4>
Attributes for the table can be wrapped in XML-like tags.
<ul>
<li>&lt;headings>&lt;/headings> for headings.  Headings can have additional attributes, which can be used to customize the user interface and link content of rows to external sources (see below)</li>
<li>&lt;heading_style>&lt;/heading_style> for heading style</li>
<li>&lt;table_style>&lt;/table_style> for table style</li>
<li>&lt;type>&lt;/type> for horizontal (default) or vertical (1) orientation</li>
</ul>
<h4>Programming the TableEdit interface</h4>
Heading lists can have additional markup to modify the behavior of the Table Editor.  Anything after a || in the headings list is considered part of a list of additional parameters for that heading.  Individual parameters are delimited by single |s.

The first parameter after the heading text is a unique column_name for the heading (this allows you to use non-unique names for the actual display).  If this is not present, the heading name is assumed to be usable as the column_name.  These are used for lookup and calculation fields as described below.
<pre>
  Syntax:
  Heading||column_name|rule
 
  rule syntax
    text                    to use input type=text instead of textarea
    select|1|2|etc          make a pulldown menu with choices 1, 2, etc
    lookup|sql|field        sql statement|name of field to return
                            sql statements use {{{column_name}}} in place of specific fields
		
   example: Aspect||aspect|lookup|SELECT namespace from go_archive.term WHERE go_id = '{{{1}}}' ORDER BY term_update DESC LIMIT 1|namespace
	
   currently available calcs
      split                 split|delimiter|x|y|z 
                            where x,y, and z are integers indicating which parts of the split to join into the result

      reqcomplete           reqcomplete|column_name|column_name|column_name...  
                            where column names are required fields.
                            returns \"complete\" or \"required field needed\"
</pre>
<h5>Example</h5>
<p>Figure 9 shows the kind of thing you can do with the programming interface</p>
<table border=1  align=left margin = 20px>
<tr><td><img src ='$figspath/fig9.jpg' alt = 'fig 9'><br/>Figure 9</td></tr>
</table>
<p>
<ul>
<li>Two of the fields are controlled by pulldown menus.</li>
<li>Aspect is looked up in an external database</li>
<li>GO term name is a combination of a lookup and some string manipulation.</li>
<li>Status is calculated based on whether there is content in other fields.</li>
</ul>
</p>
<br clear = 'all'>
<p>Here is the template code used to generate the editing interface shown above (the indented lines are actually all part of one line; I broke them up to make them easier to read):
<pre>
Qualifier||qualifier|select| |NOT
GO ID||go_id|text
GO term name||go_term|lookupcalc|SELECT page_title from go_archive.term WHERE go_id = '{{{go_id}}}' ORDER BY term_update DESC LIMIT 1|page_title|split|_!_|1
Reference(s)||refs
Evidence Code||evidence|select
   | 
   |IC: Inferred by Curator
   |IDA: Inferred from Direct Assay
   |IEA: Inferred from Electronic Annotation
   |IEP: Inferred from Expression Pattern
   |IGC: Inferred from Genomic Context
   |IGI: Inferred from Genetic Interaction
   |IMP: Inferred from Mutant Phenotype
   |IPI: Inferred from Physical Interaction
   |ISS: Inferred from Sequence or Structural Similarity
   |NAS: Non-traceable Author Statement
   |ND: No biological Data available
   |RCA: inferred from Reviewed Computational Analysis
   |TAS: Traceable Author Statement
   |NR: Not Recorded
with/from||with|text
Aspect||aspect|lookup|SELECT namespace from go_archive.term WHERE go_id = '{{{go_id}}}' ORDER BY term_update DESC LIMIT 1|namespace
Notes
Status||status|calc|reqcomplete|go_id|refs|go_term|evidence
</pre>

<h3>Additional Documentation</h3><a id='additional_docs'></a>
For Known issues, ToDos, discussion of TableEdit, and other documentation, see (<a href='http://www.mediawiki.org/wiki/Extension:TableEdit'>http://www.mediawiki.org/wiki/Extension:TableEdit</a>)

			";
		
?>			