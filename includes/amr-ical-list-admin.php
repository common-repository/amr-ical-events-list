<?php
/* This is the amr ical wordpress admin section file */

	$amricaladmin = new amrical_plugin_admin();
	
	add_filter('plugin_action_links', 'amr_plugin_action', 8, 2);
 	
function amr_plugin_action($links, $file) {
	static $this_plugin;

	if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);


	if( stristr($this_plugin,$file )) {
	/* create link */
		array_unshift($links,'<a href="admin.php?page=manage_amr_ical">'. __('Settings','amr-ical-events-list').'</a>' );
	}

	return $links;
	} // end plugin_action()
 
function amr_ical_support_links () {

	echo '<div class="postbox" style="padding:1em 2em; width: 600px;">
	<p>
	<a target="_blank" href="http://icalevents.com/documentation" title="documentation for amr-ical-events-list and amr-events">';
	_e('Documentation', 'amr-ical-events-list');
	echo '</a>&nbsp; &nbsp;
	<a target="_blank" href="http://icalevents.com/support/" title="Support Forum">';
	_e('Support', 'amr-ical-events-list');

	echo '</a>&nbsp; &nbsp;
	<a target="_blank" href="https://wordpress.org/support/plugin/amr-ical-events-list/reviews/" title="If you like it rate it...">';
	_e('Rate or Review', 'amr-ical-events-list');
	echo '</a>&nbsp; &nbsp;<a target="_blank" href="http://icalevents.com/feed/">';
	_e('Plugin feed', 'amr-ical-events-list');
	echo '</a>&nbsp; &nbsp;<img src="';
	echo includes_url(); 
	echo 'images/rss.png" alt="Rss icon" style="vertical-align:middle;" />';
	echo '</p>';	
	echo '<p>';
	_e('To edit events in wordpress:','amr-ical-events-list');
	echo ' <span style="color: green;">';
	_e('Upgrade to amr-events at ','amr-ical-events-list');

	echo ' <a target="_blank" title="home of both plugins" href="http://icalevents.com" >icalevents.com</a></b>';
	echo '</span><br /> ';
	
	_e('Need help remembering shortcodes?', 'amr-ical-events-list');
	?><a href="https://wordpress.org/plugins/amr-shortcodes/"> <?php
	_e('List all shortcodes in your site', 'amr-ical-events-list');?>
	</a>
<p>
	<?php	_e('Create page with ics calendar shortcode: &nbsp; &nbsp; &nbsp; ', 'amr-ical-events-list');?>
	<b><a title="needs an ics file url" href="<?php 
	echo admin_url( 'post-new.php?post_type=page&content=[ical http://youricsfeedurl]'); 
	?>"><?php _e('Add ics calendar page', 'amr-ical-events-list'); ?></a></b>
</p>	
<p>
	<?php	_e('Create page with events calendar shortcode: ', 'amr-ical-events-list');?>
	<b><a title="needs amr-events" href="<?php 
	echo admin_url( 'post-new.php?post_type=page&content=[events]'); 
	?>"><?php _e('Add events calendar page', 'amr-ical-events-list'); ?></a></b>
</p></div><?php
	}
 	
function amr_get_files ($dir, $string) {
	if (!file_exists($dir)) return false;
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
		if (stristr ($filename, $string))
		$files[] = $filename;
		}
	if (isset ($files)) return ($files);
	else return (false);
	}

function amr_check_timezonesettings () {

	global $amr_globaltz;
	echo '<ul>';
	
	if (function_exists('timezone_version_get'))
		printf('<li>'.__('Your timezone db version is: %s','amr-ical-events-list').'</li>',  timezone_version_get());
	else echo '<li>'.'<a href="http://en.wikipedia.org/wiki/Tz_database">'
		.__('Plugin cannot determine timezonedb version in php &lt; 5.3.' ,'amr-ical-events-list')
		.'</a>';
	echo '</li></li><li><a href="http://pecl.php.net/package/timezonedb">';
	_e('Php timezonedb versions', 'amr-ical-events-list');
	echo '</a> &nbsp; <a href="http://pecl.php.net/package/timezonedb">';
	_e('Info on what changes are in which timezonedb version', 'amr-ical-events-list');
	echo '</a></li>';
	if (!(isset($amr_globaltz))) {
			echo '<b>'.__('No global timezone - is there a problem here? ','amr-ical-events-list').'</b>'; 			return;
		}
		$tz = get_option('timezone_string');
		$settingslink = '<a href="'.get_option('siteurl').'/wp-admin/options-general.php">'.__('Go to settings','amr-ical-events-list').'</a>';
		if ($tz == '') {
			$gmtoffset = get_option('gmt_offset');
			if (!empty($gmtoffset ) ) {
				printf('<li style="color: red;"><b>'.__('You are using the "old" gmt_offset setting ','amr-ical-events-list').'</b></li><li>', $gmtoffset );
				_e('Consider changing to the more accurate timezone setting','amr-ical-events-list');
				echo '</li>';
				}
		}
		$now = date_create('now', $amr_globaltz);
		echo '<li>'.__('The plugin thinks your timezone is: ','amr-ical-events-list')
		. timezone_name_get($amr_globaltz)
		.'&nbsp;'.$settingslink
		.'</li>'
		.'<li>'.__('The current UTC offset for that timezone is: ','amr-ical-events-list').$now->getoffset()/(60*60).'</li>';

		if (function_exists('timezone_transitions_get') ) {
			
			foreach (timezone_transitions_get($amr_globaltz) as $tr) {  //2018 06 24 remove the break code just in case
				if (empty($nexttransition) and ($tr['ts'] > time())) {
					$nexttransition = 	$tr;
				}
			}
			if (!empty($nexttransition)) $tr = $nexttransition; //20210628  eg if UTC timezone
		}
		
		$utctz= new DateTimeZone('UTC');
		if (isset ($tr['ts']) ) {
		
			$d = amr_create_date_time ( "@{$tr['ts']}",$utctz );
			//try {$d = new DateTime( "@{$tr['ts']}",$utctz );}
			//catch(Exception $e) { break;}
			
			date_timezone_set ($d,$amr_globaltz );
			printf('<li>'.__('Switches to %s on %s. GMT offset: %d', 'amr-ical-events-list').'</li>',
				 $tr['isdst'] ? "DST" : "standard time",
				$d->format('d M Y @ H:i'), $tr['offset']/(60*60)
			);
		}
		echo '<li>';
		_e('Current time (unlocalised): ','amr-ical-events-list');
		echo $now->format('r').'</li>';
		echo '<li>'.__('Current time (localised): ','amr-ical-events-list').' '.amr_wp_format_date('r', $now, false).'</li>';
'</ul>';
	}

function amr_ical_submit_buttons ($resettext='') {
global $calendar_preview_url, $current_list;
	if (empty($resettext)) 
		$resettext=__('Reset all listing options', 'amr-ical-events-list');

?>
<fieldset id="submit">
	<input type="hidden" name="action" value="save" />
	<input type="submit" class="button-primary" title="<?php _e('Save the settings','amr-ical-events-list'); ?>" 
	value="<?php _e('Update', 'amr-ical-events-list') ; ?>"/>
	<input type="submit" class="button" name="reset" title="<?php _e('Warning: This will reset ALL the listing options immediately.','amr-ical-events-list') ;?>" value="<?php echo $resettext; ?>"/>
	<?php
		$calendar_preview_url = get_option('amr-ical-calendar_preview_url' );
		if ($calendar_preview_url) {
			?><a class="button" target="wp-preview"  href="<?php
			echo htmlspecialchars(add_query_arg(array('listtype'=>$current_list, 'preview'=>'true'),$calendar_preview_url)).'" '
			.' title="'.__('Preview the list using a calendar page.','amr-ical-events-list').'" >'
			.__('Preview','amr-ical-events-list');?>
			</a><?php
		}
	?>
</fieldset>
<?php			
}
 
