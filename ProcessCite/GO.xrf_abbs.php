<?php
#!version: $Revision: 2.120 $
#!date: $Date: 2006/06/29 15:10:50 $
#!
#!Gene Ontology
#!Abbreviations for cross-referenced databases.
#!
#!Note that URLs are not necessarily stable entities and that some
#!databases may have many other access routes or mirror sites.
#!
#!This data is available as a web page at
#!http://www.geneontology.org/cgi-bin/xrefs.cgi
#!

#Abbreviation: AgBase
#database: AgBase resource for functional analysis of agricultural plant and animal gene products
#generic_url: http://www.agbase.msstate.edu/
#url_syntax: http://www.agbase.msstate.edu/cgi-bin/getEntry.pl?db_pick=[ChickGO/MaizeGO]&uid=[ProteinID]
#
$dbxref_url['AgBase:ChickGO'] = "http://www.agbase.msstate.edu/cgi-bin/getEntry.pl?db_pick=ChickGO&uid=";
$dbxref_url['AgBase:MaizeGO'] = "http://www.agbase.msstate.edu/cgi-bin/getEntry.pl?db_pick=MaizeGO&uid=";



#Abbreviation: AGI_LocusCode
#database: Arabidopsis Genome Initiative (TAIR, TIGR, MIPS)
#object: Locus identifier
#example_id: AGI_LocusCode:At2g17950
#generic_url: http://www.arabidopsis.org
#url_syntax: http://mips.gsf.de/cgi-bin/proj/thal/search_gene?code=
#url_syntax: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=ath1&orf=
#url_syntax: http://arabidopsis.org/servlets/TairObject?type=locus&name=
#url_example: http://mips.gsf.de/cgi-bin/proj/thal/search_gene?code=At2g17950
#url_example: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=ath1&orf=At2g17950
#url_example: http://arabidopsis.org/servlets/TairObject?type=locus&name=At2g17950
#See also TAIR
$dbxref_url['AGI_LocusCode'] = "http://arabidopsis.org/servlets/TairObject?type=locus&name=";

#

#Abbreviation: AGRICOLA_bib
#database: AGRICultural OnLine Access
#object: AGRICOLA call number
#example_id: AGRICOLA_bib:MARC TAG 016
#example_id: AGRICOLA_bib:bib=0000-05160
#generic_url: http://agricola.nal.usda.gov/
#
$dbxref_url['AGRICOLA_bib'] = "http://agricola.nal.usda.gov/?";



#Abbreviation: AGRICOLA_IND
#database: AGRICultural OnLine Access
#object: AGRICOLA call number
#example_id: AGRICOLA_IND:IND84014403
#generic_url: http://agricola.cos.com/
#
$dbxref_url['AGRICOLA_IND'] = "http://agricola.cos.com/?";


#Abbreviation: AGRICOLA_NAL
#database: AGRICultural OnLine Access
#object: AGRICOLA call number
#example_id: AGRICOLA_NAL:TP248.2 P76 v.14
#generic_url: http://agricola.nal.usda.gov/
#
$dbxref_url['AGRICOLA_NAL'] = "http://agricola.nal.usda.gov/?";


#Abbreviation: BIOMD
#database: BioModels Database
#object: Accession
#synonym: BIOMDID
#example_id: BIOMD:BIOMD0000000045
#generic_url: http://www.ebi.ac.uk/biomodels/
#url_syntax: http://www.ebi.ac.uk/compneur-srv/biomodels-main/publ-model.do?mid=
#url_example: http://www.ebi.ac.uk/compneur-srv/biomodels-main/publ-model.do?mid=BIOMD0000000045
#
$dbxref_url['BIOMD'] = "http://www.ebi.ac.uk/compneur-srv/biomodels-main/publ-model.do?mid=";
$dbxref_url['BIOMDID'] = "http://www.ebi.ac.uk/compneur-srv/biomodels-main/publ-model.do?mid=";


#Abbreviation: BIOSIS
#database: BIOSIS previews
#object: Identifier
#example_id: BIOSIS:200200247281
#generic_url: http://www.biosis.org/
#
$dbxref_url['BIOSIS'] = "http://www.biosis.org/";


#Abbreviation: BRENDA
#database: BRENDA, The Comprehensive Enzyme Information System
#object: EC enzyme identifier
#example_id: BRENDA:4.2.1.3
#generic_url: http://www.brenda.uni-koeln.de/
#url_syntax: http://www.brenda.uni-koeln.de/php/result_flat.php4?ecno=
#url_example: http://www.brenda.uni-koeln.de/php/result_flat.php4?ecno=4.2.1.3
#
$dbxref_url['BRENDA'] = "http://www.brenda.uni-koeln.de/php/result_flat.php4?ecno=";

#! Note that CBS examples are not in the traditional accession number style because the entities in question are not entries in a database, and don't have accession numbers#

#Abbreviation: CBS
#database: Center for Biological Sequence Analysis
#object: prediction tool
#example_id: CBS:TMHMM
#example_id: CBS:SignalP
#example_id: CBS:ProP
#example_id: CBS:TargetP
#example_id: CBS:NetPhos
#example_id: CBS:NetOGlyc
#example_id: CBS:NetNGlyc
#generic_url: http://www.cbs.dtu.dk/
#url_example: http://www.cbs.dtu.dk/services/TMHMM/
#
$dbxref_url['CBS'] = "http://www.cbs.dtu.dk/services/";



#! note: The CGD is a synonym for the CGDID
#

#Abbreviation: CGD
#database: Candida Genome Database
#object: Identifier for CGD Loci
#example_id: CGD:CAL0005516
#synonym: CGDID
#generic_url: http://www.candidagenome.org/
#url_syntax: http://www.candidagenome.org/cgi-bin/locus.pl?sgdid=
#url_example: http://www.candidagenome.org/cgi-bin/locus.pl?sgdid=CAL0005516
#
$dbxref_url['CGD'] = "http://www.candidagenome.org/cgi-bin/locus.pl?sgdid=";
$dbxref_url['CGDID'] = "http://www.candidagenome.org/cgi-bin/locus.pl?sgdid=";

#! note: The CGD object also includes the orf19 assembly names, eg. orf19.2475
#
#Abbreviation: CGD_LOCUS
#database: Candida Genome Database
#object: Gene name (gene symbol in mammalian nomenclature)
#example_id: CGD_LOCUS:HWP1
#example_id: CGD_LOCUS:orf19.2475
#generic_url: http://www.candidagenome.org/
#url_syntax: http://www.candidagenome.org/cgi-bin/locus.pl?locus=
#url_example: http://www.candidagenome.org/cgi-bin/locus.pl?locus=HWP1
#url_example: http://www.candidagenome.org/cgi-bin/locus.pl?locus=orf19.2475
#
$dbxref_url['CGD_LOCUS'] = "http://www.candidagenome.org/cgi-bin/locus.pl?locus=";


#Abbreviation: CGD_REF
#database: Candida Genome Database
#object: Literature Reference Identifier
#example_id: CGD_REF:1490
#generic_url: http://www.candidagenome.org/
#url_syntax: http://www.candidagenome.org/cgi-bin/reference/reference.pl?refNo=
#url_example: http://www.candidagenome.org/cgi-bin/reference/reference.pl?refNo=1490
#
$dbxref_url['CGD_REF'] = "http://www.candidagenome.org/cgi-bin/reference/reference.pl?refNo=";

#! note: also see GO/gene-associations/readme/Compugen.README
#
#Abbreviation: CGEN
#database: Compugen Gene Ontology Gene Association Data
#object: Identifier
#example_id: CGEN:PrID131022
#generic_url: http://www.cgen.com/
#
$dbxref_url['CGEN'] = "http://www.cgen.com/?";


#Abbreviation: CGSC
#database: CGSC: E.coli Genetic Stock Center
#object: Gene symbol
#example_id: CGSC:rbsK
#generic_url: http://cgsc.biology.yale.edu/
#url_example: http://cgsc.biology.yale.edu/cgi-bin/sybgw/cgsc/Site/315
#
#use for gene name
$dbxref_url['CGSC'] = "http://cgsc.biology.yale.edu/cgi-bin/sybgw/cgsc/Site/?name=";


#Abbreviation: ChEBI
#database: Chemical Entities of Biological Interest
#object: Identifier
#example_id: ChEBI:17234
#generic_url: http://www.ebi.ac.uk/chebi/
#url_syntax: http://www.ebi.ac.uk/chebi/searchId.do?chebiId=
#url_example: http://www.ebi.ac.uk/chebi/searchId.do?chebiId=CHEBI:17234
#
$dbxref_url['ChEBI'] = "http://www.ebi.ac.uk/chebi/searchId.do?chebiId=";


#Abbreviation: CL
#database: Cell Type Ontology
#object: Identifier
#example_id: CL:0000041
#generic_url: https://lists.sourceforge.net/lists/listinfo/obo-cell-type
$dbxref_url['CL'] = "https://lists.sourceforge.net/lists/listinfo/obo-cell-type";

#Abbreviation: COG
#database: NCBI Clusters of Orthologous Groups
#generic_url: http://www.ncbi.nlm.nih.gov/COG/
$dbxref_url['COG'] = "http://www.ncbi.nlm.nih.gov/COG/old/palox.cgi?";

#Abbreviation: COG_Cluster
#database: NCBI COG cluster
#object: Identifier
#example_id: COG_Cluster:COG0001
#generic_url: http://www.ncbi.nlm.nih.gov/COG/
#url_syntax: http://www.ncbi.nlm.nih.gov/COG/new/release/cow.cgi?cog=
#url_example: http://www.ncbi.nlm.nih.gov/COG/new/release/cow.cgi?cog=COG0001
$dbxref_url['COG_Cluster'] = "http://www.ncbi.nlm.nih.gov/COG/new/release/cow.cgi?cog=";

#Abbreviation: COG_Function
#database: NCBI COG function
#object: Identifier
#example_id: COG_Function:H
#generic_url: http://www.ncbi.nlm.nih.gov/COG/
#url_syntax: http://www.ncbi.nlm.nih.gov/COG/grace/shokog.cgi?fun=
#url_example: http://www.ncbi.nlm.nih.gov/COG/grace/shokog.cgi?fun=H
$dbxref_url['Synonym'] = "url";

