/*
 * File: init_datatables.js
 * Version: 0.0.4
 * Author: Daniel Renfro
 * Info: ecoliwiki.net
 *
 * Copyright 2009-2010 Daniel Renfro, all rights reserved.
 */ 
 
 /* global $ */
 
// define some variables
var view = 'tableEdit_view';
var GO_table;
var GO_template = 'GO_table_product';
var GONUTS_GO_template = 'Annotation_Headings';

// add a class to any rows that have IEA in the correct column
IEA_regex = /<p>IEA/;
var color_IEAs = function( nRow, aData, iDisplayIndex ) {
	if ( IEA_regex.test(aData[GO_evidence_column]) ) {    
		// highlight things that match the regex
		$(nRow).addClass( 'tableEdit_IEA' );
		// remove the other highlighting
		if ($(nRow).hasClass('odd')) { $(nRow).removeClass('odd'); }
		if ($(nRow).hasClass('even')) { $(nRow).removeClass('even'); }
	}
	return nRow;
};

// move the edit link into the table footer thing & remove the tfoot
var move_edit_link = function( table ) {
	var e = $(table).find(".tableEdit_editLink");
	e.attr('class', 'plainlinks').css('padding-left','20px');
	e.prependTo( $(table).closest('.dataTables_wrapper').find(".bottom") );
	
	$(table).find('tfoot').remove();
}

// set the base options for all tables
var base_options = {
	'bFilter': false,
	'bInfo': false,
	'bSort': true,
	'bPaginate': false,
	'oLanguage': {
		'sSearch': 'Filter Rows:',
		'sZeroRecords': 'No Rows in Table',
		'sInfoEmpty': 'No entries to show',
		
	},
};