function amrical_listing_options_page()  {
	global $current_list, $amr_options, $listtype;
	
	if (!empty($_REQUEST["list"])) 
		$current_list = intval( $_REQUEST["list"]);
	else 
		$current_list = 1;

	if (isset ($_POST['reset']))
		$amr_options = amr_getset_options (true);
	else
		$amr_options = amr_getset_options(false);	/* options will be set to defaults here if not already existing */
	
	if (!isset($_REQUEST["list"]) ) {
		amrical_admin_heading(__('Manage Event List Types', 'amr-ical-events-list') ) ;

		if (!empty($_POST['delete']) ) /* Validate the input and save */
			amrical_delete_listings();
		elseif ((isset ($_POST['action']) and ($_POST['action'] == "save")) and !isset($_POST['reset'])) /* Validate the input and save */
			amrical_validate_manage_listings();
		
		amr_ical_submit_buttons ();
		amrical_manage_listings();
		amrical_admin_footer();
		return;
		}
	else {


		amrical_admin_heading(__('Configure event list type: ', 'amr-ical-events-list'). $current_list ) ;
		amrical_admin_navigation();




		if ((isset ($_POST['action']) and ($_POST['action'] == "save")) and !isset($_POST['reset'])) {/* Validate the input and save */
				echo '<div class="updated"><p>';
				_e('Saving....','amr-ical-events-list');
				$result = amr_ical_validate_list_options($current_list); /* messages are in the function */
				if ($result) _e('Lists saved','amr-ical-events-list');
					else _e('No change to options or unexpected error in saving','amr-ical-events-list');
				echo '</p></div>';

		}
		amr_ical_submit_buttons ();
		amr_configure_list($current_list);
		amrical_admin_footer();
	}
}	//end amrical_option_page
 
function amrical_delete_listings () {
global $amr_options;

	$nonce = $_REQUEST['_wpnonce'];
	if (! wp_verify_nonce($nonce, 'amr-ical-events-list')) die ("Cancelled due to failed security check");

	echo '<h3>'.__('Check for lists to delete','amr-ical-events-list').'</h3>';
// ------------------- deleted lists
	if (!empty($_POST['deletelist']))  {
		foreach ($_POST['deletelist'] as $i=>$del) {
			$d = (int) $del;
			unset ($amr_options['listtypes'][$d]);
			echo '<div class="updated"><p>'
			.sprintf(__('List %s will be deleted','amr-ical-events-list'),$d)
			.'</p></div>';
		}
	}
	update_option('amr-ical-events-list', $amr_options);

}
 
function amrical_col_headings() {
	/* for component properties only */
	global $current_list, $amr_options, $amr_csize;
		$listtype = $amr_options['listtypes'][$current_list];

		$j = 0;
		while ($j < 8) {
			$j = $j + 1;
			if (isset ( $listtype['heading'][$j] )) {
				$h = $listtype['heading'][$j];
			}
			else $h = '';

			echo '<label class="colhead" for="h'.$current_list.'-'.$j.'" >'
				.'<input type="text" size="'.$amr_csize['ColHeading'].'"  class="colhead"  id="h'.$current_list.'-'.$j
				.'"  name="ColH['.$current_list.']['.$j.']"  value= "'.htmlspecialchars($h).'"  />&nbsp;'
				.$j.'</label>';
		}
		echo PHP_EOL;
		return;
}
 
function amrical_calpropsoption() {
	global $current_list, $amr_options, $amr_csize;
		$listtype = $amr_options['listtypes'][$current_list];
		foreach ( $listtype['calprop'] as $c => $v )
		{
			echo "\n\t\t".'<fieldset class="layout"><legend>'.$c.'</legend>';
			foreach ( $v as $si => $sv )  /* for each specification */
			{	echo '<label class="'.$si.'" for="CalP'.$si.$current_list.$c.'" >'.$si.'</label>'
					.'<input type="text" size="'.$amr_csize[$si].'"  class="'.$si.'"  id="CalP'.$si.$current_list.$c
					.'"  name="'.'CalP['.$current_list.']['.$c.']['.$si.']"  value= "'.htmlspecialchars($sv).'"  />';
			}
			echo "\n\t\t".'</fieldset>';
		}

		return;
	}

 
function amrical_admin_heading($title)  {
	echo '<div class="wrap" id="amrical">
		<div id="icon-options-general" class="icon32"><br />
		</div>
		<h2>'.$title.' v'.AMR_ICAL_LIST_VERSION.'</h2>
		<form method="post" action="'
//		.esc_url($_SERVER['PHP_SELF'])
		.'">';
		wp_nonce_field('amr-ical-events-list'); /* outputs hidden field */
		;
}
 
function amrical_admin_footer()  {
	echo '</form>
		</div>';
}
 
function amrical_admin_navigation()  {
	global $amr_options;
	echo '<div id="listnav"  style="clear:both;">';
	if (!isset($_REQUEST["list"]) ) { $list = '';}
	else $list = intval( $_REQUEST["list"]);
	$url = remove_query_arg('list');
	
	_e('Configure another list type: ','amr-ical-events-list');

	foreach ($amr_options['listtypes'] as $i => $listtype) {
		if ($i > 1) echo '&nbsp;';
		$text = ' <a title="'.$listtype['general']['name']
		.'" href="'.$url.'&amp;list='.$i.'">'.$i
//		.$listtype['general']['name']
		.'</a>';
		if ($list==$i) 	echo '<b>'.$text.'</b>';
		else echo $text;
	}

	if (isset($_GET["list"]) ) {
		echo '<a style=" padding-left: 20px;" href="'.$url.'" >'.__('Return to manage list types','amr-ical-events-list' ).'</a>';
	}
	echo '</div>';
}
 
function amrical_validate_manage_listings()  {
	global	$amr_options;

	$nonce = $_REQUEST['_wpnonce'];
	if (! wp_verify_nonce($nonce, 'amr-ical-events-list')) die ("Cancelled due to failed security check");

	if (!empty($_POST['calendar_preview_url'])) 	{
		if	(!filter_var($_POST['calendar_preview_url'],FILTER_VALIDATE_URL)) {
			 amr_invalid_url();
		}
		else {
			$url = filter_var($_POST['calendar_preview_url'],FILTER_SANITIZE_URL);
			$sticky_url = amr_make_sticky_url($url);
			if (!$sticky_url)
				$calendar_preview_url  = $url ; //might be external
			else
				$calendar_preview_url  = $sticky_url ;
			update_option('amr-ical-calendar_preview_url', $calendar_preview_url);
		}
	}
	else {
		update_option('amr-ical-calendar_preview_url', '');
	}

//----- list numbers
	$dupcheck = array(); // clear it out
	if (!empty($_POST['listnumber']))  {
		foreach ($_POST['listnumber'] as $i=> $n) {
			if ($n === '') break;
			$nn =  abs (intval ( $n));

			if ($nn === 0){
				echo '<div class="error">'.__('Please use numbers > 1','amr-ical-events-list').'</div>';
				return;
			}

			if (in_array($nn, $dupcheck))
				echo '<b>Duplicate List Number was entered - please ensure all list numbers are unique.</b>';
			else {
				$listnumber[$i] = $nn;
				$dupcheck[$nn] = $nn;  // keep a record of the numbers

				if (empty ($_POST['prevnumber'][$i]) or ($_POST['prevnumber'][$i] === '')) { // if we have no listtype for the  existing number, then must be new
					$amr_options['listtypes'][$nn] = new_listtype();
					$amr_options['listtypes'][$nn]['Id'] = $nn;
					$amr_options['listtypes'][$nn]['general']['name'] .= $nn;
					$amr_options['listtypes'][$nn] = customise_listtype($nn); // includes the fix, need till we drop away old compatibility
					echo '<br />Create new '.$nn;
				}
				else  {// we are changing a list number, copy the list type to the new number
					$prev = $_POST['prevnumber'][$i];
					if (!($prev == $nn)) {

						$amr_options['listtypes'][$nn] = $amr_options['listtypes'][$prev];
						$amr_options['listtypes'][$nn]['Id'] = $nn;
						unset($amr_options['listtypes'][$prev]);
					}
				}
			}
		}

		ksort ($amr_options['listtypes']);
	}
// ---------------------- imported lists
	if (isset($_POST['import'])) 	{
		$import = $_POST['import'];
		foreach ($import as $i=>$il) {

			if (!empty($il)) {
				//var_dump($il);
				$importedlist = unserialize(base64_decode($il));
				$importedlist['Id'] = $i;  // use the id just pasted into

				if ((!is_array($importedlist)) or empty($importedlist['general']))
					echo '<div class="error"><p>'
					.sprintf(__('Imported settings for list %s invalid - not saved','amr-ical-events-list'),$i)
					.'</p></div';
				else {
					$amr_options['listtypes'][$i] = $importedlist;
					
					echo '<div class="updated"><p>'
					.sprintf(__('List %s will be saved with imported data','amr-ical-events-list'),$i)
					.'</p></div';
				}
			}
		}
	}
	
	update_option('amr-ical-events-list', $amr_options);


}
 