#Abbreviation: COG_Pathway
#database: NCBI COG pathway
#object: Identifier
#example_id: COG_Pathway:14
#generic_url: http://www.ncbi.nlm.nih.gov/COG/
#url_syntax: http://www.ncbi.nlm.nih.gov/COG/new/release/coglist.cgi?pathw=
#url_example: http://www.ncbi.nlm.nih.gov/COG/new/release/coglist.cgi?pathw=14
$dbxref_url['COG_Function'] = "http://www.ncbi.nlm.nih.gov/COG/new/release/coglist.cgi?pathw=";

#Abbreviation: DDB
#database: DictyBase
#object: Accession ID
#example_id: DDB:DDB0001836
#generic_url: http://dictybase.org
#url_syntax: http://dictybase.org/db/cgi-bin/gene_page.pl?dictybaseid=
#url_example: http://dictybase.org/db/cgi-bin/gene_page.pl?dictybaseid=DDB0001836
$dbxref_url['DDB'] = "http://dictybase.org/db/cgi-bin/gene_page.pl?dictybaseid=";

#Abbreviation: DDB_gene_name
#database: DictyBase
#object: Locus
#example_id: DDB_gene_name:mlcE
#generic_url: http://dictybase.org
#url_syntax: http://dictybase.org/db/cgi-bin/gene_page.pl?gene_name=
#url_example: http://dictybase.org/db/cgi-bin/gene_page.pl?gene_name=mlcE
$dbxref_url['DDB_gene_name'] = "http://dictybase.org/db/cgi-bin/gene_page.pl?gene_name=";

#Abbreviation: DDB_REF
#database: DictyBase literature references
#object: Literature Reference Identifier
#example_id: DDB_REF:10157
#generic_url: http://dictybase.org
#url_syntax: http://dictybase.org/db/cgi-bin/dictyBase/reference/reference.pl?refNo=
#url_example: http://dictybase.org/db/cgi-bin/dictyBase/reference/reference.pl?refNo=10157
$dbxref_url['DDB_REF'] = "http://dictybase.org/db/cgi-bin/dictyBase/reference/reference.pl?refNo=";

#Abbreviation: EC
#database: The Enzyme Commission
#example_id: EC:1.1.1.1
#generic_url: http://www.chem.qmw.ac.uk/iubmb/enzyme/
#url_example: http://www.chem.qmw.ac.uk/iubmb/enzyme/EC1/1/1/1.html
$dbxref_url['EC'] = "http://www.chem.qmw.ac.uk/iubmb/enzyme/EC1/";

#Abbreviation: EchoBASE
#database: EchoBASE post-genomic database for Escherichia coli
#object: Identifier
#example_id: EchoBASE:EB0231
#generic_url: http://www.ecoli-york.org/
#url_syntax: http://www.biolws1.york.ac.uk/echobase/Gene.cfm?recordID=
#url_example: http://www.biolws1.york.ac.uk/echobase/Gene.cfm?recordID=EB0231
$dbxref_url['EchoBASE'] = "http://www.biolws1.york.ac.uk/echobase/Gene.cfm?recordID=";

#Abbreviation: EcoCyc
#database: The Encyclopedia of E. coli metabolism
#object: Pathway identifier
#example_id: EcoCyc:P2-PWY
#generic_url: http://ecocyc.org/
#url_syntax: http://biocyc.org/ECOLI/NEW-IMAGE?type=PATHWAY&object=
#url_example: http://biocyc.org/ECOLI/NEW-IMAGE?type=PATHWAY&object=P2-PWY
$dbxref_url['EcoCyc'] = "http://biocyc.org/ECOLI/NEW-IMAGE?type=PATHWAY&object=";

#Abbreviation: ECOGENE
#database: CGSC: E.coli Genetic Stock Center
#object: EcoGene Accession Number
#example_id: ECOGENE:EG10818
#generic_url: http://cgsc.biology.yale.edu/
#url_example: http://cgsc.biology.yale.edu/cgi-bin/sybgw/cgsc/Site/315
$dbxref_url['ECOGENE'] = "http://ecogene.org/geneinfo.php?eg_id=EG10527";

#Abbreviation: ECOGENE_G
#database: CGSC: E.coli Genetic Stock Center
#object: EcoGene Primary Gene Name
#example_id: ECOGENE_G:deoC
#generic_url: http://cgsc.biology.yale.edu/
#$dbxref_url['Synonym'] = "url";

#Abbreviation: EMBL
#database: International Nucleotide Sequence Database Collaboration, comprising EMBL-EBI International Nucleotide Sequence Data Library (EMBL-Bank), DNA DataBank of Japan (DDBJ), and NCBI GenBank
#object: Sequence accession number
#example_id: EMBL:AA816246
#example_id: DDBJ:AA816246
#example_id: GB:AA816246
#synonym: DDBJ
#synonym: GB
#synonym: GenBank
#generic_url: http://www.ebi.ac.uk/embl/
#generic_url: http://www.ddbj.nig.ac.jp/
#generic_url: http://www.ncbi.nlm.nih.gov/Genbank/
#url_syntax: http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?db=nucleotide&val=
#url_syntax: http://www.ebi.ac.uk/cgi-bin/emblfetch?style=html&Submit=Go&id=
#url_syntax: http://arsa.ddbj.nig.ac.jp/arsa/ddbjSplSearch?KeyWord=
#url_example: http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?db=nucleotide&val=AA816246
#url_example: http://www.ebi.ac.uk/cgi-bin/emblfetch?style=html&Submit=Go&id=AA816246
#url_example: http://arsa.ddbj.nig.ac.jp/arsa/ddbjSplSearch?KeyWord=AA816246
#
$dbxref_url['EMBL'] = "http://www.ebi.ac.uk/cgi-bin/emblfetch?style=html&Submit=Go&id=";
$dbxref_url['DDBJ'] = "http://arsa.ddbj.nig.ac.jp/arsa/ddbjSplSearch?KeyWord=";
$dbxref_url['GB'] = "http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?db=nucleotide&val=";
$dbxref_url['GenBank'] = "http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?db=nucleotide&val=";

#Abbreviation: ENSEMBL
#database: Database of automatically annotated genomic data
#object: Identifier
#example_id: ENSEMBL:ENSP00000265949
#generic_url: http://www.ensembl.org/
#url_syntax: http://www.ensembl.org/perl/protview?peptide=
#url_example: http://www.ensembl.org/perl/protview?peptide=ENSP00000265949
$dbxref_url['ENSEMBL'] = "http://www.ensembl.org/perl/protview?peptide=";

#Abbreviation: ENZYME
#database: The Swiss Institute of Bioinformatics database of Enzymes
#object: Identifier
#example_id: ENZYME:EC 1.1.1.1
#generic_url: http://www.expasy.ch/
#url_syntax: http://www.expasy.ch/cgi-bin/nicezyme.pl?
#url_example: http://www.expasy.ch/cgi-bin/nicezyme.pl?1.1.1.1
#
#process identifier in $id = script str_replace('.','/',$id);
$dbxref_url['ENZYME'] = "http://www.expasy.ch/cgi-bin/nicezyme.pl?";

#! note: For FB [NCBI use FLYBASE]
#Abbreviation: FB
#database: FlyBase
#object: Gene identifier
#example_id: FB:FBgn0000024
#generic_url: http://flybase.bio.indiana.edu/
#url_syntax: http://flybase.bio.indiana.edu/.bin/fbidq.html?
#url_example: http://flybase.bio.indiana.edu/.bin/fbidq.html?FBgn0000180
$dbxref_url['FB'] = "http://flybase.org/.bin/fbidq.html?";

#Abbreviation: FLYBASE
#database: FlyBase
#object: Gene symbol
#example_id: FLYBASE:Adh
#generic_url: http://flybase.bio.indiana.edu/
#url_syntax: http://flybase.bio.indiana.edu/.bin/fbidq.html?
$dbxref_url['FLYBASE'] = "http://flybase.org/.bin/fbidq.html?";
$dbxref_url['flybase'] = "http://flybase.org/.bin/fbidq.html?";

#Abbreviation: GDB
#database: Human Genome Database
#object: Accession number
#example_id: GDB:306600
#generic_url: http://www.gdb.org/
#url_syntax: http://www.gdb.org/gdb-bin/genera/accno?accessionNum=
#url_example: http://www.gdb.org/gdb-bin/genera/accno?accessionNum=GDB:306600
$dbxref_url['GDB'] = "http://www.gdb.org/gdb-bin/genera/accno?accessionNum=";

#Abbreviation: GeneDB_Gmorsitans
#shorthand_name: Tsetse
#database: GeneDB_Gmorsitans
#object: Gene identifier
#example_id: GeneDB_Gmorsitans:Gmm-0142
#generic_url: http://www.genedb.org/genedb/glossina/
#url_syntax: http://www.genedb.org/genedb/Search?organism=glossina&name=
#url_example: http://www.genedb.org/genedb/Search?organism=glossina&name=Gmm-0142
$dbxref_url['GeneDB_Gmorsitans'] = "http://www.genedb.org/genedb/Search?organism=glossina&name=";

#Abbreviation: GeneDB_Lmajor
#shorthand_name: Lmajor
#database: GeneDB_Lmajor
#object: Gene identifier
#example_id: GeneDB_Lmajor:LM5.32
#generic_url: http://www.genedb.org/genedb/leish/
#url_syntax: http://www.genedb.org/genedb/Search?organism=leish&name=
#url_example: http://www.genedb.org/genedb/Search?organism=leish&name=LM5.32
$dbxref_url['GeneDB_Lmajor'] = "http://www.genedb.org/genedb/Search?organism=leish&name=";

