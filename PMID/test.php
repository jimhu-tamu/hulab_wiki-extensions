<?php
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



#$o = new PMIDeFetch('1512252');
#$o = new PMIDeFetch('9278503'); # Blattner MG1655 sequence
#$o = new PMIDeFetch('22102568'); # GO Consortium
$o = new PMIDeFetch('22282803'); # Science paper about lambda evolution

#$o->dump();
#print_r($o->pmidObj);
print_r($o->article());
#echo $o->abstract_text();

print_r($o->mesh());
print_r($o->authors());
print_r($o->citation());

echo "\n\n";


function usage($msg = ''){
	global $argv;
	
	return "$msg 
USAGE ".$argv[0]." -w <path_to_wiki>	
";
}