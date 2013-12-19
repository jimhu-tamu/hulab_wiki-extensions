<?php
class TableEditLinks extends MediaWikiTestCase {
    public function testGoArchiveDbExists() {
	$dbr = & wfGetDB( DB_SLAVE );
	$sql =  "SELECT page_title FROM GO_archive.term LIMIT 1";

	$result = $dbr->query($sql);
	$this->assertEquals($dbr->numRows($result), 1);
    }
    /**
     * @depends testGoArchiveDbExists
     */
    //public function testStuff() {
    //}

}
