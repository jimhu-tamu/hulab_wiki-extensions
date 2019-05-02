<?php
/*
 ProcessCite.php - An extension 'module' for the PagesOnDemand extension.
 @author Jim Hu (jimhu@tamu.edu)
 @version 0.5
 @copyright Copyright (C) 2007-2019 Jim Hu
 @license The MIT License - http://www.opensource.org/licenses/mit-license.php 

* Features

Retrieves ref text from the following sources
		PubMed
		GO dbxrefs

if RefType:ID in the key or the text string enclosed by <ref name=key>text</ref>
Either of these can be catenated with a semicolon-delimited list of dbxrefs to be added as links 
 		Example: PMID:15849274.3BTAIR:Publication:501715204
The PMID should be the first element of this list

* Installation.  

In addition to the usual, ProcessCite requires
1. The PMID extension to add the PMIDeFetch class
2. a hook in Cite.php

if not already present, add
 			wfRunHooks( 'CiteBeforeStackEntry', array( &$key, &$val ) );
 at the start of function stack

3. Internal configs to for commonly used refs stored in the wiki that are not recognized automatically e.g.
a. set the prefix you want to use
	$libtag = 'LIB'; # the prefix for commonly used references to be pulled from a page in the wiki

b. set the name of the page in the wiki (in the main namespace) where the commonly used refs are stored 
	$lib_pageName = "$wgSitename Reference Library"; # the PAGENAMEE of the page where the commonly used references are stored
c. Edit that wikipage to store references as lines in the format refname|refinfo Example: 
	
	Darwin_Origin|Darwin, Charles (1859) ''On the origin of species'' [http://www.gutenberg.org/etext/1228 etext at Project Gutenberg]
d. Set the location of a file of dbxref urls.

* Changes in version 0.3
Separate to be in a directory outside Cite. Modify for wfLoadExtension system.
* Changes in version 0.2

Changed to use PMID extension to connect to NCBI eUtilities and manage XML caching.  Note that version 0.1 used esummary records, while version 0.2 uses the more complete XML from efetch, which includes abstract more information needed by PMIDonDemand.

Pushed to trunk
 */

if ( ! defined( 'MEDIAWIKI' ) ) die();


# Register hooks ('CiteBeforeStackEntry' hook is provided by a patch to the Cite extension).
# If hook not present in Cite, add it at the start of function stack

#$wgHooks['CiteBeforeStackEntry'][] = 'wfProcessCite';
//require_once('/Volumes/SAS/local/wiki-extensions/trunk/PMID/class.PMIDeFetch.php');

#global $wgExtensionPath;
#$wgAutoloadClasses['PMIDeFetch'] =  "$wgExtensionPath/PMID/class.PMIDeFetch.php";

/**
* params from cite: key is the name from <ref name =key>sometext</ref> or <ref name=key/>
* val is an associative array with keys text, count, and number.
*/

