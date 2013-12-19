<?php
/**
 *
 *
This module takes Mediawiki tables and parses them so they work with the
jQuery extension, Datatables. Datatables requires tables with <thead>.
tags. Since Mediawiki doesn't include <thead> when rendering a page, this 
extension intercepts the raw html and attempts to match any nested <tr><th> 
that aren't already surrounded by <thead> and wrap them accordingly.
 *
 *
 Originally written by Daniel Renfro. Modified by Nathan Liles. Refactored and modified by Jim Hu
 
 Changelog
 0.1	Refactored by JH to not treat the whole page at once. Markup should only happen witin
 		TableEdit Tables.
 0.02 	adjusted by NL
 
 *
 */

$wgExtensionCredits['parserhook'][] = array(
  'author'      => array(
		    '[mailto:bluecurio@gmail.com Daniel Renfro]',
		    '[mailto:nml5566@gmail.com Nathan Liles]'
		    ),
  'description' => 'Inserts a \'thead\' and \'tfoot\' tag into tables.',
  'name'        => 'TableMarkerUpper',
  'update'      => '2013-08-3',
  'url'         => 'http://ecoliwiki.net',
  'status'      => 'development',
  'type'        => 'hook',
  'version'     => 0.1
);

//Avoid unstubbing $wgParser on setHook() too early on modern (1.12+) MW versions, as per r35980
if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
	$wgHooks['ParserBeforeTidy'][] = 'TableEditTableMarkerUpper::execute';
} else { // Otherwise do things the old fashioned way
	$wgExtensionFunctions[] = "TableEditTableMarkerUpper::execute";
}


class TableEditTableMarkerUpper{

	public static function execute(&$parser, &$text){
		$tables = self::extract_tables($text);
		foreach($tables as $key=>$table){
			$new_table = self::do_header($table);
			$new_table = self::do_footer($new_table); #echo "<br>new $key $new_table"; 
			$text = str_replace($table, $new_table, $text);
		}
		#die;
		return true;
	}
	
	/*
	Adds thead and opens tbody
	*/
	public static function do_header($text){
		// find all the heading rows and drop them into a <thead>
		$regex = "/(?<!<thead>)".	    #Do not match rows preceded by thead		
				  "<tr[^<]*?"  .	    #Match an opening row not preceded by a left diamond bracket
				  "(<th[^<]*?<\/th>)+".	#and followed by multiple headers
				  ".*?"        .	    #followed by trailing characters such as newlines
				  "<\/tr>/xms";	        #followed by a closing row
			  
		preg_match_all($regex, $text, $matches);

		$already_replaced = array();
		foreach ($matches[0] as $old) {
			if ( !in_array($old, $already_replaced )  ) {

				# brackets in old interpreted as delimiting character ranges?

				if ( 
				  preg_match('!<thead>'.preg_quote($old).'</thead>!ms', $text) 
				  /*
				  Ignore any fixed <thead> tags with nested <td>
				  in order to skip tables with vertical headers
				  */
				  || preg_match('!<td>!ms', $old) 
				) {
					array_push( $already_replaced, $old );
					continue;
				}


				$new = "<thead>" . $old . "</thead>";
				$text = str_replace($old, $new, $text);
				array_push( $already_replaced, $old );
			}
		}
		return $text;
	}
	
	/*
	Closes tbody and adds tfoot element to the footer row.
	*/
	public static function do_footer($text){
		// find the rows with class="sortbottom" and drop them into a <tfoot>
		$regex = '!
				  <tr
				  \s+
				  class=
				  "tableEdit_footer.*?"
				  >
				  .*?
				  </tr>
				  !xms';
		preg_match_all($regex, $text, $matches);
		foreach ($matches[0] as $old) {
			$new = "";
			$new = "<tfoot>\n" . $old . "</tfoot>";
			$text = str_replace($old, $new, $text);
		}
		return $text;
	}

	/*
	ParserAfterStrip has removed the comment tags used to delimit TableEdit tables in WikiText.
	This extracts tables with a tableEdit or dataTable class
	
	http://blog.stevenlevithan.com/archives/match-innermost-html-element
	#$pattern = "/<table\b[^>]*>(?:(?>[^<]+)|<(?!table\b[^>]*>))*?<\/table>/ms";

	*/
	public static function extract_tables($text){
		$tarr = array();
		// many tables
		$pattern = "/<table[^>]*(tableEdit|dataTable)[^>]*>.*<\/table>/Ums";

		preg_match_all($pattern, $text, $tables);
	#	foreach($tables[0] as $table){
		#	if(strpos($table, 'tableEdit') == 0 && strpos($table, 'dataTable') == 0) continue;
	#	}
		return $tables[0];
	
	}
}