function amr_manage_listtypes_row ($listtype, $i) {
global $calendar_preview_url;

		if (!isset ($listtype['Id']) ) {
			$id = '0';
			$num = '';
		}
		else {
			$id = $listtype['Id'];
			$num = $id;
		}

		echo '<tbody><tr>';
		echo '<td>';
		echo '<label for="delete'.$id.'"><input type="checkbox" id="delete'.$id.'" value="'.$id.'" name="deletelist['.$id.']" />';
		echo '</label>';
		echo '</td>';
		echo '<td>';


		echo '<input type="text" size="1" id="listnumber'.$id.'" name="listnumber['.$id.']" value="'
			.$num.'" style="margin:0;" />';
		echo '<input type="hidden"  id="prevnumber'.$id.'" name="prevnumber['.$id.']" value="'
			.$num.'" />';

		echo '</td>';

		if (!empty($listtype['general']) ) {
			echo '<td>';
			echo '<a href="'.esc_attr(add_query_arg('list',$id)).'" '
			.' title="'.__('Configure: ','amr-ical-events-list').$listtype['general']['Description'].'" >'
			.$listtype['general']['name'].'</a>'
			.'<div class="row_action">';
			echo '<a href="'.esc_attr(add_query_arg('list',$id)).'" '
			.' title="'.__('Click to choose the fields, the columns and set other parameters.','amr-ical-events-list').'" >'
			.__('Configure', 'amr-ical-events-list').'</a>&nbsp;|&nbsp;';
			if ($calendar_preview_url) {
				echo '<a target="wp-preview" href="'
				.esc_attr(add_query_arg(array('listtype'=>$id, 'preview'=>'true'),$calendar_preview_url)).'" '
				.' title="'.__('Preview the list using a calendar page.','amr-ical-events-list').'" >'
				.__('Preview','amr-ical-events-list').'</a>';
			}
			else {
				echo '<a href="" title ="'
				.__('Calendar Page URL for previews must be entered above','amr-ical-events-list').' ">'
				.__('n/a','amr-ical-events-list').'</a>';
			}

			echo '</div>';
			echo '</td>';
			echo '<td>'.$listtype['general']['ListHTMLStyle'];
			echo '</td>';
			echo '<td><textarea id="export'
			.$i.'" rows="2" cols="50" readonly="readonly"  '
			.' style="margin: 0;" '
//		.'onClick="select_all(\'export'.$id.'\');"'
			.'name="export['.$id.']" >';
			if (!empty($listtype) ) {
				echo base64_encode(serialize($listtype));  // too many problems when not encoded
			}
			echo '</textarea></td>';
			echo '<td><textarea id="import'.$id.'" rows="2" cols="50" '
			.' style="margin: 0;" '
//		.'onClick="select_all(\'import'.$id.'\');"'
			.'name="import['.$id.']" >'
			.'</textarea></td>';
		}
		else {
			echo '<td colspan="2">';
			_e('Enter a list number to start a list type with new default settings.','amr-ical-events-list');
			echo '</td><td>'
			.__('After that, cut and paste from the list type closest to what you want to get you started','amr-ical-events-list')
			.'</td><td></td>';
		}
		
		echo '</tr></tbody>';
}
 
function amrical_option_page()  {
	global $amr_options;
	//$nonce = wp_create_nonce('amr-ical-events-list'); /* used for security to verify that any action request comes from this plugin's forms */	
	amrical_admin_heading(__('iCal Events List ', 'amr-ical-events-list'));
	
	if (isset ($_POST['reset']))
		$amr_options = amr_getset_options (true);
	else
		$amr_options = amr_getset_options(false);	/* options will be set to defaults here if not already existing */

	if (!(isset ($_POST['reset'])) and (isset ($_POST['action']) and ($_POST['action'] == "save"))) {/* Validate the input and save */
		echo '<div class="updated"><p>';
		_e('Saving....','amr-ical-events-list');
		if (!isset($_REQUEST['list'])) {
				if (! amr_ical_validate_general_options() )
					{echo '<h2>Error validating general options</h2>';}
				else _e('List saved','amr-ical-events-list');
			}
		echo '</p></div>';	
	}

	//amr_request_acknowledgement();
	amr_ical_support_links ();
	//amrical_mimic_meta_box('acknowledge', 'About', 'amr_request_acknowledgement' , true);

	//if (!(is_plugin_active('amr-events/amr-events.php'))) {amr_getting_started();}
	amr_ical_submit_buttons ();
	amr_ical_general_form();
	echo '</form></div>';
}	//end amrical_option_page

function amrical_add_options_panel() {

	global $wp_version,
			$current_user,
			$events_menu_added;

	/* add the options page at admin level of access */
		$parent_slug =  'amr-events';
		$capability = 'manage_event_settings';
		

	
		$menu_title = $page_title = __('iCal Events List', 'amr-ical-events-list');

		$function = 'amrical_option_page';
		
		$menu_slug = 'manage_amr_ical';

		if (function_exists('amr_events_settings_menu')) {
			$menu_title = $page_title = __('Listing Events', 'amr-ical-events-list');

			if (empty($events_menu_added) or (!$events_menu_added)) {
				amr_events_settings_menu();
				$events_menu_added = true;
				}
			add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
			if (!(current_user_can('manage_event_settings'))) 
				add_submenu_page( $parent_slug, $page_title, $menu_title, 'manage_options', $menu_slug, $function); // some sites need this
		}

		else {
			$capability = 'manage_options';
			add_menu_page($page_title, $menu_title , $capability, $menu_slug, $function);
			$parent_slug = $menu_slug;
//		$page = add_options_page($page_title, $menu_title , 'manage_options', $menu_slug, $function);

		}
		$function = 'amrical_choose_fields';
		$menu_slug = 'manage_event_fields';
		$page_title = __('iCal Fields', 'amr-ical-events-list');
		$menu_title = __('Fields for lists', 'amr-ical-events-list');
		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);		

		
		$function = 'amrical_listing_options_page';
		$menu_slug = 'manage_event_listing';
		$page_title = __('iCal Events Lists', 'amr-ical-events-list');
		$menu_title = __('List types', 'amr-ical-events-list');
		add_submenu_page( $parent_slug, $page_title, $menu_title,$capability, $menu_slug, $function);
		

		amr_ical_updates_menu($parent_slug);
}

