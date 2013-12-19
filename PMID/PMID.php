<?php
/*
Classes for using NCBI EUtils.

 * @license The MIT License - http://www.opensource.org/licenses/mit-license.php 
 * @author Jim Hu (jimhu@tamu.edu)
 * @version 0.1
 * @copyright Copyright (C) 2012 Jim Hu
 
 Set $extEfetchCache to a location writeable by the webserver


*/

$wgExtensionCredits['other'][] = array(
    'name'=>'PMID_OnDemand',
    'author'=>'Jim Hu &lt;jimhu@tamu.edu&gt;',
    'description'=>'Objects for using National Library of Medicine PubMed database via NCBI EUtils.',
    'version'=>'0.1'
);


// autoload classes
$wgAutoloadClasses['PMIDeFetch'] =  dirname(__FILE__)."/class.PMIDeFetch.php";

//cache directory. Set it here or in LocalSettings.php
#$extEfetchCache = "/Library/WebServer/tmp/pubmed";