#Abbreviation: GeneDB_Pfalciparum
#shorthand_name: Pfalciparum
#database: GeneDB_Pfalciparum
#object: Gene identifier
#example_id: GeneDB_Pfalciparum:PFD0755c
#generic_url: http://www.genedb.org/genedb/malaria/
#url_syntax: http://www.genedb.org/genedb/Search?organism=malaria&name=
#url_example: http://www.genedb.org/genedb/Search?organism=malaria&name=PFD0755c
$dbxref_url['GeneDB_Pfalciparum'] = "http://www.genedb.org/genedb/Search?organism=malaria&name=";

#Abbreviation: GeneDB_Spombe
#shorthand_name: Spombe
#database: GeneDB_Spombe
#object: Gene identifier
#example_id: GeneDB_Spombe:SPAC890.04C
#generic_url: http://www.genedb.org/genedb/pombe/
#url_syntax: http://www.genedb.org/genedb/Search?organism=pombe&name=
#url_example: http://www.genedb.org/genedb/Search?organism=pombe&name=SPAC890.04C
$dbxref_url['GeneDB_Spombe'] = "http://www.genedb.org/genedb/Search?organism=pombe&name=";

#Abbreviation: GeneDB_Tbrucei
#shorthand_name: Tbrucei
#database: GeneDB_Tbrucei
#object: Gene identifier
#example_id: GeneDB_Tbrucei:Tb927.1.5250
#generic_url: http://www.genedb.org/genedb/tryp/
#url_syntax: http://www.genedb.org/genedb/Search?organism=tryp&name=
#url_example: http://www.genedb.org/genedb/Search?organism=tryp&name=Tb927.1.5250
$dbxref_url['GeneDB_Tbrucei'] = "http://www.genedb.org/genedb/Search?organism=tryp&name=";

#Abbreviation: GenProtEC
#database: GenProtEC E. coli genome and proteome database
#generic_url: http://genprotec.mbl.edu/
$dbxref_url['GenProtEC'] = "http://genprotec.mbl.edu/?";

#Abbreviation: GermOnline
#database: GermOnline
#object: Identifier
#example_id: GermOnline:140116
#generic_url: http://www.germonline.org/
#url_syntax: http://germonline.unibas.ch/gene_page.php?orf_id=
#url_syntax: http://germonline.yeastgenome.org/gene_page.php?orf_id=
#url_syntax: http://germonline.biochem.s.u-tokyo.ac.jp/gene_page.php?orf_id=
#url_example: http://germonline.unibas.ch/gene_page.php?orf_id=140116
#url_example: http://germonline.yeastgenome.org/gene_page.php?orf_id=140116
#url_example: http://germonline.biochem.s.u-tokyo.ac.jp/gene_page.php?orf_id=140116
$dbxref_url['GermOnline'] = "http://germonline.yeastgenome.org/gene_page.php?orf_id=";

#Abbreviation: GO
#database: Gene Ontology Database
#object: Identifier
#example_id: GO:0046703
#generic_url: http://godatabase.org/cgi-bin/go.cgi?query=
#url_syntax: http://godatabase.org/cgi-bin/go.cgi?query=GO:0046703
#
#! note: generic_url and url_example for GO_REF to be filled in when available
$dbxref_url['GO'] = "http://gowiki.tamu.edu/GO/wiki/index.php/Special:Search?go=Go&search=";

#Abbreviation: GO_REF
#database: Gene Ontology Database references
#object: Accession (for reference)
#example_id: GO_REF:0000001
#generic_url:
#url_example:
#$dbxref_url['GO_REF'] = "url";

#Abbreviation: GOA
#database: GO Annotation at EBI
#generic_url: http://www.ebi.ac.uk/goa/
$dbxref_url['GOA'] = "http://www.ebi.ac.uk/goa/?";

#Abbreviation: GOC
#database: Gene Ontology Consortium
#generic_url: http://www.geneontology.org/
$dbxref_url['GOC'] = "http://www.geneontology.org/?";

#Abbreviation: GR
#database: Gramene: A Comparative Mapping Resource for Grains
#object: Protein
#example_id: GR:P93436
#generic_url: http://www.gramene.org/
#url_syntax: http://www.gramene.org/perl/protein_search?acc=Accession number 
#url_example: http://www.gramene.org/perl/protein_search?acc=P93436 
$dbxref_url['GR'] = "http://www.gramene.org/perl/protein_search?acc=";

#Abbreviation: GR_MUT
#database: Gramene: A Comparative Mapping Resource for Grains
#object: Mutant
#example_id: GR_MUT:659
#generic_url: http://www.gramene.org/
#url_syntax: http://www.gramene.org/db/mutant/search_mutant?id=ID number
#url_example: http://www.gramene.org/db/mutant/search_mutant?id=GR:0060198
$dbxref_url['GR_MUT'] = "http://www.gramene.org/db/mutant/search_mutant?id=";

#Abbreviation: GR_protein
#database: Gramene: A Comparative Mapping Resource for Grains
#object: Protein identifier
#example_id: GR_protein:110916
#generic_url: http://www.gramene.org/
#url_syntax: http://www.gramene.org/db/protein/protein_search?protein_id=
#url_example: http://www.gramene.org/db/protein/protein_search?protein_id=110916
$dbxref_url['GR_protein'] = "http://www.gramene.org/db/protein/protein_search?protein_id=";

#Abbreviation: GR_REF
#database: Gramene: A Comparative Mapping Resource for Grains
#object: Reference
#example_id: GR_REF:659
#generic_url: http://www.gramene.org/
#url_syntax: http://www.gramene.org/perl/pub_search?ref_id=ID number
#url_example: http://www.gramene.org/perl/pub_search?ref_id=659
$dbxref_url['GR_REF'] = "http://www.gramene.org/perl/pub_search?ref_id=";

#Abbreviation: H-invDB
#database: H-invitational Database
#generic_url: http://www.h-invitational.jp/
$dbxref_url['H-invDB'] = "http://www.h-invitational.jp/?";

#Abbreviation: H-invDB_cDNA
#database: H-invitational Database
#object: Accession
#example_id: H-invDB_cDNA:AK093148
#generic_url: http://www.h-invitational.jp/
#url_syntax: http://www.jbirc.aist.go.jp/hinv/soup/pub_Detail.pl?acc_id=
#url_syntax: http://www.h-invdb.jbic.or.jp/soup/pub_Detail.pl?acc_id=
#url_syntax: http://www.jbirc.aist.go.jp/hinv/soup/pub_Detail.pl?acc_id=
#url_example: http://www.jbirc.aist.go.jp/hinv/soup/pub_Detail.pl?acc_id=AK093149
#url_example: http://www.h-invdb.jbic.or.jp/soup/pub_Detail.pl?acc_id=AK093149
#url_example: http://www.jbirc.aist.go.jp/hinv/soup/pub_Detail.pl?acc_id=AK093149
$dbxref_url['H-invDB_cDNA'] = "http://www.h-invdb.jbic.or.jp/soup/pub_Detail.pl?acc_id=";

#Abbreviation: H-invDB_locus
#database: H-invitational Database
#object: Cluster identifier
#example_id: H-invDB_locus:HIX0014446
#generic_url: http://www.h-invitational.jp/
#url_syntax: http://www.jbirc.aist.go.jp/hinv/soup/pub_Locus.pl?locus_id=
#url_syntax: http://www.h-invdb.jbic.or.jp/soup/pub_Locus.pl?locus_id=
#url_syntax: http://www.jbirc.aist.go.jp/hinv/soup/pub_Locus.pl?locus_id=
#url_example: http://www.jbirc.aist.go.jp/hinv/soup/pub_Locus.pl?locus_id=HIX0014446
#url_example: http://www.h-invdb.jbic.or.jp/soup/pub_Locus.pl?locus_id=HIX0014446
#url_example: http://www.jbirc.aist.go.jp/hinv/soup/pub_Locus.pl?locus_id=HIX0014446
$dbxref_url['H-invDB_locus'] = "http://www.h-invdb.jbic.or.jp/soup/pub_Locus.pl?locus_id=";

#Abbreviation: HAMAP
#database: High-quality Automated and Manual Annotation of microbial Proteomes
#object: Identifier
#example_id: HAMAP:MF_00031
#generic_url: http://us.expasy.org/sprot/hamap/
#url_syntax: http://us.expasy.org/unirules/
#url_example: http://us.expasy.org/unirules/MF_00031
$dbxref_url['HAMAP'] = "http://us.expasy.org/unirules/";

#Abbreviation: HGNC
#database: HUGO Gene Nomenclature Committee
#object: Gene symbol
#example_id: HGNC:HNRPK
#generic_url: http://www.gene.ucl.ac.uk/nomenclature/
#url_example: http://www.gene.ucl.ac.uk/cgi-bin/nomenclature/searchgenes.pl?field=symbol&anchor=begins&match=HNRPK&symbol_search=Search&page_size=25&limit=1000&.cgifields=limit&.cgifields=page_size
$dbxref_url['HGNC'] = "http://www.gene.ucl.ac.uk/cgi-bin/nomenclature/searchgenes.pl?field=symbol&anchor=begins&symbol_search=Search&page_size=25&limit=1000&.cgifields=limit&.cgifields=page_size&match=";

#Abbreviation: HUGO
#database: Human Genome Organisation
#generic_url: http://www.hugo-international.org/
$dbxref_url['HUGO'] = "http://www.hugo-international.org/?";

#Abbreviation: IMGT_HLA
#database: Immunogenetics database, human MHC
#example_id: IMGT_HLA:HLA00031
#generic_url: http://www.ebi.ac.uk/imgt/hla
$dbxref_url['IMGT_HLA'] = "http://www.ebi.ac.uk/imgt/hla?";

#Abbreviation: IMGT_LIGM
#database: Immunogenetics database, immunoglobulins and T-cell receptors
#example_id: IMGT_LIGM:U03895
#generic_url: http://imgt.cines.fr
$dbxref_url['IMGT_LIGM'] = "http://imgt.cines.fr?";

#Abbreviation: IntAct
#database: IntAct protein interaction database
#object: Accession
#example_id: IntAct:EBI-17086
#generic_url: http://www.ebi.ac.uk/intact/
#url_syntax: http://www.ebi.ac.uk/intact/search/do/search?searchString=
#url_example: http://www.ebi.ac.uk/intact/search/do/search?searchString=EBI-17086
$dbxref_url['IntAct'] = "http://www.ebi.ac.uk/intact/search/do/search?searchString=";