class ProcessCite{
	public static function processCitation( $key, &$str ){
		global $wgHooks, $wgSitename;

		# Configuration section
	
		$libtag = 'LIB'; # the prefix for commonly used references to be pulled from a page in the wiki
		$lib_pageName = "$wgSitename Reference Library"; # the PAGENAMEE of the page where the commonly used references are stored
		require "GO.xrf_abbs.php"; # load the dbxref url file
	
	
		#get the text enclosed in <ref> tags if present
		$my_text = $str;

		# get the key;xrefs list
		if (isset($key) && !is_int($key)){
			$tmp_key = $key;
		}else{
			$tmp_key = $my_text; #to handle situation where user puts the info inside <ref> instead of in <ref name =key>
		}
		$xrefs = explode('.3B',$tmp_key); #Cite changes the encoding of the semicolon


		$string = $my_text;
		$link ='';
	
		foreach ($xrefs as $my_key){
			$ref_fields = explode(':',$my_key);
		
			#these lines trim the extra _ when the user puts a space after colon in REFTYPE:data, then restore internal _
			$data = trim(str_replace('_',' ', array_pop($ref_fields)));	# assume the last element is the identifier data
			$data = str_replace(' ','_',$data);
			$ref_type = implode(':',$ref_fields); 	# reassemble the front part
	
			switch ($ref_type){
				case 'PMID':
					if(class_exists('PMIDeFetch')){
						$paper = new PMIDeFetch($data);
					}else{
						trigger_error('No PMIDeFetch!!!');
						throw Exception("PMID extension must be installed for PMIDeFetch class");
					}	
					$ref_fields = $paper->citation();
					$string = '';
	
					$author = array();
					$date = '('.$ref_fields['Year'].')';
					$title = $ref_fields['Title'];
					$source = $ref_fields['Journal'];
					$volume = $ref_fields['Volume'];
					$pages = $ref_fields['Pages'];
					$author = array();
					foreach($paper->authors() as $auth){
						$author[] = $auth['Cite_name'];
					}
				
					switch(count($author)){
						case 0:
							break;
						case 1:
							$string .= $author[0]."";
							break;
						case 2:
							$string .= $author[0]." &amp; ".$author[1];
							break;
						default:
							$string .= $author[0]." et al.";
					}
				
					$string .= " $date $title $source $volume $pages";	
					$link = " [http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&db=pubmed&dopt=Abstract&list_uids=$data PubMed]";
					# add internal link if PagesOnDemand is present.
					if (isset($wgHooks['PagesOnDemand']) && in_array('wfLoadPubmedPageOnDemand',$wgHooks['PagesOnDemand'])) $link .= " [[$key|$wgSitename page]]";
					unset($paper);
					break;
				case 'ISBN':
					break;
			
				# look in a library of reference information on a specific pagename
				case "$libtag":
					# load the data library
					$item = array();
					$data_page = new WikiPage(Title::newFromText($lib_pageName, NS_MAIN));
					if (! $data_page){
						trigger_error( "Library not found\n"); break; 
					}else{
						$revision = $data_page->getRevision();
						if(is_null($revision)){
							$content = ContentHandler::MakeContent('', $title);
						}else{
							$content = $revision->getContent( Revision::RAW );
						}
						$text = ContentHandler::getContentText( $content );
						$text = preg_replace( '/<noinclude>.*?<\/noinclude>/s', '', $text );
						$records = explode("\n",$text);
						foreach($records as $record){
							$tmp = explode('|',$record);
							# rejoin just in case the text has another |, which will happen with redirected wiki links
							$item[$tmp[0]] = implode('|',array_slice($tmp, 1)); 
						}

					}

					@$string = $item[$data];

					break;
				
				# add xref links
				case 'FB':
					$link .= " [http://flybase.org/.bin/fbidq.html?$data $data]";
					break;
				case 'SGD_REF':
					$link .= " [http://db.yeastgenome.org/cgi-bin/reference/reference.pl?dbid=$data $my_key]";
					break;
				case 'TAIR':
				case 'TAIR:Publication':
					$link .= " [http://www.arabidopsis.org/servlets/TairObject?type=publication&id=$data $my_key]";
					break;
				case 'TAIR:AnalysisReference':	
					$link .= " [http://www.arabidopsis.org/servlets/TairObject?type=analysis_reference&id=$data $my_key]";
					break;
				case 'TAIR:Communication':	
					$link = " [http://www.arabidopsis.org/servlets/TairObject?type=communication&id=$data $my_key]";
					break;
				case 'MGI:MGI':
					$string = 'MGI annotation';
					$data = "MGI:$data";
					$link .= " [".$dbxref_url['MGI']."$data $data] ";
					break;

				default:
					if (isset ($dbxref_url[$ref_type])){
						switch($ref_type){
							#tweak $data if needed
							case 'ENZYME':
								#enzyme database uses mod_rewrite to make EC number 1.1.1 into 1/1/1
								$val = str_replace('.','/',$data);
								break;
							case 'TIGR_REF':
								$data .='.shtml';
								break;
						}
						$link .= " [".$dbxref_url[$ref_type]."$data $my_key] "; 
					}
				} # end switch ref_type
			}
	
		$string .= " $link ";

		$my_text = $string;

		#Replace the reference text
		$str = $my_text;
		return true;
	}
}