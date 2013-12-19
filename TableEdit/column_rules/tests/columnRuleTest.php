<?php
class Quicklinks extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgSitename;

		if ( strcmp($wgSitename, 'GONUTS') == 0 ) {
		    $this->markTestSkipped("Quicklinks not part of GONUTS, unable to run tests");
		} else {
		    $this->quicklinks = new ecTableEditQuicklinks(null,null);
		}

	}
	public function testSequencePropertyExists() {
		$phys_prop = $this->quicklinks->product_physical_properties();
		$this->assertArrayHasKey('sequence', $phys_prop);
	}
	/**
	 * @depends testSequencePropertyExists
	 */
	public function testValidProteinSequenceData() {
	    $dbr = & wfGetDB( DB_SLAVE );
	    $quicklinks = $this->quicklinks;
	    $result = $quicklinks->_sql_box_row_data($dbr,
		"page_name like '%:Gene_Product(s)'");

	    $phys_prop = $quicklinks->product_physical_properties();

	    while($x = $dbr->fetchObject ( $result )){
		$tmp = explode('||', $x->row_data);
		$aaseq = $quicklinks->_protein_sequence($phys_prop,$x);
		$this->assertRegExp('/^[gastcvlimpfywdenqhkr]+|$/', $aaseq);
	    }


	}
}
