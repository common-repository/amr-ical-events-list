<?php
/**
 * Uninstall functionality for amr iCal Events List plugin.
 * 
 * Removes the plugin cleanly in WP 2.7 and up
 */

// first, check to make sure that we are indeed uninstalling
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}

delete_option('amr-ical-calendar_preview_url');
delete_option('amr-ical-events-version');
delete_option('amr-ical-events-list');
delete_option("amricalWidget");
delete_option("amr-ical-widget");
delete_option('amr-ical-images-to-use');

// now look for and delete cache files in upload dir
$upload_dir = wp_upload_dir();
$dir_to_delete = $upload_dir['basedir'] . '/ical-events-cache/' ;
$files = list_files( $dir_to_delete );
if (!empty( $files) ) {
	foreach ($files as $i=>$f) {
	 unlink($f);		
	}
}
rmdir($dir_to_delete);

/*		echo '<p>'.__('Css files may also exist.  They and the css folder have not been deleted as they have been shared with other plugins.', 'amr-ical-events-list').'</p>';
$cssdir = $upload_dir . '/css/' ;

*/
