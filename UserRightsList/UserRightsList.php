<?php
/*
The MIT License
Copyright (c) <2006-208> <Jim Hu>
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

Versions
0.54 update special page for deprecated wfMsg function
0.53 update special page stuff to conform to most recent guidelines
0.52 patch for 1.14
	add select for users not assigned to other groups
0.51 patch for 1.13
0.5 release for 1.12
0.41 add db tablename lookup for better compatibility with installations not using MySQL
0.4 change base class and fix injection vulnerability
	more bug fixes
0.3 change database calls to more standard MW usage
	add russian translation
0.22 shading alternate rows
0.21 added return true to fix MW1.11 problems
0.2 fixed some unset variable problems

*/
# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/UserRightsList/UserRightsList.php" );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'UserRightsList',
	'author' => 'Jim Hu',
	'version'=>'0.54',
	'description' => 'list-based user rights management',
	'url' => 'http://www.mediawiki.org/wiki/Extension:UserRightsList'
);
$dir =  dirname(__FILE__);
$wgAutoloadClasses['UserRightsList'] = "$dir/SpecialUserRightsList.body.php";
$wgSpecialPages['UserRightsList'] = 'UserRightsList';
$wgExtensionMessagesFiles['UserRightsList'] = "$dir/SpecialUserRightsList.i18n.php";