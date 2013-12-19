<?php
$TableEditDocs['en']['tableEditDocs'] = Xml::element('h2',array(), "Editing Headings");
$TableEditDocs['en']['tableEditDocs'] .= <<<HELP
This view allows you to 
<ul>
	<li>change the labels on the headings</li>
	<li>create new columns</li>
	<li>delete columns</li>
	<li>rearrange the order of the columns.  Moving the columns moves the data associated with them, even though you can't see it in this view.  You can also move columns from the basic editing view.</li>
	<li>change the display style for the headings</li>
</ul>
<h3>Heading styles</h3>
Heading styles are based on html style commands.  There are two formats based on how the html specification for tables works. Many browsers will still recognize attributes like bgcolor and align, but you can also set a style using CSS.

<p>For the text color, use either 
<code>style='background-color:color'</code>
For the background color, use either 
<code>style='color:color'</code>
in these examples, 'color' can be either a color name or a hexadecimal code for the color.  See <a href='http://en.wikipedia.org/wiki/Web_colors'>Wikipedia</a> or search for 'web colors' or 'html color picker' to find many websites that describe how to use colors.</p>

Here are some examples of some combinations
<table><tr>
<td>
<table border="2" cellpadding="4" cellspacing="0" style="margin: 1em 1em 1em 0; border: 1px #aaa solid; border-collapse: collapse;"'>
<tr style = 'color:darkblue;background-color:lightgreen'><td>style = 'color:darkblue;background-color:lightgreen'</td></tr>
<tr style = 'color:red;background-color:yellow'><td>style = 'color:red;background-color:yellow'</td><tr>
<tr style = 'color:blue;background-color:pink'><td>style = 'color:blue;background-color:pink'</td><tr>
<tr style = 'color:darkblue;text-align:right'><td>style = 'color:darkblue;text-align:right'</td><tr>
</table>
</td>
<td>
<table border="2" cellpadding="4" cellspacing="0" style="margin: 1em 1em 1em 0; border: 1px #aaa solid; border-collapse: collapse;"'>
<tr style = 'color:darkblue;background-color:#ccccff'><td>style = 'color:darkblue;background-color:#ccccff'</td></tr>
<tr style = 'color:red;background-color:#cccccc'><td>style = 'color:red;background-color:#cccccc'</td><tr>
<tr style = 'color:#ff00ff;background-color:#000000'><td>style = 'color:#ff00ff;background-color:#000000'</td><tr>
<tr style = 'color:darkblue;text-align:center'><td>style = 'color:darkblue;text-align:center'</td><tr>
</table>
</td>
<td>
<table border="2" cellpadding="4" cellspacing="0" style="margin: 1em 1em 1em 0; border: 1px #aaa solid; border-collapse: collapse;"'>
<tr style = 'font-family:times,serif;background-color:#eeeeee'><td>style = 'font-family:times,serif;background-color:#eeeeee'</td></tr>
<tr style = 'color:maroon;background-color:wheat'><td>style = 'color:maroon;background-color:wheat'</td><tr>
<tr style = 'font-size:large;background-color:#ccccff'><td>style = 'font-size:large;background-color:#ccccff'</td><tr>
<tr style = 'color:darkblue;text-align:left'><td>style = 'color:darkblue;text-align:left'</td><tr>
</table>
</td>
</tr></table>
HELP;
?>