//build admin interface =======================================================
function amr_ical_validate_general_options(){
		global
		$amr_options,
		$amr_calprop,
		$amr_limits,
		$amr_compprop,
		$amr_groupings,
		$amr_components;

		$nonce = $_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce, 'amr-ical-events-list')) die ("Cancelled due to failed security check");

			foreach (array(
				'noeventsmessage',
				'lookmoremessage',
				'lookprevmessage',
				'resetmessage',
				'freebusymessage'   // was missing 20141119
				) as $message) {
				if (isset($_POST[$message])) 	
					$amr_options[$message] =  $_POST[$message];
				else 
					$amr_options[$message] =  '';

			}
			if (isset($_POST["usehumantime"])) 
				$amr_options['usehumantime'] =  true;
			else 
				$amr_options['usehumantime'] =  false;
				
			if (isset($_POST["do_grouping_js"])) 
				$amr_options['do_grouping_js'] =  true;
			else 
				$amr_options['do_grouping_js'] =  false;
				
			if (isset($_POST["js_only_these_pages"])) {
				$tmp = explode (',',$_POST["js_only_these_pages"]);
				foreach ($tmp  as $i) {$i = (int) $i;}
				$amr_options['js_only_these_pages'] =  $tmp;
				}
			else 
				$amr_options['js_only_these_pages'] =  false;	
				
			if (isset($_POST["own_css"])) 
				$amr_options['own_css'] =  true;
			else 
				$amr_options['own_css'] =  false;
			if ((isset($_POST["date_localise"])) and (in_array($_POST["date_localise"], array('none', 'wp', 'wpgmt', 'amr')) )) $amr_options['date_localise'] =  $_POST["date_localise"];		/* from dropdown */
			else $amr_options['date_localise'] =  'none';
			if (isset($_POST["cssfile"])) $amr_options['cssfile'] =  $_POST["cssfile"];		/* from dropdown */
			else $amr_options['cssfile'] =  '';
			if (isset($_POST["no_images"]))  $amr_options['no_images'] =  true;		/* from dropdown */
			else $amr_options['no_images'] =  false;

			if (isset($_POST['images_size']))  {
				$amr_ical_image_settings['images_size'] =  (int) ($_POST['images_size']) ;		/* from dropdown */
				}
			else
				$amr_ical_image_settings['images_size'] =  '16';
				
			if (isset($_POST['timeout']))  {
				$amr_options['timeout'] =  (int) ($_POST['timeout']) ;		/* from dropdown */
				}
			else
				$amr_options['timeout'] =  '5';	

			update_option('amr-ical-events-list', $amr_options);
			update_option('amr-ical-images-to-use', $amr_ical_image_settings);
			amr_ical_events_list_record_version();

			return(true);
	}

function amr_ical_validate_list_options($i)	{
global $amr_options;

	if (isset($_POST['general']))  { 
		if (is_array($_POST['general'][$i])){
			foreach ($_POST['general'][$i] as $c => $v)	{
				if (!empty($_POST['general'][$i][$c])) {
					switch ($c) {
					case 'Default Event URL' : {
						if	(!filter_var($_POST['general'][$i][$c],FILTER_VALIDATE_URL)) {
							 amr_invalid_url();
						}
						else {
							$url = filter_var($_POST['general'][$i][$c],FILTER_SANITIZE_URL);
							$sticky_url = amr_make_sticky_url($url);
							if (!$sticky_url)
								$amr_options['listtypes'][$i]['general'][$c] = $url ; //might be external
							else
								$amr_options['listtypes'][$i]['general'][$c] = $sticky_url ;
						}
						break;
					}
					case 'customHTMLstylefile': {
							$custom_htmlstyle_file = esc_attr($_POST['general'][$i]['customHTMLstylefile']);

							if (!($custom_htmlstyle_file[0]  === '/'))
								$custom_htmlstyle_file = '/'.$custom_htmlstyle_file;
							$uploads = wp_upload_dir();
							if (!file_exists($uploads['basedir'].$custom_htmlstyle_file))  {
								amr_invalid_file();
								$amr_options['listtypes'][$i]['general']['customHTMLstylefile'] = ' ';
								}
							else {
								$amr_options['listtypes'][$i]['general']['customHTMLstylefile']
								= $custom_htmlstyle_file;
							}
							break;
						}

					default: {   
						$amr_options['listtypes'][$i]['general'][$c]
						= filter_var($_POST['general'][$i][$c],FILTER_SANITIZE_STRING) ;
					}
					}
				}
				else	$amr_options['listtypes'][$i]['general'][$c] = '';
			}
		}
		else echo 'Error in form - general array not found';
	}

	if (isset($_POST['limit']))
			{	if (is_array($_POST['limit'][$i]))
				{	foreach ($_POST['limit'][$i] as $c => $v)
					{
						$amr_options['listtypes'][$i]['limit'][$c] =
							(isset($_POST['limit'][$i][$c])) ? $_POST['limit'][$i][$c] :11;
					}
				}
				else echo 'Error in form - limit array not found';
			}
	if (isset($_POST['format'])){	
		if (is_array($_POST['format'][$i]))
			{	foreach ($_POST['format'][$i] as $c => $v)
				{   /* amr - how should we validate this ?  accepting any input for now */
					$amr_options['listtypes'][$i]['format'][$c] =
						(isset($_POST['format'][$i][$c])) ? stripslashes_deep($_POST['format'][$i][$c]) :'';
				}
			}
		else echo 'Error in form - format array not found';
	}

	foreach ($amr_options['listtypes'][$i]['component'] as $k => $c) {
		if (isset($_POST['component'][$i][$k])) {
			$amr_options['listtypes'][$i]['component'][$k] =  true;
		}
		else {
			$amr_options['listtypes'][$i]['component'][$k] =  false;
		}
	}
	unset ($amr_options['listtypes'][$i]['grouping']);
	
	if (isset($_POST['level'][1])) { 
		$k = esc_attr($_POST['level']['1']); 
		if (!($k === 'none')) $amr_options['listtypes'][$i]['grouping'][$k] =  '1';
	}
	if (isset($_POST['level'][2])) { 
		$k = esc_attr($_POST['level']['2']); 
		if (!($k === 'none')) $amr_options['listtypes'][$i]['grouping'][$k] =  '2';
	}
				
				
	if (isset($_POST['ColH']))
				{	if (is_array($_POST['ColH'][$i])) {
						foreach ($_POST['ColH'][$i] as $c => $v) {
							$amr_options['listtypes'][$i]['heading'][$c] = $v;
						}
					}
					// else echo 'Error in form - grouping array not found';   /* May not want any groupings ?
				}
	if (isset($_POST['CalP'])) {
		if (is_array($_POST['CalP'][$i])) {
			foreach ($_POST['CalP'][$i] as $c => $v) {
			   if (is_array($v))
				foreach ($v as $p => $pv){
					/*need to validate these */
					switch ($p):
					case 'Column':
						if (function_exists( 'filter_var') )
						{	if (filter_var($pv, FILTER_VALIDATE_INT,
							array("options" => array("min_range"=>0, "max_range"=>20))))
								$amr_options['listtypes'][$i]['calprop'][$c][$p]= $pv;
							else 	
								$amr_options['listtypes'][$i]['calprop'][$c][$p]= 0;
						}
						else $amr_options['listtypes'][$i]['calprop'][$c][$p]= $pv;
						break;

					case 'Order':
						if (function_exists( 'filter_var') )
						{	if (filter_var($pv, FILTER_VALIDATE_INT,
							array("options" => array("min_range"=>0, "max_range"=>99))))
							$amr_options['listtypes'][$i]['calprop'][$c][$p] = $pv;break;
						}
						else 
							$amr_options['listtypes'][$i]['calprop'][$c][$p] = $pv;break;
					case 'Before': $amr_options['listtypes'][$i]['calprop'][$c][$p] = wp_kses($pv, amr_allowed_html());
						break;
					case 'After': $amr_options['listtypes'][$i]['calprop'][$c][$p] = wp_kses($pv, amr_allowed_html());
						break;
					endswitch;
				}
			}
		}
		else _e('Error in form - calprop array not found', 'amr-ical-events-list');

	}

	if (isset($_POST['ComP']))  {
		if (is_array($_POST['ComP'])) {
			foreach ($_POST['ComP'] as $c => $v) { /* eg c= summary */
				if (is_array($v)) {
					foreach ($v as $p => $pv)	{
						/*need to validate these */
						switch ($p):
						case 'Column':
							if (function_exists( 'filter_var') )
							{	if (filter_var($pv, FILTER_VALIDATE_INT,
								array("options" => array("min_range"=>0, "max_range"=>20))))
									$amr_options['listtypes'][$i]['compprop'][$c][$p]= $pv;
								else
									$amr_options['listtypes'][$i]['compprop'][$c][$p]= 0;
								break;
							}
							else $amr_options['listtypes'][$i]['compprop'][$c][$p]= $pv;
							break;
						case 'Order':
							if (function_exists( 'filter_var') )
							{	if (filter_var($pv, FILTER_VALIDATE_INT,
								array("options" => array("min_range"=>0, "max_range"=>99))))
									$amr_options['listtypes'][$i]['compprop'][$c][$p] = $pv;
								else
									$amr_options['listtypes'][$i]['compprop'][$c][$p]= 0;
								break;
							}
							else $amr_options['listtypes'][$i]['compprop'][$c][$p] = $pv;
							break;
						case 'Before':
							$bef = wp_kses($pv, amr_allowed_html());
							$bef = wp_kses_stripslashes($bef);
							//if (stripos($bef, '\\"')) echo 'YES still there';
							$bef = str_replace('\\"', '"', $bef);
							$amr_options['listtypes'][$i]['compprop'][$c][$p] = $bef;
							//if ($c == 'URL') echo 'TEST:'. $bef.' else '.$amr_options['listtypes'][$i]['compprop'][$c][$p];
							
							
							break;
						case 'After':
							$amr_options['listtypes'][$i]['compprop'][$c][$p] = wp_kses_stripslashes(wp_kses($pv, amr_allowed_html()));
							break;
						endswitch;
					}
				}
			}
			$amr_options['listtypes'][$i]['compprop']
			= amr_sort_by_two_cols_asc('Column','Order',$amr_options['listtypes'][$i]['compprop']);
		}
		else echo 'Error in form - compprop array not found';
	}
	$result = update_option( 'amr-ical-events-list', $amr_options);
	return($result);
}
	 
