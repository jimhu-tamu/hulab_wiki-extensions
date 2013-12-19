<?php

/**
 *	Page-Specific Dropdown list for TableEdit
 *
 *	This column rule defines how to make a dropdown list from things embedded within the wiki page.
 *	The column rule should be specified a "page-specific-dropdown" followed by a single "pipe" 
 *	character and then the name of the XML tag enclosing the items that will go in the list. 
 *	For example:
 *
 *	 	Isoform||isoform|page-specific-dropdown|te_dropdown1
 *
 *	In this example TableEdit will look for a list of items wrapped by the "te_dropdown1" XML tag to 
 * 	make the dropdown list from. It is a normal XML tag with an opening and closing tag. In this fashion 
 *	you can specify as many page-specific dropdown lists as you want, given that they have different associated
 *  dropdown lists. The list should be formatted like a bulletted list in wiki-formatting. A line that starts 
 * 	with a single asterisk character ("*") is interpretted as the start of a new option-group. A line that 
 *	starts with two asterisk characters is interpretted as an option. For a simple dropdown list the following 
 *	is fine (keeping with the above example):
 *
 *		<te_dropdown1>
 *		** option A
 *		** option B
 *		** option C
 *		</te_dropdown1>
 *
 *	You can (and SHOULD) wrap these drop-down lists in HTML comments in the wikipage - unless you want them to 
 *	show up and confuse your users. The above example becomes:
 *		<!--
 *		<te_dropdown1>
 *		** option A
 *		** option B
 *		** option C
 *		</te_dropdown1>
 *		-->
 *	
 *	If no tag is found that is specified, TableEdit reports and error and returns nothing for the list. 
 */


$wgExtensionCredits['other'][] = array(
    'name'				=> 'TableEdit:protein_properties',
    'author'			=> '[mailto:bluecurio@gmail.com Daniel Renfro]',
    'description'		=> 'Special column type for page-specific dropdown lists.',
    'version'			=> '0.1'
);


$wgHooks['TableEditApplyColumnRules'][] = 'ef_TableEdit_PSDD';

function ef_TableEdit_PSDD( $te, $rule_fields, $box, $row_data, $i, $type ){

	$column_rule_type 	= array_shift($rule_fields);
	if ($column_rule_type != 'page-specific-dropdown') return true;
	list($dropdown_tag, $not_found_behavior) = $rule_fields;
	if ( !isset($not_found_behavior) || is_null($not_found_behavior) ) {
		$not_found_behavior = "help";
	}
	$revision = Revision::newFromTitle( Title::newFromText($te->page_name) );
	
	$escaped_tag = preg_quote($dropdown_tag, "#");
	$regex = "#<" . $escaped_tag . ">(.*?)</" . $escaped_tag . ">#xms"; // like <tag>(.*?)</tag>
	
	$dropdown_list = preg_match(
		$regex,
		$revision->getText(),
		$matches
	);
	if ( !$matches || count($matches) == 0) {
			$error_message = '<span style="color:red; font-weight:bolder;">';
			$error_message .= "[[Help:Isoforms Help with isoforms]]";
			$error_message .= '</span><br />';
			$type = "ERROR";
			$row_data[$i] = $error_message;
			return false;
	}	

	$list = strip_tags($matches[1]);
	$html = XML::listDropDown($dropdown_tag,  $list);
	$type = "";
	$row_data[$i] = $html;
	return true;
}