#Abbreviation: InterPro
#database: The InterPro database of protein domains and motifs
#object: Identifier
#synonym: INTERPRO
#synonym: IPR
#example_id: InterPro:IPR000001
#generic_url: http://www.ebi.ac.uk/interpro/
#url_syntax: http://www.ebi.ac.uk/interpro/DisplayIproEntry?ac=
#url_example: http://www.ebi.ac.uk/interpro/DisplayIproEntry?ac=IPR000001
#
#! note: IPI identifiers are in the format ID.version
#
$dbxref_url['InterPro'] = "http://www.ebi.ac.uk/interpro/DisplayIproEntry?ac=";
$dbxref_url['INTERPRO'] = "http://www.ebi.ac.uk/interpro/DisplayIproEntry?ac=";
$dbxref_url['IPR'] = "http://www.ebi.ac.uk/interpro/DisplayIproEntry?ac=";

#Abbreviation: IPI
#database: International Protein Index
#object: Identifier
#example_id: IPI:IPI00000005.1
#generic_url: http://www.ebi.ac.uk/IPI/IPIhelp.html
$dbxref_url['IPI'] = "http://www.ebi.ac.uk/IPI/?";

#Abbreviation: ISBN
#database: International Standard Book Number
#object: Identifier
#example_id: ISBN:0781702534
#generic_url: http://isbntools.com/
#url_syntax: http://my.linkbaton.com/get?lbCC=q&nC=q&genre=book&item=
#url_example: http://my.linkbaton.com/get?lbCC=q&nC=q&genre=book&item=0781702534
$dbxref_url['ISBN'] = "http://my.linkbaton.com/get?lbCC=q&nC=q&genre=book&item=";

#Abbreviation: ISSN
#database: International Standard Serial Number
#object: Identifier
#example_id: ISSN:1234-1231
#generic_url: http://www.issn.org/
$dbxref_url['ISSN'] = "http://www.issn.org/?";

#Abbreviation: IUPHAR
#database: The IUPHAR Compendium of Receptor Characterization and Classification
#object: Receptor code
#example_id: IUPHAR:2.1.CBD
$dbxref_url['IUPHAR'] = "http://www.iuphar-db.org/GPCR/ReceptorDisplayForward?receptorID=";

#Abbreviation: KEGG
#database: Kyoto Encyclopedia of Genes and Genomes
#generic_url: http://www.genome.ad.jp/kegg/
$dbxref_url['KEGG'] = "http://www.genome.ad.jp/kegg/?";

#Abbreviation: KEGG_PATHWAY
#database: KEGG Pathways Database
#object: Pathway
#example_id: KEGG_PATHWAY:ot00020
#generic_url: http://www.genome.ad.jp/kegg/docs/upd_pathway.html
#url_syntax: http://www.genome.ad.jp/dbget-bin/www_bget?path:
#url_example: http://www.genome.ad.jp/dbget-bin/www_bget?path:ot00020
$dbxref_url['KEGG_PATHWAY'] = "http://www.genome.ad.jp/dbget-bin/www_bget?path:";

#Abbreviation: LIFEdb
#database: LIFEdb, a database for the integration and dissemination of functional data
#object: cDNA clone identifier
#example_id: LIFEdb:DKFZp564O1716
#generic_url: http://www.lifedb.de/
#url_syntax: http://www.dkfz.de/LIFEdb/LIFEdb.aspx?ID=
#url_example: http://www.dkfz.de/LIFEdb/LIFEdb.aspx?ID=DKFZp564O1716
$dbxref_url['LIFEdb'] = "http://www.dkfz.de/LIFEdb/LIFEdb.aspx?ID=";

#Abbreviation: LIGAND
#database: KEGG LIGAND Database
#object: Enzyme or Compound
#example_id: LIGAND:C00577
#example_id: LIGAND:EC 1.1.1.1
#url_syntax: http://www.genome.ad.jp/dbget-bin/www_bget?ec:
#url_syntax: http://www.genome.ad.jp/dbget-bin/www_bget?cpd:
#generic_url: http://www.genome.ad.jp/kegg/docs/upd_ligand.html#ENZYME
#generic_url: http://www.genome.ad.jp/kegg/docs/upd_ligand.html#COMPOUND
#url_example: http://www.genome.ad.jp/dbget-bin/www_bget?ec:1.1.1.1
#url_example: http://www.genome.ad.jp/dbget-bin/www_bget?cpd:C00577
$dbxref_url['LIGAND'] = "http://www.genome.ad.jp/dbget-bin/www_bget?cpd:";
$dbxref_url['LIGAND:EC'] = "http://www.genome.ad.jp/dbget-bin/www_bget?ec:";

#Abbreviation: LocusID
#database: NCBI LocusLink ID
#object: Identifier
#example_id: LocusID:3195
#generic_url: http://www.ncbi.nlm.nih.gov/
#url_syntax: http://www.ncbi.nlm.nih.gov:80/LocusLink/LocRpt.cgi?l=
#url_example: http://www.ncbi.nlm.nih.gov:80/LocusLink/LocRpt.cgi?l=3195
$dbxref_url['LocusID'] = "http://www.ncbi.nlm.nih.gov:80/LocusLink/LocRpt.cgi?l=";

#Abbreviation: MA
#database: Adult Mouse Anatomical Dictionary; part of Gene Expression Database
#object: Identifier
#example_id: MA:0000003
#generic_url: http://www.informatics.jax.org/
#url_syntax: http://www.informatics.jax.org/searches/AMA.cgi?id=
#url_example: http://www.informatics.jax.org/searches/AMA.cgi?id=MA:0000003
$dbxref_url['MA'] = "tp://www.informatics.jax.org/searches/AMA.cgi?id=";

#Abbreviation: MaizeGDB
#database: MaizeGDB
#object: MaizeGDB Object ID Number
#example_id: MaizeGDB:881225
#generic_url: http://www.maizegdb.org
#url_syntax: http://www.maizegdb.org/cgi-bin/id_search.cgi?id=
#url_example: http://www.maizegdb.org/cgi-bin/id_search.cgi?id=881225
$dbxref_url['MaizeGDB'] = "http://www.maizegdb.org/cgi-bin/id_search.cgi?id=";

#Abbreviation: MaizeGDB_Locus
#database: MaizeGDB
#object: Maize gene name
#example_id: MaizeGDB_Locus:ZmPK1
#generic_url: http://www.maizegdb.org
#url_syntax: http://www.maizegdb.org/cgi-bin/displaylocusresults.cgi?term=?
#url_example: http://www.maizegdb.org/cgi-bin/displaylocusresults.cgi?term=ZmPK1
$dbxref_url['MaizeGDB_Locus'] = "http://www.maizegdb.org/cgi-bin/displaylocusresults.cgi?term=";

#Abbreviation: MEDLINE
#database: The Medline literature database
#object: Identifier
#example_id: MEDLINE:20572430
#$dbxref_url['MEDLINE'] = "url";

#Abbreviation: MEROPS
#database: MEROPS - the Peptidase Database
#object: Identifier
#example_id: MEROPS:A01.001
#generic_url: http://merops.sanger.ac.uk/
#broken link
#$dbxref_url['MEROPS'] = "http://merops.sanger.ac.uk/";

#Abbreviation: MEROPS_fam
#database: MEROPS: The Peptidase Database
#object: Peptidase family identifier
#example_id: MEROPS_fam:M18
#generic_url: http://merops.sanger.ac.uk/
#url_syntax: http://merops.sanger/ac/uk/famcards/
#url_example: http://merops.sanger.ac.uk/famcards/M18.htm
#broken link
#$dbxref_url['MEROPS_fam'] = "http://merops.sanger/ac/uk/famcards/";

#Abbreviation: MeSH
#database: Medical Subject Headings 
#object: MeSH heading
#example_id: MeSH:mitosis
#generic_url: http://www.nlm.nih.gov/mesh/2005/MBrowser.html
#url_syntax: http://www.nlm.nih.gov/cgi/mesh/2005/MB_cgi?mode=&term=
#url_example: http://www.nlm.nih.gov/cgi/mesh/2005/MB_cgi?mode=&term=mitosis
$dbxref_url['MeSH'] = "http://www.nlm.nih.gov/cgi/mesh/2005/MB_cgi?mode=&term=";

#Abbreviation: MetaCyc
#database: The Metabolic Encyclopedia of metabolic and other pathways
#object: Pathway identifier
#example_id: MetaCyc:GLUTDEG-PWY
#generic_url: http://metacyc.org/
#url_syntax: http://biocyc.org/META/NEW-IMAGE?type=NIL&object=
#url_example: http://biocyc.org/META/NEW-IMAGE?type=NIL&object=GLUTDEG-PWY
$dbxref_url['MetaCyc'] = "http://biocyc.org/META/NEW-IMAGE?type=NIL&object=";

#Abbreviation: MGD
#database: Mouse Genome Database
#object: Gene symbol
#example_id: MGD:Adcy9
#generic_url: http://www.informatics.jax.org/
#url_syntax: http://www.informatics.jax.org/searches/marker.cgi?
$dbxref_url['MGD'] = "http://www.informatics.jax.org/searches/marker.cgi?";

#Abbreviation: MGI
#database: Mouse Genome Informatics
#object: Accession number
#example_id: MGI:80863
#generic_url: http://www.informatics.jax.org/
#url_syntax: http://www.informatics.jax.org/searches/accession_report.cgi?id=
#url_example: http://www.informatics.jax.org/searches/accession_report.cgi?id=MGI:80863
$dbxref_url['MGI'] = "http://www.informatics.jax.org/searches/accession_report.cgi?id=";