function amrical_general_form () {
	global $current_list, $amr_options;

	$listtype = $amr_options['listtypes'][$current_list];
	
 ?><fieldset id="general" class="general" >
	<div class="postbox-container"><?php
	if (! isset($listtype['general'])) echo 'No general specifications set';
	else {

	?><label for="name" ><?php _e('Name','amr-ical-events-list'); ?></label>
		<input type="text" class="wide" size="40" id="name" name="general[<?php echo $current_list; ?>][name]" value="<?php
		if (isset($listtype['general']['name'])) echo $listtype['general']['name']; ?>" />
</div>
<div class="postbox-container">
	<label for="description" ><?php _e('Internal Description','amr-ical-events-list'); ?></label><br />
		<textarea cols="60" rows="4" id="description" name="general[<?php echo $current_list; ?>][Description]"><?php
		if (isset($listtype['general']['Description'])) echo $listtype['general']['Description']; ?></textarea><br />
</div>

<?php 	amrical_other_form(); ?>
</fieldset><?php
	return ;
	}
}
		 
function amrical_other_form () {
	global $current_list, $amr_options;
	$listtype = $amr_options['listtypes'][$current_list];
	if (isset($listtype['general']['ListHTMLStyle']))
			$style = $listtype['general']['ListHTMLStyle'];
	else
			$style = '';

	if (! isset($listtype['general'])) echo 'No general specifications set';
	else {?>
<br />
<div class="postbox-container">
	<label for="ListHTMLStyle" ><?php _e('List HTML Style','amr-ical-events-list'); ?></label>
		<select class="wide" id="ListHTMLStyle" name="general[<?php echo $current_list; ?>][ListHTMLStyle]">
			<option value="table" <?php if ($style==='table') echo 'selected="selected" '; ?>><?php _e('Table', 'amr-ical-events-list'); ?></option>
			<option value="HTML5table" <?php if ($style=='HTML5table') echo 'selected="selected" '; ?>><?php _e('HTML5 in table', 'amr-ical-events-list'); ?></option>
			<option value="HTML5" <?php if ($style==='HTML5') echo 'selected="selected" '; ?>><?php _e('HTML5 clean and lean', 'amr-ical-events-list'); ?></option>
			<option value="breaks" <?php if ($style==='breaks') echo 'selected="selected" '; ?>><?php _e('Breaks for rows!', 'amr-ical-events-list'); ?></option>
			<option value="smallcalendar" <?php if ($style==='smallcalendar') echo 'selected="selected" '; ?>><?php _e('Small box calendar', 'amr-ical-events-list'); ?></option>
			<option value="largecalendar" <?php if ($style==='largecalendar') echo 'selected="selected" '; ?>><?php _e('Large box calendar', 'amr-ical-events-list'); ?></option>
			<option value="weekscalendar" <?php if ($style==='weekscalendar') echo 'selected="selected" '; ?>><?php _e('Weeks calendar', 'amr-ical-events-list'); ?></option>
			<option value="list" <?php if ($style==='list') echo 'selected="selected" '; ?>><?php _e('Lists for rows', 'amr-ical-events-list'); _e(' *Avoid - deprecated','amr-ical-events-list'); ?></option>
			<option value="tableoriginal" <?php if ($style==='tableoriginal') echo 'selected="selected" '; ?>><?php _e('Table with lists in cells (original)', 'amr-ical-events-list'); ?></option>
		</select></div>
<div class="postbox-container">
	<label for="defaulturl" ><?php _e('Default Event URL','amr-ical-events-list'); ?><em>
	<?php
//	_e(' (For ics files in widget. External, or calendar page.)','amr-ical-events-list'); ?>
	</em>
	<a title="<?php _e('More information','amr-ical-events-list'); ?>"	href="https://icalevents.com/1365-ical-events-and-default-urls-for-the-widget/" >?</a>
	</label>
		<input  class="wide" type="text" size="60" id="defaulturl" name="general[<?php echo $current_list; ?>][Default Event URL]" value="<?php
				if (isset($listtype['general']['Default Event URL']))
					echo ($listtype['general']['Default Event URL']); ?>" />
</div>
<?php
	}
	return ;
	}
 
function amrical_manage_listings()  {
	global $amr_options;
	global $calendar_preview_url;

	$calendar_preview_url = get_option('amr-ical-calendar_preview_url' );

	echo '<fieldset style="padding: 1em;"><label for="calendarpreviewurl" >'
	.__('Calendar Page URL for Previews:','amr-ical-events-list')
	.'<br /><em>'.__('Enter the url of a page with [events] or [largecalendar] in the content to use for list type previews.','amr-ical-events-list').'</em>'
	.'</label>';

	echo '<br /><input class="regular-text" type="text"  size="40" id="calendarpreviewurl" '
	.'name="calendar_preview_url" '
//	.'placeholder="http://domain.com/page_id=xx" '
	.'value="';
	echo ($calendar_preview_url ? ($calendar_preview_url) :  '');
	echo '" /></fieldset>';


	$url = remove_query_arg('list');?>
	<h3><?php _e('Click the name of each list type below to configure that list.','amr-ical-events-list'); ?></h3>
	<p><em><ul><li>
	<a target="new" title="<php _e('Go to plugin website for documentation.','amr-ical-events-list' ); ?>" 
	href="http://icalevents.com/documentation/list-types/#config"><?php 
	_e('Configuration help','amr-ical-events-list' ); ?></a></li>
<li>
	<?php _e('Be careful when editing or deleting - some listtypes are defaults for shortcodes and widgets. ','amr-ical-events-list'  ); ?> (1=agenda, 4=widget, 7=eventinfo, 8=small calendar, 9=largecalendar(month), 13=event master (tbc) ) </li>

<li><?php _e('Add listtype=n in the parameters of the shortcode or widget to use another list type.','amr-ical-events-list'  ); ?>
	</em></li></ul></p>

	<p><input type="submit" class="button" name="delete" title="'<?php 
	_e('Warning: This will delete all selected list types immediately.','amr-ical-events-list'); ?>" value="<?php _e('Delete'); ?>" /></p>
	<table class="wp-list-table widefat"><thead><tr>
	<th style="text-align: center; color:red;" >X</th>
	<th><?php _e('List','amr-ical-events-list'); ?></th>
	<th><?php _e('Name','amr-ical-events-list'); ?></th>
	<th><?php _e('List HTML Style','amr-ical-events-list'); ?></th>
	<?php echo '<th>'.__('To export or copy a list type','amr-ical-events-list')
	.'<br /><em>'.__('Select ALL the content, and COPY.','amr-ical-events-list')
	.' <a href="" title="'
	.__('The encoding is to prevent errors when copying and pasting.','amr-ical-events-list').' '
	.__('The whole string must be selected (it should be when you click on the text box)',   'amr-ical-events-list').' '
	.__('The list type is a huge array.','amr-ical-events-list'). ' '
	.__('Without encoding, there were varying problems with slashes. Encoding was more stable.','amr-ical-events-list')
	.'" >'.__('Why encode?','amr-ical-events-list').'</a>'

	.'</em>'
	.'</th>';
	echo '<th>'.__('To import or paste a list type','amr-ical-events-list')
	.'<br /><em>'.__('PASTE list type string','amr-ical-events-list')
	.' <a href="http://www.google.com/search?q=online+base64_decode" title="'
	.__('If you did not produce the string and are concerned about the contents, then inspect the list type string using a decode tool. You should see a serialised array.','amr-ical-events-list')
	.' - '.__('Click to search for decoding tools','amr-ical-events-list' ).'" >'.__('Test decode','amr-ical-events-list').'</a>'
	.'</em>'
	.'</th>';
	echo '</tr></thead>';

	foreach ($amr_options['listtypes'] as $i=>$listtype) {
		amr_manage_listtypes_row ($listtype, $i);
	}
	amr_manage_listtypes_row ($listtype=null, '');
	}

