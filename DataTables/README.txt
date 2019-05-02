DataTables mediawiki extension notes

The DataTables MW extension was originally written as a wrapper around the DataTables JQuery plugin library by Daniel Renfro

Starting with version 0.2, updating should be doable by downloading the full datatables distribution and adding it to the DataTables extension directory, and then modifying the version number in DataTables.php

The distribution contains optional css and js files that are not currently used. For example, we load the default js and css files but do not register the ones optimized for BootStrap in Mediawiki.

== Version 0.4 ==
* update to dataTables 1.10.16
== Version 0.3 ==
* Refactor to fit with the new MW extension system after MW 1.25. The only thing this extension does in 1.25 or lower is register the DataTables modules.  All of this can go in extension.json, I think.
== Version 0.2 ==
Restructured to facilitate updating the Datatables library
* All DataTables distro files are now in a DataTables-version directory
** the old media directory has been removed
** legacy extras were kept for backward compatibility, if any.
* extension set is changed to reflect updated extensions
** Buttons replaces ColVis
** Buttons and Select replace TableTools
** Add Responsive
** Add RowReorder