#Abbreviation: MIPS_funcat
#database: MIPS Functional Catalogue
#object: Identifier
#example_id: MIPS_funcat:11.02
#generic_url: http://mips.gsf.de/proj/funcatDB/
#url_syntax: http://mips.gsf.de/cgi-bin/proj/funcatDB/search_advanced.pl?action=2&wert=
#url_example: http://mips.gsf.de/cgi-bin/proj/funcatDB/search_advanced.pl?action=2&wert=11.02
$dbxref_url['MIPS_funcat'] = "http://mips.gsf.de/cgi-bin/proj/funcatDB/search_advanced.pl?action=2&wert=";

#Abbreviation: MO
#database: The MGED Ontology
#object: ontology term
#example_id: MO:Action
#generic_url: http://mged.sourceforge.net/ontologies/MGEDontology.php
#url_syntax: http://mged.sourceforge.net/ontologies/MGEDontology.php#term
#url_example: http://mged.sourceforge.net/ontologies/MGEDontology.php#Action
$dbxref_url['MO'] = "http://mged.sourceforge.net/ontologies/MGEDontology.php#";

#Abbreviation: MultiFun
#database: MultiFun, a cellfunction assignment schema
#generic_url: http://genprotec.mbl.edu/files/Multifun.html
$dbxref_url['MultiFun'] = "http://genprotec.mbl.edu/files/Multifun.html?";

#Abbreviation: NASC_code
#database: Nottingham Arabidopsis Stock Centre Seeds Database
#object: NASC code Identifier
#example_id: NASC_code:N3371
#generic_url: http://arabidopsis.info
#url_syntax: http://seeds.nottingham.ac.uk/NASC/stockatidb.lasso?code=
#url_example: http://seeds.nottingham.ac.uk/NASC/stockatidb.lasso?code=N3371
$dbxref_url['NASC_code'] = "http://seeds.nottingham.ac.uk/NASC/stockatidb.lasso?code=";

#Abbreviation: NC-IUBMB
#database: Nomenclature Committee of the International Union of Biochemistry and Molecular Biology
#generic_url: http://www.chem.qmw.ac.uk/iubmb/
$dbxref_url['NC-IUBMB'] = "http://www.chem.qmw.ac.uk/iubmb/?";

#Abbreviation: NCBI
#database: National Center for Biotechnology Information, Bethesda
#object: Prefix
#example_id: NCBI_gi
#generic_url: http://www.ncbi.nlm.nih.gov/
$dbxref_url['NCBI'] = "http://www.ncbi.nlm.nih.gov/";

#Abbreviation: NCBI_gi
#database: NCBI databases
#object: Identifier
#example: NCBI_gi:10727410
#generic_url: http://www.ncbi.nlm.nih.gov/
#url_syntax: http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=nucleotide&dopt=GenBank&list_uids=
#url_syntax: http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=protein&dopt=GenBank&list_uids=
#url_example: http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=nucleotide&dopt=GenBank&list_uids=10727410
#url_example: http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=protein&dopt=GenBank&list_uids=30580598
$dbxref_url['NCBI_gi'] = "http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=nucleotide&dopt=GenBank&list_uids=";

#Abbreviation: NCBI_GP
#database: NCBI GenPept
#object: Protein identifier
#example_id: NCBI_GP:EAL72968
#generic_url: http://www.ncbi.nlm.nih.gov/
#url_syntax: http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?db=protein&val=
#url_example: http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?db=protein&val=EAL72968
$dbxref_url['NCBI_GP'] = "http://www.ncbi.nlm.nih.gov/entrez/viewer.fcgi?db=protein&val=";

#Abbreviation: NCBI_NM
#database: NCBI RefSeq
#object: mRNA identifier
#synonym: RefSeq
#example_id: NCBI_NM:123456
#generic_url: http://www.ncbi.nlm.nih.gov/
#url_syntax:
#
$dbxref_url['NCBI_NM'] = "http://www.ncbi.nlm.nih.gov/?";

#Abbreviation: NCBI_NP
#database: NCBI RefSeq
#object: Protein identifier
#synonym: RefSeq
#example_id: NCBI_NP:123456
#generic_url: http://www.ncbi.nlm.nih.gov/
#url_syntax:
#
$dbxref_url['NCBI_NP'] = "http://www.ncbi.nlm.nih.gov/";

#Abbreviation: OMIM
#database: Mendelian Inheritance in Man
#object: Identifier
#synonym: MIM [used by NCBI]
#example_id: OMIM:190198
#generic_url: http://www3.ncbi.nlm.nih.gov/Omim/searchomim.html
#url_syntax: http://www3.ncbi.nlm.nih.gov/htbin-post/Omim/dispmim?
#url_example: http://www3.ncbi.nlm.nih.gov/htbin-post/Omim/dispmim?190198
#
$dbxref_url['OMIM'] = "http://www3.ncbi.nlm.nih.gov/htbin-post/Omim/dispmim?";
$dbxref_url['MIM'] = "http://www3.ncbi.nlm.nih.gov/htbin-post/Omim/dispmim?";

#Abbreviation: PAMGO
#database: Plant-Associated Microbe Gene Ontology Interest Group
#generic_url: http://pamgo.vbi.vt.edu/
$dbxref_url['PAMGO'] = "http://pamgo.vbi.vt.edu/";

#Abbreviation: PANTHER
#database: Protein ANalysis THrough Evolutionary Relationships Classification System
#generic_url: http://www.pantherdb.org/
$dbxref_url['PANTHER'] = "http://www.pantherdb.org/";

#Abbreviation: PDB
#database: Protein Data Bank
#object: Identifier
#example_id: PDB:1A4U
#generic_url: http://msd.ebi.ac.uk/
#generic_url: http://www.rcsb.org/pdb/
#url_syntax:
#url_example: http://www.rcsb.org/pdb/cgi/explore.cgi?pid=223051005992697&pdbId=1A4U
#
$dbxref_url['PDB'] = "http://www.rcsb.org/pdb/cgi/explore.cgi?pdbId=";

#Abbreviation: Pfam
#database: Pfam: Protein families database of alignments and HMMs
#object: Accession number
#example_id: Pfam:PF00046
#generic_url: http://www.sanger.ac.uk/Software/Pfam/
#url_syntax: http://www.sanger.ac.uk/cgi-bin/Pfam/getacc?
#url_example: http://www.sanger.ac.uk/cgi-bin/Pfam/getacc?PF00046
$dbxref_url['Pfam'] = "http://www.sanger.ac.uk/cgi-bin/Pfam/getacc?";

#Abbreviation: PfamB
#database: Pfam-B supplement to Pfam
#object: Accession number
#example_id: PfamB:PB014624
#generic_url: http://www.sanger.ac.uk/Software/Pfam/
#???!!
#$dbxref_url['PfamB'] = "url";

#Abbreviation: PharmGKB_PA
#database: The Pharmacogenetics and Pharmacogenomics Knowledge Base
#example: PA267
#generic_url: http://www.pharmgkb.org
#url_syntax: http://www.pharmgkb.org/do/serve?objId=
#url_example: http://www.pharmgkb.org/do/serve?objId=PA267
$dbxref_url['PharmGKB_PA'] = "http://www.pharmgkb.org/do/serve?objId=";

#Abbreviation: PharmGKB_PGKB
#database: The Pharmacogenetics and Pharmacogenomics Knowledge Base
#example: PA267
#generic_url: http://www.pharmgkb.org
#url_syntax: http://www.pharmgkb.org/do/serve?objId=
#url_example: http://www.pharmgkb.org/do/serve?objId=PA267
$dbxref_url['Synonym'] = "url";

#Abbreviation: PIR
#database: Protein Information Resource
#object: Accession number
#example_id: PIR:I49499
#generic_url: http://pir.georgetown.edu/
#url_syntax: http://pir.georgetown.edu/cgi-bin/pirwww/nbrfget?uid=
#url_example: http://pir.georgetown.edu/cgi-bin/pirwww/nbrfget?uid=I49499
#
$dbxref_url['PharmGKB_PGKB'] = "http://pir.georgetown.edu/cgi-bin/pirwww/nbrfget?uid=";

#Abbreviation: PINC
#database: Proteome Inc.; represents GO annotations created in 2001 for NCBI and extracted into GOA from EntrezGene
#generic_url: http://www.proteome.com/
$dbxref_url['PINC'] = "http://www.proteome.com/?";

#Abbreviation: PIRSF
#database: PIR Superfamily Classification System
#object: Identifier
#example_id: PIRSF:SF002327
#generic_url: http://pir.georgetown.edu/pirsf/
#url_syntax: http://pir.georgetown.edu/cgi-bin/ipcSF?id=
#url_example: http://pir.georgetown.edu/cgi-bin/ipcSF?id=SF002327
$dbxref_url['PIRSF'] = "http://pir.georgetown.edu/cgi-bin/ipcSF?id=";

#Abbreviation: PMID
#database: PubMed
#object: Identifier
#synonym: PUBMED
#example_id: PMID:4208797
#generic_url: http://www.ncbi.nlm.nih.gov/PubMed/
#url_syntax: http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=PubMed&dopt=Abstract&list_uids=
#url_example: http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=PubMed&dopt=Abstract&list_uids=4208797
#
$dbxref_url['PMID'] = "http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=PubMed&dopt=Abstract&list_uids=";
$dbxref_url['PUBMED'] = "http://www.ncbi.nlm.nih.gov:80/entrez/query.fcgi?cmd=Retrieve&db=PubMed&dopt=Abstract&list_uids=";

#Abbreviation: PO
#database: Plant Ontology Consortium Database
#object: Identifier
#example_id: PO:0009004
#generic_url: http://www.plantontology.org/
#url_syntax: http://www.plantontology.org/amigo/go.cgi?action=query&view=query&search_constraint=terms&query=
#url_example: http://www.plantontology.org/amigo/go.cgi?action=query&view=query&search_constraint=terms&query=PO:0009004
#
$dbxref_url['PO'] = "http://www.plantontology.org/amigo/go.cgi?action=query&view=query&search_constraint=terms&query=";

#Abbreviation: POC
#database: Plant Ontology Consortium 
#$dbxref_url['Synonym'] = "url";