function amrical_formats () {
	global $current_list,$amr_options;

	?>
<p><em><?php
		_e('These are also used for the date related grouping headings (ie: will show the date in that format as a heading for that group of dates if relevant.)', 'amr-ical-events-list');
		?> <?php echo __('Use the standard PHP format strings: ','amr-ical-events-list')
			. '<a href="http://www.php.net/manual/en/function.date.php" target="new" title="'.__('Php manual - date datetime formats', 'amr-ical-events-list').'" '			
			.'> '
			.__('See php date function format strings' , 'amr-ical-events-list').'</a>'
			.__(' (will localise) ' , 'amr-ical-events-list')
	;?>
</em></p>
<?php
		if (! isset($amr_options['listtypes'][$current_list]['format'])) echo 'No formats set';
		else
		{	$date = amr_newDateTime();
?><table><?php
		foreach ( $amr_options['listtypes'][$current_list]['format'] as $c => $v ) {
			$l = str_replace(' ','', $c).$current_list;
			echo PHP_EOL.'<tr><td><label for="'.$l.' ">'.__($c,'amr-ical-events-list').'</label></td>';
			echo '<td><input type="text" size="50" id="'.$l.'" name="format['.$current_list.']['.$c.']"';
			echo ' value="'.$v.'" /></td><td> ';
			echo amr_format_date( $v, $date); //a* amr ***/
			echo '</td></tr>'.PHP_EOL;
		}
?></table><?php
		} ?>

<?php
	return ;
}
 
function amrical_componentsoption() {
	global $current_list, $amr_options;

	$listtype = $amr_options['listtypes'][$current_list];

	$desc = amr_set_helpful_descriptions ();

	if (! isset($listtype['component'])) echo 'No default components set';
		else {
			foreach ( $listtype['component'] as $c => $v ) {
				echo PHP_EOL.'<p><label for="C'.$current_list.$c.'" > &nbsp;';
				echo '<input type="checkbox" id="C'.$current_list.$c.'" name="component['.$current_list.']['.$c.']"';
				echo ($v ? ' checked="checked" />' : '/>');
				echo $c.'</label> <em>'.$desc[$c].'</em></p>'.PHP_EOL;
			}
		}
		echo PHP_EOL;
	return ;
	}
 
function amrical_limits() {
	global $current_list, $amr_options;
	$listtype = $amr_options['listtypes'][$current_list];
	echo	PHP_EOL.'<p><em>'.
		__('Note cache times are in hours','amr-ical-events-list')
		.'</em></p>';
		if (! isset($listtype['limit'])) echo 'No default limits set';
		else {		
			foreach ( $listtype['limit'] as $c => $v ) {		
				echo '<label for="L'.$current_list.$c.'" >'.__($c,'amr-ical-events-list').'</label>';
				echo '<input type="text" size="2" id="L'.$current_list.$c.'"  name="limit['.$current_list.']['.$c.']"';
				echo ' value="'.$v.'" />&nbsp;&nbsp;';
			}
		}

	return ;
	}

function amrical_fields() { // fields
	global $current_list, $amr_options, $amr_csize;
	
		$inuse =	amr_whats_in_use();
		$listtype = $amr_options['listtypes'][$current_list];
		$desc = amr_set_helpful_descriptions();  	

		?>
		<em><ul><li>
		<?php _e('Note: a 0 (zero) in column = do not show that field.', 'amr-ical-events-list');?>
		</li><li>
		<?php _e('Uppercase fields are those defined in the iCal specification.', 'amr-ical-events-list');?>
		<a title="RFC5545" target="_blank" href="http://tools.ietf.org/html/rfc5545">RFC 5545</a></li>
		<li><?php 
		_e('Lowercase fields are additional fields added by this plugin and derived from the iCal fields for your convenience.' , 'amr-ical-events-list');?>
		</li><li>
		<?php _e('Fields show if "column" > 0 and if there is data available in your event or ics file.', 'amr-ical-events-list');?>
		</li></ul>
		<table  class="widefat layout">
		<?php $thead = '<tr>'
		.'<th>'.__('Field','amr-ical-events-list').'</th>'
		.'<th>'.__('Column','amr-ical-events-list').'<br /><em>'
		.__('(0 to hide)', 'amr-ical-events-list')
		.'</em></th>'
		.'<th>'.__('Order','amr-ical-events-list').'</th>'
		.'<th>'.__('Before','amr-ical-events-list').'</th>'
		.'<th>'.__('After','amr-ical-events-list').'</th>'
		.'</tr>';
		echo PHP_EOL.'<thead>'.$thead.'</thead>';
		echo PHP_EOL.'<tfoot>'.$thead.'</tfoot>';
		echo PHP_EOL.'<tbody>';

		$listtype['compprop'] = apply_filters('amr_ics_component_properties', $listtype['compprop']);  
		// add arrays of field array('Column' => 0, 'Order' => 510, 'Before' => '', 'After' => '');
		
		foreach ($inuse as $f=> $bool) {
			if (!isset($listtype['compprop'][$f])) { // we got a new field
				$listtype['compprop'][$f] 
					= array('Column' => 0, 'Order' => 510, 'Before' => '', 'After' => '');
			}
		}
		
		foreach ( $listtype['compprop']  as $p => $pv )  {/* for each specification, eg: p= SUMMARY  */
			if (!empty($inuse[$p])) {
				$text = '<em class="desc">'.(!empty($desc[$p])? $desc[$p] : '').'</em>';
				echo PHP_EOL.'<tr style="border-bottom: 0; "><td style="border-bottom: 0; "><b>'.$p.'</b></td>';

				foreach ( $pv as $s => $sv )  {/* for each specification eg  $s = column*/

					echo PHP_EOL.'<td style="border-bottom: 0; ">'
						.'<input type="text" size="'.$amr_csize[$s].'"  class="'.$s.'"  id="'.$p.$s
						.'"  name="'.'ComP['.$p.']['.$s.']"  value= "'
// v 4.0.12	- want to be able to see &nbsp;					.(esc_attr(wp_kses_stripslashes($sv)))
						.htmlspecialchars ($sv)
						.'"  />'
						.'</td>';
				}
				echo '</tr>'.PHP_EOL.'<tr ><td colspan="5" style="padding: 0 20px 20px; 0" >'.$text.'</td></tr>';

			}
		}
		echo PHP_EOL.'</tbody></table> <!-- end of fields -->'.PHP_EOL;
		return;
	}
 
