<?php
/*
 * PMID_OnDemand.php - An extension 'module' for the PagesOnDemand extension.
 * @authora Jim R. Wilson (wilson.jim.r@gmail.com) and Jim Hu (jimhu@tamu.edu)
 * @version 0.2
 * @copyright Copyright (C) 2007 Jim R. Wilson
 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 
 version 0.2 includes 
 - change to use PMIDefetch extension
 - change to OO with reusable methods
 
 
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();

# Credits
$wgExtensionCredits['other'][] = array(
    'name'=>'PMID_OnDemand',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt; and Jim Wilson &lt;wilson.jim.r@gmail.com&gt;',
    'description'=>'Uses PagesOnDemand to generate wiki articles about papers indexed in the National Library of Medicine PubMed database on demand.',
    'version'=>'0.2'
);

# Register hooks ('PagesOnDemand' hook is provided by the PagesOnDemand extension).
$wgHooks['PagesOnDemand'][] = 'wfLoadPubmedPageOnDemand';

/**
* Loads a demo page if the title matches a particular pattern.
* @param Title title The Title to check or create.
*/
function wfLoadPubmedPageOnDemand( Title $title, $article ){
#	global $wgOut;
	$myTitle = preg_replace('/^PMID:([ _]|%20)*/','PMID:', $title->getDBkey());
	$myTitle = preg_replace('/^PMID:0+/','PMID:', $myTitle);
#	$linkstring = '';
	# Short-circuit if $title isn't in the MAIN namespace or doesn't match the PMID pattern.
	if ( $title->getNamespace() != NS_MAIN || !preg_match('/^PMID:\\d+$/', $myTitle ) ) {
		return true;
	}
	# replace the article object with one created with the fixed title
	$article = PMIDpageOnDemand::execute($myTitle);
	# All done (returning false to kill PoD's wfRunHooks stack)
	return false;
}

class PMIDpageOnDemand{

	static function execute($myTitle){
		# Create the Article's new text using the PMID/PMIDeFetch object class
		# requires that the PMID class be installed
		# load an array of stuff from the object
		$paper = new PMIDeFetch($myTitle);
		if(!is_object($paper->article())){ 
			global $wgOut;
			$wgOut->showErrorPage('error','nopmid',$myTitle);
			return true;
		}
		# rearranged for display
		$ref_parts = self::format_citation_parts($paper);
		# get the template
		$template = Revision::newFromTitle(Title::makeTitle(NS_TEMPLATE, 'PMID_page'));
		if (! $template){
			$text = "Template not found\n\n$refstring\n\n$abstract\n\n$linkstring";
		}else{
			$text = $template->getText();
			#strip out noinclude sections
			$text = preg_replace( '/<noinclude>.*?<\/noinclude>/s', '', $text );
			$text = str_replace('{{{REFERENCE}}}',$ref_parts->refstring,$text);
			$text = str_replace('{{{ABSTRACT}}}',$ref_parts->abstract,$text);
			$text = str_replace('{{{LINKS}}}',$ref_parts->linkstring,$text);
			$text = str_replace('{{{DOI}}}',$ref_parts->doistring,$text);
		}
	
		# Create the Article, supplying the new text
		# Don't use the passed title or article objects to handle cases (extra space, leading zeros) where we want to adjust the page name
		$title = Title::makeTitle(NS_MAIN, $myTitle);
		$article = new Article($title);
		# check again that the page doesn't already exist (we previously checked the original title, not the redirected one)
		if (!$title->exists() || $title->isDeleted()){
			$article->doEdit( 'PMID on Demand placeholder', 'New PMID: Page!', EDIT_NEW | EDIT_FORCE_BOT );
		
			#fill TableEdit table if it exists
			if (class_exists('TableEdit') && strpos($text, 'Template:PMID_info_table') > 0){
				$box = new wikiBox;
				$box->page_uid = $article->getID();
				$box->page_name = $myTitle;
				$box->template = "PMID_info_table";
				/*
				citation abstract links keywords
				*/
				$box->insert_row("$ref_parts->refstring||$ref_parts->abstract||$ref_parts->linkstring\n$ref_parts->doistring||$ref_parts->meshstring");
				$box->save_to_DB();
				$tableEdit = new TableEdit;
				$table = $tableEdit->make_wikibox($box);

				$text = str_replace('<newTableEdit>Template:PMID_info_table</newTableEdit>', $table ,$text);
		
			}		
			$article->doEdit( $text, 'Fill PMID: Page!', EDIT_UPDATE | EDIT_FORCE_BOT | EDIT_SUPPRESS_RC );
		}	
		# replace the article object in the call function with this one
		return $article;
	}

	static function format_citation_parts(PMIDeFetch $paper){
		$ref_fields = $paper->citation();	
		# assemble author list
		$author = array();
		foreach($paper->authors() as $auth){
			$author[] = $auth['Cite_name'];
		}
		switch (count($author)){
			case 0:
				$refstring = 'No author listed';
				break;
			case 1:
				$refstring = $author[0];
			case 2:
				$refstring = implode (' and ',$author);
				break;
			default:	
				$last_auth = array_pop($author);
				$refstring = implode(", ",$author). " and ".$last_auth;
		}
		if ($refstring != '') $refstring = "'''".$refstring."''' "; # bold author list

		# Year
		if ($ref_fields['Year'] != '') $refstring .= " (".$ref_fields['Year'].") ";	

		# Title
		$refstring .= $ref_fields['Title'];
		
		# Journal is either in an isoabbreviation or in medlineta or in journal, title.  Use abbreviation if available.
		$refstring .= " ''".$ref_fields['Journal']."'' '''".$ref_fields['Volume']."'''";
		if ($ref_fields['Pages'] != '') $refstring .= ":".$ref_fields['Pages'];	
		# xref links
		$linkstring = $doistring = '';
		foreach ($ref_fields['xrefs'] as $key => $val){
			switch($key){
				case 'pmid':
				case 'pubmed':
					$linkstring .= " [http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids=$val PubMed]";	
					break;
				case 'pmc':
					$linkstring .= " [http://www.ncbi.nlm.nih.gov/pmc/articles/$val $val]";	
					break;
				case 'doi':
					$doistring .= "Online version:[http://dx.doi.org/$val $val]";
					break;
			}
		}

		# Abstract
		if ($ref_fields['Abstract'] != ''){
			$abstract = $ref_fields['Abstract'];
		}else{
			$abstract = 'No abstract in PubMed';
		}
		# assemble stdclass object for return
		$ref_parts = new StdClass();
		$ref_parts->refstring = $refstring;
		$ref_parts->abstract = $abstract;
		$ref_parts->linkstring = trim($linkstring);
		$ref_parts->doistring = $doistring;
		
		# MESH headings	
		$ref_parts->meshstring = implode('; ',$paper->mesh());
		return $ref_parts;
	}
}	