#Abbreviation: Pompep
#database: Schizosaccharomyces pombe protein data
#object: Gene/protein identifier
#example_id: Pompep:SPAC890.04C
#generic_url: ftp://ftp.sanger.ac.uk/pub/yeast/pombe/Protein_data/
$dbxref_url['Pompep'] = "ftp://ftp.sanger.ac.uk/pub/yeast/pombe/Protein_data/?";

#Abbreviation: PPI
#database: The Pseudomonas syringae community annotation project
#object:
#example_id:
#generic_url: http://genome.pseudomonas-syringae.org/
$dbxref_url['PPI'] = "http://genome.pseudomonas-syringae.org/?";

#Abbreviation: PRINTS
#database: PRINTS compendium of protein fingerprints
#object: Accession
#example_id: PRINTS:PR00025
#generic_url: http://umber.sbs.man.ac.uk/dbbrowser/PRINTS/
#url_syntax: http://umber.sbs.man.ac.uk/cgi-bin/dbbrowser/PRINTS/DoPRINTS.pl?cmd_a=Display&qua_a=none&fun_a=Text&qst_a=
#url_example: http://umber.sbs.man.ac.uk/cgi-bin/dbbrowser/PRINTS/DoPRINTS.pl?cmd_a=Display&qua_a=none&fun_a=Text&qst_a=PR00025
$dbxref_url['PRINTS'] = "http://umber.sbs.man.ac.uk/cgi-bin/dbbrowser/PRINTS/DoPRINTS.pl?cmd_a=Display&qua_a=none&fun_a=Text&qst_a=";

#Abbreviation: ProDom
#database: ProDom protein domain families automatically generated from Swiss-Prot and TrEMBL
#object: Accession
#example_id: ProDom:PD000001
#generic_url: http://prodes.toulouse.inra.fr/prodom/current/html/home.php
#url_syntax: http://prodes.toulouse.inra.fr/prodom/current/cgi-bin/request.pl?question=DBEN&query=
#url_example: http://prodes.toulouse.inra.fr/prodom/current/cgi-bin/request.pl?question=DBEN&query=PD000001
$dbxref_url['ProDom'] = "http://prodes.toulouse.inra.fr/prodom/current/cgi-bin/request.pl?question=DBEN&query=";

#Abbreviation: Prosite
#database: Prosite. Database of protein families and domains
#object: Accession number
#example_id: Prosite:PS00365
#generic_url: http://www.expasy.ch/prosite/
#url_syntax: http://www.expasy.ch/cgi-bin/prosite-search-ac?
#url_example: http://www.expasy.ch/cgi-bin/prosite-search-ac?PS00365
$dbxref_url['Prosite'] = "http://www.expasy.ch/cgi-bin/prosite-search-ac?";

#Abbreviation: protein_id
#database: The protein identifier shared by DDBJ/EMBL-bank/GenBank nucleotide
#sequence databases
#object: Identifier
#example_id: protein_id:CAA71991
#$dbxref_url['protein_id'] = "url";

#Abbreviation: PROW
#database: Protein Reviews on the Web
#generic_url: http://www.ncbi.nlm.nih.gov/prow/
$dbxref_url['PROW'] = "http://www.ncbi.nlm.nih.gov/prow/";

#Abbreviation: PseudoCAP
#database: Pseudomonas Genome Project
#object: Identifier
#example_id: PseudoCAP:PA4756
#generic_url: http://v2.pseudomonas.com/
#url_syntax: http://v2.pseudomonas.com/getAnnotation.do?locusID=
#url_example: http://v2.pseudomonas.com/getAnnotation.do?locusID=PA4756
$dbxref_url['PseudoCAP'] = "http://v2.pseudomonas.com/getAnnotation.do?locusID=";

#Abbreviation: PSI-MI
#database: Proteomic Standard Initiative for Molecular Interaction
#object: Interaction identifier
#synonym: MI
#example_id: MI:0018
#generic_url: http://psidev.sourceforge.net/mi/xml/doc/user/index.html
$dbxref_url['PSI-MI'] = "http://psidev.sourceforge.net/mi/xml/doc/user/index.html?";
$dbxref_url['MI'] = "http://psidev.sourceforge.net/mi/xml/doc/user/index.html?";

#Abbreviation: PSI-MOD
#database: Proteomics Standards Initiative protein modification ontology
#object: Protein modification identifier
#synonym: MOD
#example_id: MOD:00219
#generic_url: http://psidev.sourceforge.net/mod/
#url_syntax: http://www.ebi.ac.uk/ontology-lookup/?termId=
#url_example: http://www.ebi.ac.uk/ontology-lookup/?termId=MOD:00219
$dbxref_url['PSI-MOD'] = "http://www.ebi.ac.uk/ontology-lookup/?termId=";
$dbxref_url['MOD'] = "http://www.ebi.ac.uk/ontology-lookup/?termId=";

#Abbreviation: PSORT
#database: PSORT protein subcellular localization databases and prediction tools for bacteria
#generic_url: http://www.psort.org/
$dbxref_url['PSORT'] = "http://www.psort.org/?";

#Abbreviation: PubChem_BioAssay
#database: NCBI PubChem database of bioassay records
#object: Identifier
#example: PubChem_BioAssay:177
#generic_url: http://pubchem.ncbi.nlm.nih.gov/
#url_syntax: http://pubchem.ncbi.nlm.nih.gov/assay/assay.cgi?aid=
#url_example: http://pubchem.ncbi.nlm.nih.gov/assay/assay.cgi?aid=177
$dbxref_url['PubChem_BioAssay'] = "http://pubchem.ncbi.nlm.nih.gov/assay/assay.cgi?aid=";

#Abbreviation: PubChem_Compound
#database: NCBI PubChem database of chemical structures
#object: Identifier
#example: PubChem_ Compound:2244
#generic_url: http://pubchem.ncbi.nlm.nih.gov/
#url_syntax: http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?CMD=search&DB=pccompound&term=
#url_example: http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?CMD=search&DB=pccompound&term=2244
$dbxref_url['PubChem_Compound'] = "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?CMD=search&DB=pccompound&term=";

#Abbreviation: PubChem_Substance
#database: NCBI PubChem database of chemical substances
#object: Identifier
#example: PubChem_Substance:4594
#generic_url: http://pubchem.ncbi.nlm.nih.gov/
#url_syntax: http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?CMD=search&DB=pcsubstance&term=
#url_example: http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?CMD=search&DB=pcsubstance&term=4594
$dbxref_url['PubChem_Substance'] = "http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?CMD=search&DB=pcsubstance&term=";

#Abbreviation: Reactome
#database: Reactome human pathway database
#object: Identifier
#example_id: Reactome:70635
#generic_url: http://www.reactome.org/
#url_syntax: http://www.reactome.org/cgi-bin/eventbrowser?DB=gk_current&ID=
#url_example: http://www.reactome.org/cgi-bin/eventbrowser?DB=gk_current&ID=70635
$dbxref_url['Reactome'] = "http://www.reactome.org/cgi-bin/eventbrowser?DB=gk_current&ID=";

#Abbreviation: REBASE
#database: REBASE, The Restriction Enzyme Database
#object: Restriction enzyme name
#example_id: REBASE:EcoRI
#generic_url: http://rebase.neb.com/rebase/rebase.html
#url_syntax: http://rebase.neb.com/rebase/enz/
#url_example: http://rebase.neb.com/rebase/enz/EcoRI.html
$dbxref_url['REBASE'] = "http://rebase.neb.com/rebase/enz/";

#Abbreviation: RESID
#database: RESID Database of Protein Modifications
#object: Identifier
#example_id: RESID:AA0062
#generic_url: ftp://ftp.ncifcrf.gov/pub/users/residues/
$dbxref_url['RESID'] = "ftp://ftp.ncifcrf.gov/pub/users/residues/";

#Abbreviation: RGD
#database: Rat Genome Database
#object: Accession Number
#synonym: RGDID
#example_id: RGD:2340
#generic_url: http://rgd.mcw.edu/
#url_syntax: http://rgd.mcw.edu/tools/genes/genes_view.cgi?id=
#url_example: http://rgd.mcw.edu/tools/genes/genes_view.cgi?id=2340
$dbxref_url['RGD'] = "http://rgd.mcw.edu/tools/genes/genes_view.cgi?id=";
$dbxref_url['RGDID'] = "http://rgd.mcw.edu/tools/genes/genes_view.cgi?id=";

#Abbreviation: RNAmods
#database: The RNA Modification Database
#object: Identifier
#example_id: RNAmods:037
#generic_url: http://medlib.med.utah.edu/RNAmods/
#url_syntax: http://medlib.med.utah.edu/cgi-bin/rnashow.cgi?
#url_example: http://medlib.med.utah.edu/cgi-bin/rnashow.cgi?037
$dbxref_url['RNAmods'] = "http://medlib.med.utah.edu/cgi-bin/rnashow.cgi?";

#Abbreviation: Sanger
#database: The Wellcome Trust Sanger Institute
#generic_url: http://www.sanger.ac.uk/
#
$dbxref_url['Sanger'] = "http://www.sanger.ac.uk/";

#! note: The SGD is a synonym for the SGDID
#Abbreviation: SGD
#database: Saccharomyces Genome Database
#object: Identifier for SGD Loci
#synonym: SGDID
#example_id: SGD:S000006169
#generic_url: http://www.yeastgenome.org/
#url_syntax: http://db.yeastgenome.org/cgi-bin/locus.pl?dbid=
#url_example: http://db.yeastgenome.org/cgi-bin/locus.pl?dbid=S000006169 
#
$dbxref_url['SGD'] = "http://db.yeastgenome.org/cgi-bin/locus.pl?dbid=";
$dbxref_url['SGDID'] = "http://db.yeastgenome.org/cgi-bin/locus.pl?dbid=";

#! note: The SGD_LOCUS object also includes the systematic S. cerevisiae ORF names, eg. YEL001C
#Abbreviation: SGD_LOCUS
#database: Saccharomyces Genome Database
#object: Gene name (gene symbol in mammalian nomenclature)
#example_id: SGD_LOCUS:GAL4 or SGD_LOCUS:YEL001C
#generic_url: http://www.yeastgenome.org/
#url_syntax: http://db.yeastgenome.org/cgi-bin/locus.pl?locus=
#url_example: http://db.yeastgenome.org/cgi-bin/locus.pl?locus=GAL4
#url_example: http://db.yeastgenome.org/cgi-bin/locus.pl?locus=YEL001C
$dbxref_url['SGD_LOCUS'] = "http://db.yeastgenome.org/cgi-bin/locus.pl?locus=";