function amrical_groupingsoption() {
	global $current_list, $amr_options;
	$listtype = $amr_options['listtypes'][$current_list];
	
	$groupings = amr_define_possible_groupings ();
	$taxonomies = amr_define_possible_taxonomies (); 

	echo PHP_EOL.'<table><tr><th>'.__('Possible Groupings', 'amr-ical-events-list').'</th><th align=center>'.__('Level','amr-ical-events-list').' 1</th><th align=center> '.__('Level','amr-ical-events-list').' 2</th></tr>';
	$nolevel1 = false;
	$nolevel2 = false;	
	if (empty($listtype['grouping'])) { 
		$nolevel1 = true;
		$nolevel2 = true;
		}
	else { 
		if (count($listtype['grouping'])  < 2)	$nolevel2 = true;
		}


	echo PHP_EOL.'<tr><td>'.__('No grouping','amr-ical-events-list').'</td>';
	$sel = checked($nolevel1,true, false);
	echo PHP_EOL."<td align=center><input type='radio' name='level[1]' value='none' "
			. $sel."/></td>";
	$sel = checked($nolevel2,true, false);
	echo "<td align=center><input type='radio' name='level[2]' value='none' "
			. $sel."/></td>";
	echo '</tr>';	
	
	echo PHP_EOL.'<tr><th>'.__('Taxonomies','amr-ical-events-list').'</th><td colspan="2"><em>'.__('(Requires amr-events)','amr-ical-events-list').'</em></td></tr>';
	foreach ( $taxonomies as $i => $taxonomy ) {
		$taxo = get_taxonomy($taxonomy); 
		$c = $taxo->label.' ('.$taxo->name.')';  //202008 because bp didnt set up their tag taxonomies cleanly
		if (!empty($listtype['grouping'][$taxonomy])) 
			$v= $listtype['grouping'][$taxonomy];
		else 
			$v = false;
	
		echo PHP_EOL.'<tr><td>'.$c.'</td>';
		$sel = checked($v,1, false);
		echo "<td align=center><input type='radio' name='level[1]' value='".$taxonomy."' "
			. $sel."/></td>";
		$sel = checked($v,2, false);
		echo "<td align=center><input type='radio' name='level[2]' value='".$taxonomy."' "
			. $sel."/></td>";
		echo '</tr>';
		
	}
	echo PHP_EOL.'<tr><th>'.__('Date based','amr-ical-events-list').'</th><td colspan="2"><em>'.__('(See also date and time formats)','amr-ical-events-list').'</em></td></tr>';
	foreach ( $groupings as $c => $tmp ) {
		if (in_array($c,$taxonomies )) continue;  // don't repeat
		if (!empty($listtype['grouping'][$c])) 
			$v= $listtype['grouping'][$c];
		else 
			$v = false;
		echo '<tr><td>'.$c.'</td>';
		$sel = checked($v,1, false);
		echo "<td align=center><input type='radio' name='level[1]' value='".$c."' "
			. $sel."/></td>";
		$sel = checked($v,2, false);
		echo "<td align=center><input type='radio' name='level[2]' value='".$c."' "
			. $sel."/></td>";
		echo '</tr>';
	}
	echo PHP_EOL.'</table> <!-- end of grouping -->'.PHP_EOL;
	return;
	}


function amr_configure_list($current_list) {
global $current_list, $amr_options;
	?>
<p>
<a href="#limits"><?php _e('Maximums:', 'amr-ical-events-list'); ?></a> 
| <a href="#formats1"><?php _e('Date and time formats:', 'amr-ical-events-list'); ?></a>
| <a href="#headings"><?php _e('Headings', 'amr-ical-events-list'); ?></a>
| <a href="#fields"><?php _e('Fields', 'amr-ical-events-list'); ?></a>
| <a href="#calprop"><?php _e('Calendar Properties', 'amr-ical-events-list'); ?></a>
| <a href="#icalgroupings"><?php _e('Groupings', 'amr-ical-events-list'); ?></a>
| <a href="#components"><?php _e('Components', 'amr-ical-events-list'); ?></a>
</p>
<?php
		echo PHP_EOL.'<fieldset class="List" >' ;
		if (!(isset($amr_options['listtypes'])) )  echo 'Error in saved options';
		else {
			amrical_mimic_meta_box('general','', 'amrical_general_form' , false);
			amrical_mimic_meta_box('limits',__('Define maximums:', 'amr-ical-events-list'), 'amrical_limits' , false);
			amrical_mimic_meta_box('formats1',__(' Define date and time formats:', 'amr-ical-events-list'), 'amrical_formats' , false);

			if (!(in_array($amr_options['listtypes'][$current_list]['general']['ListHTMLStyle'],
				array('smallcalendar','largecalendar', 'weekscalendar')))) {
				//amrical_col_headings();
				amrical_mimic_meta_box('headings',	__('Column Headings:','amr-ical-events-list'), 'amrical_col_headings' , false);
			}

			
			amrical_mimic_meta_box('fields',__('Fields', 'amr-ical-events-list'), 'amrical_fields' , false);
			amrical_mimic_meta_box('calprop',__('Calendar Properties', 'amr-ical-events-list'), 'amrical_calpropsoption' , false);

			if (!(in_array($amr_options['listtypes'][$current_list]['general']['ListHTMLStyle'],
				array('smallcalendar','largecalendar', 'weekscalendar')))) {
				//amrical_col_headings();
				amrical_mimic_meta_box('icalgroupings',__('Define grouping:', 'amr-ical-events-list'), 'amrical_groupingsoption' , false);
			}
			amrical_mimic_meta_box('components',__('Components', 'amr-ical-events-list'), 'amrical_componentsoption' , false);
		}
		echo PHP_EOL.'</fieldset>  <!-- end of list type -->'.PHP_EOL;

	}

