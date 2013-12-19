<?php
/*
Script to sort pmid XML files from a flat directory to subdirectories used by PMID classes

After rebuilding, be sure to make all directories readable by the webserver
*/

# params: w: wiki path p:page name a:action
$params = getopt("w:");

# die if there is not a wiki, page, or action
if(!isset($params['w'])){
	die (usage());
}

# load the code libraries
$cmdLine = $params['w']."/maintenance/commandLine.inc";
if (!is_file($cmdLine)){
   die ("File $cmdLine not found in ".$params['w']."\n");
}
require_once ($cmdLine);

#check for global setting of Cache dir
if(!is_dir( $extEfetchCache)){
	 die('Cant find $extEfetchCache in LocalSettings');
}
$dh = opendir($extEfetchCache);
while (false !== ($filename = readdir($dh))) {
# 	echo $filename."<br>";
	if(strpos(" $filename","PMID") != 1) continue;
   	$files[] = $filename;
}

sort($files);

foreach ($files as $filename){
	echo "$filename\n";
	PMIDeFetch::save_to_cache(str_replace(array('PMID','.xml'), '', $filename), file_get_contents("$extEfetchCache/$filename"));
}	