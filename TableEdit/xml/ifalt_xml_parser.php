<?php
$sample_xml = <<<END
<?xml version="1.0"?>
<loader>
  <page>
    <name>Phage_P2_tin:Gene Product(s)Product</name>
    <table>
      <template>Product_structure_table</template>
      <row_data>
        <row>
          <field>&lt;beststructure&gt;lacZ
562&lt;/beststructure&gt;
* View [http://ecoliwiki.net/tools/PDB_Table/index.php?page_title={{PAGENAMEE}} all other structures].</field>
          <field>View models at:
* [http://dragon.bio.purdue.edu/ecolpredict2/localsearch/cgi-bin/search3.cgi?q=Phage_P2_tin&amp;sub=keywordSearc" EcoliPredict]
* [http://modbase.compbio.ucsf.edu/modbase-cgi/model_search.cgi?searchmode=default&amp;displaymode=moddetail&amp;searchproperties=ALL&amp;searchvalue=Phage_P2_tin&amp;organism=562&amp;organismtext= ModBase]
* [http://bioinf.cs.ucl.ac.uk/psipred/ PSI-PRED]
* [http://www.ebi.ac.uk/msd-srv/pqs/ Protein Quaternary Structure] (PQS)
* [http://spock.genes.nig.ac.jp/~genome/search.html Genomes TO Protein] (GTOP))</field>
          <metadata>New Sturcture Table Load</metadata>
          <update_type>append</update_type>
        </row>
      </row_data>
    </table>
  </page>
  <page>
    <name>Phage_P2_old:Gene Product(s)Product</name>
    <table>
      <template>Product_structure_table</template>
      <row_data>
        <row>
          <field>&lt;beststructure&gt;lacZ
562&lt;/beststructure&gt;
* View [http://ecoliwiki.net/tools/PDB_Table/index.php?page_title={{PAGENAMEE}} all other structures].</field>
          <field>View models at:
* [http://dragon.bio.purdue.edu/ecolpredict2/localsearch/cgi-bin/search3.cgi?q=Phage_P2_old&amp;sub=keywordSearc" EcoliPredict]
* [http://modbase.compbio.ucsf.edu/modbase-cgi/model_search.cgi?searchmode=default&amp;displaymode=moddetail&amp;searchproperties=ALL&amp;searchvalue=Phage_P2_old&amp;organism=562&amp;organismtext= ModBase]
* [http://bioinf.cs.ucl.ac.uk/psipred/ PSI-PRED]
* [http://www.ebi.ac.uk/msd-srv/pqs/ Protein Quaternary Structure] (PQS)
* [http://spock.genes.nig.ac.jp/~genome/search.html Genomes TO Protein] (GTOP))</field>
          <metadata>New Sturcture Table Load</metadata>
          <update_type>append</update_type>
        </row>
      </row_data>
    </table>
  </page>
</loader>
END;


$reader  = new XMLReader();
$reader->XML($sample_xml);

while($reader->read()){
	if ($reader->nodeType == XMLReader::TEXT
      || $reader->nodeType == XMLReader::CDATA
      || $reader->nodeType == XMLReader::WHITESPACE
      || $reader->nodeType == XMLReader::SIGNIFICANT_WHITESPACE) {
       $input .= $reader->value;
    }
}

$reader->close();
?>