// do stuff once the document is ready
$(document).ready( function() {
	
	$('.dataTable').each(function () {
		var table = this;

		/* --------------- GO/Annotation tables ------------------------------------- */
		
		// set the evidence column for this particular template
		var GO_evidence_column 		= 4;
		if ($(table).hasClass('Joint_annotation')) { GO_evidence_column = 7; }
		
		
		if ( $(table).hasClass(GO_template) 
			|| $(table).hasClass(GONUTS_GO_template) 
			|| $(table).hasClass('Joint_annotation') 
			|| $(table).hasClass('annotation_stats')
		) {
			
			var table_options;
			if ( !$(table).hasClass('annotation_stats') ) {
				table_options = $.extend(
					{},					// empty obj to fill
					base_options,		// start with this
					{
						'bAutoWidth': true,
						'bFilter': true,
						'bInfo': true,
						'bProcessing': false,
						'bSort': true,
						'sDom': '<"top"firp>t<"bottom"p><"clear">',
						'bPaginate': false,
						'bLengthChange': false,				// requires bPaginate
						'oLanguage': {
							'sSearch': 'Filter Rows:',
						},
						//'fnRowCallback': color_IEAs,
					}
				);
			}
			else {
				table_options = $.extend(
					{},					// empty obj to fill
					base_options,		// start with this
					{
						'bAutoWidth': false,
						'bFilter': true,
						'bInfo': true,
						'bSort': true,
						'bPaginate': true,
						'sPaginationType': 'full_numbers',
						'bProcessing': true,
						'bLengthChange': true,				// requires bPaginate
						'sDom': '<"top"iprf>t<"bottom"lp><"clear">',
						'oLanguage': {
							'sSearch': 'Filter Rows:',
							'sZeroRecords': 'No Rows in Table',
							'sInfoEmpty': 'No entries to show',
						},
					}
				);
				GO_evidence_column 	= 6;
			}
			// make this table into a dataTable with the right options
			var dT = $(table).dataTable( table_options );
			
	
			
			// if it's in view mode, add one
			if ($(table).hasClass(view)) GO_evidence_column++;
			
			// create a dropdown of Evidence types
			var evidence_types = [ ];
			$(table).find('tbody tr').each(function() {
				var columns = $(this).children('td');
				var text = $(columns[GO_evidence_column]).text().replace(/(\n|\r)/g, '').split(':')[0];
				var blankRegex = /^\s*$/;
				if ($.inArray(text, evidence_types) == -1 && !blankRegex.test(text)) {
					evidence_types.push( text );
				}
			})
			evidence_types.sort();
			var dropdown_html = '<label style="padding-left:20px">Evidence: <select id="evidence_dropdown">';
			dropdown_html += '<option value="all">Any/All</option>';
			dropdown_html += '<option value="not_IEAs">not IEAs</option>';
			dropdown_html += '<option value="no_evidence">None</option>';
			for (var i in evidence_types) {
				dropdown_html += '<option value="' + evidence_types[i] + ':">' + evidence_types[i] + '</option>';
				evidence_types[i] += ':'
			}
			dropdown_html += '</select></label>';
			$(dropdown_html).appendTo(	$(table).closest('.dataTables_wrapper').find(".dataTables_filter") );
			
			// logic for the dropdown callback
			$('select#evidence_dropdown').change( function() {
				var val = $('select#evidence_dropdown').attr('value');
				var regex;
				if (val == 'all') {
					regex = "";      // filter nothing, return everything
				}
				else if (val == 'no_evidence') {
					regex = "^\\s*$";		// return empty or just whitespace
				}
				else if (val == 'not_IEAs') {
					regex = '^\\s*$';	// build up a list of anything except IEAs
					var only_IEAs = true;
                    console.log( evidence_types );
					for (var i in evidence_types) {
						if (evidence_types[i] != 'IEA') {
							only_IEAs = false;
							break;
						}
					}
					if  (!only_IEAs) { 
						regex = '('+regex+')|'; 
						regex += evidence_types.join('|')
						regex = regex.replace('IEA:|','')
					}
 				}
				else {
					regex = val;	// return just the e_code we want
				}
                console.log( regex );
				regex.replace(/(\n|\r)/g, '');
				dT.fnFilter(regex, GO_evidence_column, true);	
			});		

			/*
			var c = '<label style="padding-left:20px">Hide IEAs: <input type="checkbox" name="hide_ieas" id="GO_table_product_HideIEAs"></label>'; 
			$(c).appendTo(	$(wrapper).find(".dataTables_filter") );
			
			// logic for hiding/unhiding the IEAs
			$('select#GO_table_product_HideIEAs').click( function() {
				($(this).attr('checked'))
					? dT.fnFilter('^(?!<p>IEA)', GO_evidence_column, false)   	// what to do when the button is checked
					: dT.fnFilter("", GO_evidence_column);			  	// what to do when it's unchecked
			});
			*/
			
			move_edit_link( table );
			
		} 
		
		/* --------------- basic searching, etc ---------------------------------------- */		
		else if ( $(table).hasClass('GO_summary') 
		            || $(table).hasClass('Gene_allele_table')
		            || $(table).hasClass('Product_motif_table')
		            || $(table).hasClass('Product_interactions_table')
		            || $(table).hasClass('Expression_dataset_table')
                ) {
			var table_options = $.extend(
				{},					// empty obj to fill
				base_options,		// start with this
				{
					'bAutoWidth': false,
					'bFilter': true,
					'bInfo': true,
					'bProcessing': false,
					'bSort': true,
					'sDom': '<"top"firp>t<"clear">',
					'bPaginate': false,
					'bLengthChange': false,				// requires bPaginate
					'oLanguage': {
						'sSearch': 'Search all columns: ',
					},
				}
			);	
			var dT = $(table).dataTable( table_options );
		}

		/* --------------- Alleles & Phenotypes table ---------------------------------------- */		
		else if ( $(table).hasClass('Gene_allele_table') ) {
			var table_options = $.extend(
				{},					// empty obj to fill
				base_options,		// start with this
				{
					'bAutoWidth': true,
					'bFilter': true,
					'bInfo': true,
					'bProcessing': false,
					'bSort': true,
					'sDom': '<"top"firp>t<"clear">',
					'bPaginate': false,
					'bLengthChange': false,				// requires bPaginate
					'oLanguage': {
						'sSearch': 'Search all columns: ',
					},
				}
			);	
			var dT = $(table).dataTable( table_options );
		}
		
		
		/* --------------- everything else ------------------------------------- */		
		else {
		//	$(table).dataTable( base_options );
		}
		
		/* --------------- table-agnostic code --------------------------------- */
		
	}); 
	
	// make sure that all other tables get some sort of sortable behaviour
	$('table:not(.dataTable):has(thead)').each(function () {
//		if ( $(this).hasClass('sortable') ) { $(this).removeClass('sortable'); }
	//	$(this).dataTable( base_options );
	});
}); // end $(document).ready()