#Abbreviation: SGD_REF
#database: Saccharomyces Genome Database
#object: Literature Reference Identifier
#example_id: SGD_REF:S000049602
#generic_url: http://www.yeastgenome.org/
#url_syntax: http://db.yeastgenome.org/cgi-bin/reference/reference.pl?dbid=
#url_example: http://db.yeastgenome.org/cgi-bin/reference/reference.pl?dbid=S000049602
$dbxref_url['SGD_REF'] = "http://db.yeastgenome.org/cgi-bin/reference/reference.pl?dbid=";

#Abbreviation: SMART
#database: Simple Modular Architecture Research Tool
#object: Accession
#example_id: SMART:SM00005
#generic_url: http://smart.embl-heidelberg.de/
#url_syntax: http://smart.embl-heidelberg.de/smart/do_annotation.pl?BLAST=DUMMY&DOMAIN=
#url_example: http://smart.embl-heidelberg.de/smart/do_annotation.pl?BLAST=DUMMY&DOMAIN=SM00005
$dbxref_url['SMART'] = "http://smart.embl-heidelberg.de/smart/do_annotation.pl?BLAST=DUMMY&DOMAIN=";

#Abbreviation: SMD
#database: Stanford Microarray Database
#generic_url: http://genome-www.stanford.edu/microarray
$dbxref_url['SMD'] = "http://genome-www.stanford.edu/microarray";

#Abbreviation: SO
#database: Sequence Ontology
#object: Identifier
#example_id: SO:0000195
#generic_url: http://song.sourceforge.net/
#url_syntax: http://song.sourceforge.net/SOterm_tables.html#
#url_example: http://song.sourceforge.net/SOterm_tables.html#SO:0000195
$dbxref_url['SO'] = "http://song.sourceforge.net/SOterm_tables.html#";

#Abbreviation: SP_KW
#database: UniProt Knowledgebase keywords
#object: Identifier
#example_id: SP_KW:KW-0812
#generic_url: http://www.expasy.org/cgi-bin/keywlist.pl
#url_syntax: http://www.expasy.org/cgi-bin/get-entries?
#url_example: http://www.expasy.org/cgi-bin/get-entries?KW=KW-0812
$dbxref_url['SP_KW'] = "http://www.expasy.org/cgi-bin/get-entries?";

#Abbreviation: SUBTILIST
#database: Bacillus subtilis Genome Sequence Project
#object: Accession number
#example_id: SUBTILISTG:BG11384
#generic_url: http://genolist.pasteur.fr/SubtiList/
$dbxref_url['SUBTILIST'] = "http://genolist.pasteur.fr/SubtiList/";

#Abbreviation: SUBTILISTG
#database: Bacillus subtilis Genome Sequence Project
#object: Gene symbol
#example_id: SUBTILISTG:accC
#generic_url: http://genolist.pasteur.fr/SubtiList/
#url_syntax:
$dbxref_url['SUBTILISTG'] = "http://genolist.pasteur.fr/SubtiList/";

#Abbreviation: Swiss-Prot
#database: UniProtKB/Swiss-Prot, a curated protein sequence database which provides a high level of annotation and a minimal level of redundancy
#object: Accession number
#example_id: UniProt:P51587
#generic_url: http://www.uniprot.org
#url_syntax: http://www.ebi.uniprot.org/entry/
#url_example: http://www.ebi.uniprot.org/entry/P51587
$dbxref_url['Swiss-Prot'] = "http://www.ebi.uniprot.org/entry/";

#Abbreviation: TAIR
#database: The Arabidopsis Information Resource
#object: Accession number
#example_id: TAIR:gene:2062713
#generic_url: http://www.arabidopsis.org/
#url_syntax: http://www.arabidopsis.org/servlets/TairObject?accession=
#url_example: http://www.arabidopsis.org/servlets/TairObject?accession=gene:2062713
#
$dbxref_url['TAIR'] = "http://www.arabidopsis.org/servlets/TairObject?accession=gene:";
$dbxref_url['TAIR:Publication'] = "http://www.arabidopsis.org/servlets/TairObject?type=publication&id=";
$dbxref_url['TAIR:AnalysisReference'] = "http://www.arabidopsis.org/servlets/TairObject?type=analysis_reference&id=";
$dbxref_url['TAIR:Communication'] = "http://www.arabidopsis.org/servlets/TairObject?type=communication&id=";

#Abbreviation: taxon
#database: NCBI Taxman
#object: Identifier
#example_id: taxon:7227
#generic_url: http://www.ncbi.nlm.nih.gov/Taxonomy/taxonomyhome.html/
#url_syntax:
$dbxref_url['taxon'] = "http://www.ncbi.nlm.nih.gov/Taxonomy/taxonomyhome.html/?";

#Abbreviation: TC
#database: The Transport Protein Database
#object: Identifier
#example_id: TC:9.A.4.1.1
#generic_url: http://tcdb.ucsd.edu/tcdb/
#url_syntax: http://tcdb.ucsd.edu/tcdb/tcprotein.php?substrate=
#url_example: http://tcdb.ucsd.edu/tcdb/tcprotein.php?substrate=9.A.4.1.1
$dbxref_url['TC'] = "http://tcdb.ucsd.edu/tcdb/tcprotein.php?substrate=";

#Abbreviation: TGD
#database: Tetrahymena Genome Database
#generic_url: http://www.ciliate.org/
#
#! note: The TGD_LOCUS object also includes the systematic T. thermophila ORF names, eg. U66363
$dbxref_url['TGD'] = "http://www.ciliate.org/";

#Abbreviation: TGD_LOCUS
#database: Tetrahymena Genome Database
#object: Gene name (gene symbol in mammalian nomenclature)
#example_id: TGD_LOCUS:PDD1 or TGD_LOCUS:U66363
#generic_url: http://www.ciliate.org/
#url_syntax: http://db.ciliate.org/cgi-bin/locus.pl?locus=
#url_example: http://db.ciliate.org/cgi-bin/locus.pl?locus=PDD1
#url_example: http://db.ciliate.org/cgi-bin/locus.pl?locus=U66363
$dbxref_url['TGD_LOCUS'] = "http://db.ciliate.org/cgi-bin/locus.pl?locus=";

#Abbreviation: TGD_REF
#database: Tetrahymena Genome Database
#object: Literature Reference Identifier
#example_id: TGD_REF:T000005818
#generic_url: http://www.ciliate.org/
#url_syntax: http://db.ciliate.org/cgi-bin/reference/reference.pl?dbid=
#url_example: http://db.ciliate.org/cgi-bin/reference/reference.pl?dbid=T000005818
$dbxref_url['TGD_REF'] = "http://db.ciliate.org/cgi-bin/reference/reference.pl?dbid=";

#Abbreviation: TIGR
#database: The Institute for Genomic Research
#generic_url: http://www.tigr.org/
$dbxref_url['TIGR'] = "http://www.tigr.org/";

#Abbreviation: TIGR_Ath1
#database: The Institute for Genomic Research, Arabidopsis thaliana database
#object: Accession
#example_id: TIGR_Ath1:At3g01440
#generic_url: http://www.tigr.org/tdb/e2k1/ath1/ath1.shtml
#url_syntax: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=ath1&orf=
#url_example: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=ath1&orf=At3g01440
$dbxref_url['TIGR_Ath1'] = "http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=ath1&orf=";

#Abbreviation: TIGR_CMR
#database: The Institute for Genomic Research, Comprehensive Microbial Resource
#object: Locus
#example_id: TIGR_CMR:VCA0557
#generic_url: http://www.tigr.org/
#url_syntax: http://www.tigr.org/tigr-scripts/CMR2/GenePage.spl?locus=
#url_example: http://www.tigr.org/tigr-scripts/CMR2/GenePage.spl?locus=VCA0557
$dbxref_url['TIGR_CMR'] = "http://www.tigr.org/tigr-scripts/CMR2/GenePage.spl?locus=";

#Abbreviation: TIGR_EGAD
#database: The Institute for Genomic Research, EGAD database
#object: Accession
#example_id: TIGR_EGAD:74462
#generic_url: http://www.tigr.org/
#url_syntax: http://www.tigr.org/tigr-scripts/CMR2/ht_report.spl?prot_id=
#url_example: http://www.tigr.org/tigr-scripts/CMR2/ht_report.spl?prot_id=74462
$dbxref_url['TIGR_EGAD'] = "http://www.tigr.org/tigr-scripts/CMR2/ht_report.spl?prot_id=";

#Abbreviation: TIGR_GenProp
#database: The Institute for Genomic Research, Genome Properties
#object: Accession
#example_id: TIGR_GenProp:GenProp0120
#generic_url: http://www.tigr.org/
#url_syntax: http://www.tigr.org/tigr-scripts/CMR2/genome_property_def.spl?prop_acc=
#url_example: http://www.tigr.org/tigr-scripts/CMR2/genome_property_def.spl?prop_acc=GenProp0120
$dbxref_url['TIGR_GenProp'] = "http://www.tigr.org/tigr-scripts/CMR2/genome_property_def.spl?prop_acc=";

#Abbreviation: TIGR_Pfa1
#database: The Institute for Genomic Research, Plasmodium falciparum database
#object: Accession
#example_id: TIGR_Pfa1:PFB0010w 
#generic_url: http://www.tigr.org/tdb/e2k1/pfa1/pfa1.shtml
#url_syntax: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=pfa1&orf=
#url_example: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=pfa1&orf=PFB0010w
$dbxref_url['TIGR_Pfa1'] = "http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=pfa1&orf=";