function amr_ical_general_form() {
	global $current_list,
		$amr_csize,
		$amr_calprop,
		$amr_formats,
		$amr_limits,
		$amr_compprop,
		$amr_groupings,
		$amr_components,
		$amr_options,
		$amr_globaltz;
		
		$amr_options = amr_getset_options(false);
		
		$amr_ical_image_settings = get_option('amr-ical-images-to-use');
		if (empty ($amr_ical_image_settings['images_size']))
			$imagesize = '16';
		else
			$imagesize = (int) ($amr_ical_image_settings['images_size']);

		$gentext = __('General Options', 'amr-ical-events-list'); 
		$styletext = __('Styling and Images', 'amr-ical-events-list');
		$advtext = __('Advanced','amr-ical-events-list');
		$managetext = __('Manage Event List Types','amr-ical-events-list');
		
		echo '<div><p>
		<a title="'.$gentext .'" href="#amrglobal">'
		.$gentext.'</a> | '
		.'<a title="'.$styletext .'" href="#amrstyle">'
		.$styletext.'</a> | '
		.'<a title="'.$advtext .'" href="#amradvanced">'
		.$advtext
		.'</a> | '
		.'<a title="'.$managetext.'" href="'.admin_url('admin.php?page=manage_event_listing').'">'
		.$managetext
		.'</a></p>
		
		<fieldset id="amrglobal"><h3>'.$gentext.'</h3>';
		echo '<div class="postbox" style="padding:1em 2em; width: 600px;">

			<label for="noeventsmessage">';
		_e('Message if no events found: ', 'amr-ical-events-list');
			?></label><br />
			<input class="wide regular-text" type="text" id="noeventsmessage" name="noeventsmessage"
			<?php if (isset($amr_options['noeventsmessage']) and ($amr_options['noeventsmessage']))
				{echo 'value="'.$amr_options['noeventsmessage'].'"';}?>/>
			<br />
			<label for="lookmoresmessage">
			<?php _e('Look for more events message: ', 'amr-ical-events-list');
			?></label><br />
			<input class="wide regular-text" type="text" id="lookmoremessage" name="lookmoremessage"
			<?php 
			if (isset($amr_options['lookmoremessage']) and ($amr_options['lookmoremessage']))
				{echo 'value="'
				.$amr_options['lookmoremessage']
				.'"';}?>/>
			<br />
			<label for="lookprevmessage">
			<?php _e('Look for previous events message: ', 'amr-ical-events-list');
			?></label><br />
			<input class="wide regular-text" type="text" id="lookprevmessage" name="lookprevmessage"
			<?php if (isset($amr_options['lookprevmessage']) and ($amr_options['lookprevmessage']))
				{echo 'value="'.$amr_options['lookprevmessage'].'"';}?>/>
			<br />
			<label for="resetmessage">
			<?php _e('Reset events message: ', 'amr-ical-events-list');
			?></label><br />
			<input class="wide regular-text" type="text" id="resetmessage" name="resetmessage"
			<?php if (isset($amr_options['resetmessage']) and ($amr_options['resetmessage']))
				{echo 'value="'.$amr_options['resetmessage'].'"';}?>/>
			<br />
			<label for="freebusymessage">
			<?php _e('Free busy text: ', 'amr-ical-events-list');
			_e(' - replaces the summary text (Busy)in a VFREEBUSY component.', 'amr-ical-events-list');
			?></label><br />
			<input class="wide regular-text" type="text" id="freebusymessage" name="freebusymessage"
			<?php if (isset($amr_options['freebusymessage']) and ($amr_options['freebusymessage']))
				{echo 'value="'.$amr_options['freebusymessage'].'"';}?>/>
			<br />

			<label for="usehumantime">
			<input type="checkbox" id="usehumantime" name="usehumantime" value="usehumantime"
			<?php if (isset($amr_options['usehumantime']) and ($amr_options['usehumantime']))  {echo 'checked="checked"';}
			?>/> <?php _e('Use human time like midday, midnight', 'amr-ical-events-list'); ?></label>			
			
			</div>
			</fieldset>
			<fieldset id="amrstyle"><h3><?php echo $styletext; ?></h3>
			<div class="postbox" style="padding:1em 2em; width: 600px;">
				<label for="do_groupingjs">
				<input type="checkbox" id="do_grouping_js" name="do_grouping_js" value="do_grouping_js"
				<?php if (!empty($amr_options['do_grouping_js']) and ($amr_options['do_grouping_js']))  {echo 'checked="checked"';}
				?>/> <?php _e('Use javascript to collapse event groupings', 'amr-ical-events-list');
				?></label>
				<br />

				<label for="own_css">
				<input type="checkbox" id="own_css" name="own_css" value="own_css"
				<?php if (isset($amr_options['own_css']) and ($amr_options['own_css']))  {echo 'checked="checked"';}
				?>/> <?php _e('Use my theme css, not plugin css', 'amr-ical-events-list');
				$files = amr_get_css_url_choices();
				?></label>
				<br />
				<label for="no_images">
				<input type="checkbox" id="no_images" name="no_images" value="true"
				<?php if (isset($amr_options['no_images']) and ($amr_options['no_images']))  {echo 'checked="checked"';}
				?>/><?php _e(' No images (tick for text only)', 'amr-ical-events-list');
				?></label>
				<br />
				<br />

				<label for="images_size16">
				<?php _e('Image icon size:', 'amr-ical-events-list');	?>
				<input type="radio" id="images_size16" name="images_size" value="16"
				<?php if ($imagesize == '16')  {echo 'checked="checked"';}
				?>/><?php _e('16', 'amr-ical-events-list');	?></label>
				<label for="images_size32">
				<input type="radio" id="images_size32" name="images_size" value="32"
				<?php if ($imagesize == '32')  {echo 'checked="checked"';}
				?>/><?php _e('32', 'amr-ical-events-list');
				?></label>
			<p>
			<label for="cssfile"><?php _e('Choose plugin default css or choose a custom css and edit it.', 'amr-ical-events-list'); ?></label>
			<select id="cssfile" name="cssfile"class="wide" ><?php
				if (empty ($files)) echo AMR_NL.' <option value="">'.__('No css files found in css directory ', 'amr-ical-events-list').$dir.' '.$files.'</option>';
				else foreach ($files as $ifile => $file) {
					echo AMR_NL.' <option value="'.$file.'"';
					if (isset($amr_options['cssfile']) and ($amr_options['cssfile'] == $file)) echo ' selected="selected" ';
					echo '>'.$file.'</option>';
				}
			?></select></p>

</div>
</fieldset>
<fieldset id="amradvanced">
<h3><?php _e('Advanced:','amr-ical-events-list');
?></h3><div class="postbox" style="padding:1em 2em; width: 600px;">
<p>
<?php printf(__('Your php version is: %s','amr-ical-events-list'),  phpversion());	?></p>
<?php
if (version_compare('5.3', PHP_VERSION, '>')) {
	echo( '<p><b>'.__('Minimum Php version 5.3 required for events cacheing. ','amr-ical-events-list').	'</b>');
	_e('Cacheing of generated events for re-use on same page (eg: widget plus list) will not be attempted. ','amr-ical-events-list') ;
	_e('Apparently objects do not serialise correctly in php < 5.3.','amr-ical-events-list') ;
	echo '<br /></p>';
	}
			
	echo '<p>';
		_e('Http timeout for external ics files:', 'amr-ical-events-list');
		$options = array('5','8','10','20','30','1');
		if (!isset($amr_options['timeout'])) 
			$amr_options['timeout'] = 5;
		?><br />
		<label for="timeout"><?php _e('Choose seconds before timeout for each ics file fetch', 'amr-ical-events-list'); ?></label>
			<select id="timeout" name="timeout" ><?php
			foreach ($options as $i=> $sec) {
	
				echo '<option value="'.$sec.'"';
				if (isset($amr_options['timeout']) and ($amr_options['timeout'] == $sec)) echo ' selected="selected" ';
				echo '>'.$sec.'</option>';
			}
			?></select><br />
		<em><?php _e('Warning - 30 seconds is a long time! Let it use cache rather if things are slow', 'amr-ical-events-list'); ?></em></p><br />	
	
<?php 	
	
		amr_check_timezonesettings();
		$now = date_create('now', $amr_globaltz);
		?><p><b>Deprecated, do not change permanently:</b></p><p><?php
		_e('Choose date localisation method:', 'amr-ical-events-list');
		?><a href="http://icalevents.com/2044-date-and-time-localisation-in-wordpress/"><b>?</b></a><br />
			<br /><label for="no_localise"><input type="radio" id="no_localise" name="date_localise" value="none" <?php if ($amr_options['date_localise'] === "none") echo ' checked="checked" '; ?> />
			<?php _e('none', 'amr-ical-events-list'); echo ' - '.amr_format_date('r', $now); ?></label>
			<br /><label for="am_localise"><input type="radio" id="am_localise" name="date_localise" value="amr" <?php if ($amr_options['date_localise'] === "amr") echo ' checked="checked" '; ?> />
			<?php _e('amr', 'amr-ical-events-list'); echo ' - '.amr_date_i18n('r', $now); ?></label>
			<br /><label for="wp_localise"><input type="radio" id="wp_localise" name="date_localise" value="wp" <?php if ($amr_options['date_localise'] === "wp") echo ' checked="checked" '; ?> />
			<?php _e('wp', 'amr-ical-events-list'); echo ' - '.amr_wp_format_date('r', $now, false);?></label>
			<br /><label for="wpg_localise"><input type="radio" id="wpg_localise" name="date_localise" value="wpgmt" <?php if ($amr_options['date_localise'] === "wpgmt") echo ' checked="checked" '; ?> />
			<?php _e('wpgmt', 'amr-ical-events-list'); echo ' - '.amr_wp_format_date('r', $now, true);?></label>
		</p>	

		</div>
		</fieldset>
	</div>

<?php
	}
?>