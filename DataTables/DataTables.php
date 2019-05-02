<?php
/*
JQuery DataTables extension. Originally written by Daniel Renfro in 2012
Updated to DataTables 1.10.8 by Jim Hu in August 2015
*/
// ============ check if we're inside MediaWiki =======================
if ( !defined('MEDIAWIKI') ) {
    echo <<<EOT
To install this extension, you'll need to edit your LocalSettings.php with
the appropriate configuration directives. Please see the README that comes
with the software, or contact your system administrator with this message.
EOT;
    exit( 1 );
}



// ============= default global variables, constants, etc. =====================
define( 'MW_DATATABLES_VERSION', '0.2.0' );
define( 'DATATABLES_VERSION', '1.10.8' );

// ============ credits =================================
$wgExtensionCredits['other'][] = array(
	'name' 			=> 'DataTables',
	'description' 	=> 'Datatables jQuery library for MediaWiki',
	'author' 		=> array(
		'[mailto:bluecurio@gmail.com Daniel Renfro] (Mediawiki Extension)',
		'[mailto:jimhu@tamu.edu Jim Hu]',
		'Allan Jardine (Javascript library)'
	),
	'version' 		=> MW_DATATABLES_VERSION
);


// ============ hooks =================================
$wgHooks['ResourceLoaderRegisterModules'][] = array( new DataTables, 'register' );
#$wgHooks['ResourceLoaderRegisterModules'][] =  "DataTables::register";

#$wgHooks['BeforePageDisplay'][] = 'Datatables::init';


// ============ Global Functions ============================================

class DataTables {
	/*
	When updating this extension, use a global search and replace for the version number. PHP does not like using the constant DATATABLES_VERSION in a catenation for the array values.
	*/
	static $modules = array(
	
		'ext.datatables' => array(
			'scripts' => array(
			#	'DataTables-1.10.8/media/js/jquery.dataTables.js'
				'DataTables/DataTables-1.10.13/js/jquery.dataTables.js'
				),
			'styles' => array(
			#    'DataTables-1.10.8/media/css/jquery.dataTables.css'
			    'DataTables/DataTables-1.10.13/css/jquery.dataTables.css'
			   // 'media/css/demo_table_jui.css'
				),
			'messages' => array()
		),
		
		'ext.datatables.autofill' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/AutoFill/js/dataTables.autoFill.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/AutoFill/css/autoFill.dataTables.css'
			),
			'messages' => array()		
		),

		'ext.datatables.buttons' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/Buttons/js/dataTables.buttons.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/Buttons/css/buttons.dataTables.css'
			),
			'messages' => array()		
		),
		
		'ext.datatables.colreorder' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/ColReorder/js/datatables.colReorder.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/ColReorder/css/colReorder.dataTables.css'
			),
			'messages' => array()		
		),
				
		'ext.datatables.fixedcolumns' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/FixedColumns/js/dataTables.fixedColumns.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/FixedColumns/css/fixedColumns.dataTables.css'
			),
			'messages' => array()		
		),
		
		'ext.datatables.fixedheader' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/FixedHeader/js/dataTables.fixedHeader.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/FixedHeader/css/fixedHeader.dataTables.css'
			),
			'messages' => array()		
		),
		
		'ext.datatables.keytable' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/KeyTable/js/dataTables.keyTable.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/KeyTable/css/keyTable.dataTables.css'
			),
			'messages' => array()		
		),
		
		'ext.datatables.responsive' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/Responsive/js/dataTables.responsive.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/Responsive/css/responsive.dataTables.css'
			),
			'messages' => array()		
		),
		
		'ext.datatables.rowreorder' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/RowReorder/js/dataTables.rowReorder.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/RowReorder/css/rowReorder.dataTables.css'
			),
			'messages' => array()		
		),
		
		'ext.datatables.scroller' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/Scroller/js/datatables.scroller.js'
			),
			'styles' => array(
				'DataTables-1.10.8/extensions/Scroller/css/scroller.dataTables.css'
			),
			'messages' => array()		
		),

		'ext.datatables.select' => array(
			'scripts' => array(
				'DataTables-1.10.8/extensions/Select/js/datatables.select.js'
			),
			'styles' => array(
			    'DataTables-1.10.8/extensions/Select/css/select.dataTables.css'
			),
			'messages' => array()		
		),
		
		# deprecated but available for backward compatibility
		# newer projects should use Buttons
		'ext.datatables.colvis' => array(
			'scripts' => array(
				'extras/ColVis/media/js/ColVis.js'
			),
			'styles' => array(
			    'extras/ColVis/media/css/ColVis.css'
			),
			'messages' => array()		
		),
		# deprecated but available for backward compatibility
		# newer projects should use Buttons and Select
		'ext.datatables.tabletools' => array(
			'scripts' => array(
				'extras/TableTools/media/js/TableTools.js'
			),
			'styles' => array(
			    'extras/TableTools/media/css/TableTools.css',
			    'extras/TableTools/media/css/TableTools_JUI.css'
			),
			'messages' => array()		
		)
	);

	public static function init(OutputPage $out, Skin $skin){
		global $wgExtensionAssetsPath;
		foreach ( self::$modules as $name => $resources ) {
			$out->addModules($name);
		}
		global $wgResourceModules;
		#trigger_error(print_r($wgResourceModules, true));
		return true;
	}


	public static function register( $resourceLoader ) {
		global $wgExtensionAssetsPath, $wgResourceModules;
		$localpath = dirname( __FILE__ );
		$remotepath = "$wgExtensionAssetsPath/DataTables";
		foreach ( self::$modules as $name => $resources ) {
			#if (!$resourceLoader->isModuleRegistered($name) ){
			#	$resourceLoader->register( 
			#		$name, new ResourceLoaderFileModule( $resources, $localpath, $remotepath )
			#		);
			$resources['localBasePath'] = __DIR__;
			$resources['remoteExtPath'] = 'DataTables';
 			$wgResourceModules[$name] =	$resources;
			#}
		#	trigger_error(__METHOD__." $name ".print_r($wgResourceModules[$name],true));
		}
		
		return true;
	}
}