#Abbreviation: TIGR_REF
#database: The Institute for Genomic Research
#object: reference locator
#example_id: TIGR_REF:GO_ref
#generic_url: http://www.tigr.org/tdb/GO_REF/GO_REF.shtml
#url_example: http://www.tigr.org/tdb/GO_REF/GO_REF.shtml
#URL needs further processing
$dbxref_url['TIGR_REF'] = "http://www.tigr.org/tdb/GO_REF/";

#Abbreviation: TIGR_Tba1
#database: The Institute for Genomic Research, Trypanosoma brucei database
#object: Accession
#example_id: TIGR_Tba1:25N14.10
#generic_url: http://www.tigr.org/tdb/e2k1/tba1/
#url_syntax: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=tba1&orf=
#url_example: http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=tba1&orf=25N14.10
$dbxref_url['TIGR_Tba1'] = "http://www.tigr.org/tigr-scripts/euk_manatee/shared/ORF_infopage.cgi?db=tba1&orf=";

#Abbreviation: TIGR_TGI
#database: The Institute for Genomic Research, TIGR Gene Index
#object: TC accession
#example_id: TIGR_TGI:Cattle_TC123931
#generic_url: http://www.tigr.org/
#url_syntax: http://www.tigr.org/tigr-scripts/nhgi_scripts/tc_report.pl?tc=?
#url_example: http://www.tigr.org/tigr-scripts/nhgi_scripts/tc_report.pl?tc=Cattle_TC123931
$dbxref_url['TIGR_TGI'] = "http://www.tigr.org/tigr-scripts/nhgi_scripts/tc_report.pl?tc=";

#Abbreviation: TIGR_TIGRFAMS
#database: The Institute for Genomic Research, TIGRFAMs HMM collection
#object: Accession
#example_id: TIGR_TIGRFAMS:TIGR00254
#generic_url: http://www.tigr.org/
#url_syntax: http://www.tigr.org/tigr-scripts/CMR2/hmm_report.spl?acc=
#url_example: http://www.tigr.org/tigr-scripts/CMR2/hmm_report.spl?acc=TIGR00254
$dbxref_url['TIGR_TIGRFAMS'] = "http://www.tigr.org/tigr-scripts/CMR2/hmm_report.spl?acc=";

#Abbreviation: TRAIT
#database: TRAnscript Integrated Table, an integrated database of transcripts expressed in human skeletal muscle
#synonym: Muscle TRAIT
#generic_url: http://muscle.cribi.unipd.it/
$dbxref_url['TRAIT'] = "http://muscle.cribi.unipd.it/?";

#Abbreviation: TRANSFAC
#database: TRANSFAC database of eukaryotic transcription factors
#generic_url: http://www.gene-regulation.com/pub/databases.html#transfac
$dbxref_url['TRANSFAC'] = "http://www.gene-regulation.com/pub/databases.html#transfac";

#Abbreviation: TrEMBL
#database: UniProtKB-TrEMBL, a computer-annotated protein sequence database supplementing UniProtKB and containing the translations of all coding sequences (CDS) present in the EMBL Nucleotide Sequence Database but not yet integrated in UniProtKB/Swiss-Prot
#object: Accession number
#example_id: TrEMBL:O31124
#generic_url: http://www.uniprot.org
#url_syntax: http://www.ebi.uniprot.org/entry/
#url_example: http://www.ebi.uniprot.org/entry/O31124
$dbxref_url['TrEMBL'] = "http://www.ebi.uniprot.org/entry/";

#Abbreviation: UM-BBD
#database: The University of Minnesota Biocatalysis/Biodegradation Database
#object: Prefix
#generic_url: http://umbbd.ahc.umn.edu/index.html
$dbxref_url['UM-BBD'] = "http://umbbd.ahc.umn.edu/index.html";

#Abbreviation: UM-BBD_enzymeID
#database: The University of Minnesota Biocatalysis/Biodegradation Database
#object: Enzyme identifier
#example_id: UM-BBD_enzymeID:e0413
#generic_url: http://umbbd.ahc.umn.edu/index.html
#url_syntax: http://umbbd.ahc.umn.edu:8015/umbbd/servlet/pageservlet?ptype=ep&enzymeID=
#url_example: http://umbbd.ahc.umn.edu:8015/umbbd/servlet/pageservlet?ptype=ep&enzymeID=e0413
$dbxref_url['UM-BBD_enzymeID'] = "http://umbbd.ahc.umn.edu:8015/umbbd/servlet/pageservlet?ptype=ep&enzymeID=";

#Abbreviation: UM-BBD_pathwayID
#database: The University of Minnesota Biocatalysis/Biodegradation Database
#object: Pathway identifier
#example_id: UM-BBD_pathwayID:acr
#generic_url: http://umbbd.ahc.umn.edu/index.html
#url_example: http://umbbd.ahc.umn.edu/acr/acr_map.html
#
$dbxref_url['UM-BBD_pathwayID'] = "http://umbbd.ahc.umn.edu/index.html";

#! note: UniParc supersedes REMTREMBL; the latter is no longer maintained
#Abbreviation: UniParc
#database: UniProt Archive; a non-redundant archive of protein sequences extracted from Swiss-Prot, TrEMBL, PIR-PSD, EMBL, Ensembl, IPI, PDB, RefSeq, FlyBase, WormBase, European Patent Office, United States Patent and Trademark Office, and Japanese Patent Office
#object: Accession number
#example_id: UniParc:UPI000000000A
#generic_url: http://www.ebi.ac.uk/uniparc/
#url_syntax: http://www.ebi.ac.uk/cgi-bin/dbfetch?db=uniparc&id=
#url_example: http://www.ebi.ac.uk/cgi-bin/dbfetch?db=uniparc&id=UPI000000000A
$dbxref_url['UniParc'] = "http://www.ebi.ac.uk/cgi-bin/dbfetch?db=uniparc&id=";

#Abbreviation: UniProtKB
#database: The Universal Protein Knowledgebase, a central repository of protein sequence and function created by joining the information contained in Swiss-Prot, TrEMBL, and PIR
#object: Accession number
#synonym: UniProt
#example_id: UniProt:P51587
#generic_url: http://www.uniprot.org
#url_syntax: http://www.ebi.uniprot.org/entry/
#url_example: http://www.ebi.uniprot.org/entry/P51587
#
#! note: The link URL for VEGA is species specific (much like Ensembl).
#! For a direct link you need to translate the abbreviation in the ID
#! (eg 'HUM') into 'Homo_sapiens':
#! 
#! http://vega.sanger.ac.uk/Homo_sapiens/protview?peptide=OTTHUMP00000000661&db=core
#! 
#! Therefore to make it easier D. Barrell has given the search URL which
#! gives the results of a search on a peptide ID.
$dbxref_url['UniProtKB'] = "http://www.ebi.uniprot.org/entry/";
$dbxref_url['UniProt'] = "http://www.ebi.uniprot.org/entry/";

#Abbreviation: VEGA
#database: The Vertebrate Genome Annotation database
#object: Identifier
#example_id: VEGA:OTTHUMP00000000661
#generic_url: http://vega.sanger.ac.uk/index.html
#url_syntax: http://vega.sanger.ac.uk/perl/searchview?species=all&idx=All&q=
#url_example: http://vega.sanger.ac.uk/perl/searchview?species=all&idx=All&q=OTTHUMP00000000661
$dbxref_url['VEGA'] = "http://vega.sanger.ac.uk/perl/searchview?species=all&idx=All&q=";

#Abbreviation: VIDA
#database: Virus Database at University College London
#generic_url: http://www.biochem.ucl.ac.uk/bsm/virus_database/VIDA.html
$dbxref_url['VIDA'] = "http://www.biochem.ucl.ac.uk/bsm/virus_database/VIDA.html";

#Abbreviation: WB
#database: WormBase, database of nematode biology
#object: Gene Identifier
#synonym: WormBase
#example_id: WB:WBGene00003001
#generic_url: http://www.wormbase.org/
#url_syntax: http://www.wormbase.org/db/gene/gene?name=
#url_example: http://www.wormbase.org/db/gene/gene?name=WBGene00003001
$dbxref_url['WB'] = "http://www.wormbase.org/db/gene/gene?name=";
$dbxref_url['WormBase'] = "http://www.wormbase.org/db/gene/gene?name=";

#Abbreviation: WB_GENE
#database: WormBase, database of nematode biology
#object: Gene symbol
#synonym: WormBase
#example_id: WB:lin-12
#generic_url: http://www.wormbase.org/
#url_syntax: http://www.wormbase.org/db/gene/gene?name=
#url_example: http://www.wormbase.org/db/gene/gene?name=lin-12
$dbxref_url['WB_GENE'] = "http://www.wormbase.org/db/gene/gene?name=";

#Abbreviation: WB_REF
#database: WormBase, database of nematode biology
#object: Literature Reference Identifier
#example_id: WB_REF:WBPaper00004823
#generic_url: http://www.wormbase.org/
#url_syntax: http://www.wormbase.org/db/misc/paper?name=
#url_example: http://www.wormbase.org/db/misc/paper?name=WBPaper00004823
$dbxref_url['WB_REF'] = "http://www.wormbase.org/db/misc/paper?name=";

#Abbreviation: WP
#database: Wormpep, database of proteins of C. elegans
#object: Identifier
#synonym: Wormpep
#example_id: WP:CE25104
#generic_url: http://www.wormbase.org/
#url_syntax: http://www.wormbase.org/db/get?class=Protein;name=
#url_example: http://www.wormbase.org/db/get?class=Protein;name=WP%3ACE15104
$dbxref_url['WP'] = "http://www.wormbase.org/db/get?class=Protein;name=";
$dbxref_url['Wormpep'] = "http://www.wormbase.org/db/get?class=Protein;name=";

#Abbreviation: ZFIN
#database: The Zebrafish Information Network
#object: Accession ID
#example_id: ZFIN:ZDB-GENE-990415-103
#generic_url: http://zfin.org/
#url_syntax: http://zfin.org/cgi-bin/ZFIN_jump?record=
#url_example: http://zfin.org/cgi-bin/ZFIN_jump?record=ZDB-GENE-990415-103
#
$dbxref_url['ZFIN'] = "http://zfin.org/cgi-bin/ZFIN_jump?record=";

?>