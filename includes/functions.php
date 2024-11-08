<?php //commonly useful functions
function amrical_mimic_meta_box($id, $title, $callback , $toggleable = true) {
//	global $screen_layout_columns;

	//	$style = 'style="display:none;"';
		//$h = (2 == $screen_layout_columns) ? ' has-right-sidebar' : '';
?>
<div style="clear:both;" class="metabox-holder1">
<div class="postbox-container" >
<div class="meta-box-sortables" style="min-height: 10px;">
<div id="' . $id . '" class="postbox">
<div class="inside">
<h3 id="<?php echo $id; ?>"><?php echo $title; ?></h3>
<?php call_user_func($callback); ?>
</div></div></div></div></div>
<?php

	}

function amr_need_this_field($field) { // can we do away with or replace this now - see below 20151018
	global  $amr_fields_needed;
	//$amr_options;	global $amr_listtype;
	
	//20151018 if (!empty($amr_options['listtypes'][$amr_listtype]['compprop'][$field]['column'])) 
	//amr 20151012 - isset not enough, must check that column is not zero

	if (in_array($field, $amr_fields_needed))
		return true;
	else 
		return false;
}

function amr_extract_fields($amr_event_columns) { // just get array of fields needed
global $amr_eventinfo_settings;


	$amr_fields_needed = array();
	
	if (!empty($amr_eventinfo_settings)) { // we are doing a single post
		
		foreach($amr_eventinfo_settings['eventinfo'] as $fld=>$specs) {
			$amr_fields_needed[] = $fld;
		}	
	}	
	//else echo 'WHY EMPTY';
	
	foreach ($amr_event_columns as $col => $fields) {
		foreach ($fields as $field =>$data) {
			if (!in_array($field, // these are calculated or derived fields
				array(
				'SUMMARY',
				'DESCRIPTION',
				'subscribeevent',
				'subscribeseries',
				'addevent',
				'post_tag',
				'category')))
				$amr_fields_needed[] = $field;
		}	
	}
	
	foreach ($amr_fields_needed as $i=>$fld) 
		$amr_fields_needed[$i] = '_'.$fld;
	
	if (function_exists('ical_location_values')  // is in amr-events for events with locations
		and (in_array('_GEO', $amr_fields_needed) or
		in_array('_map', $amr_fields_needed) or
		in_array('_place', $amr_fields_needed) or
		in_array('_LOCATION', $amr_fields_needed )))
		{

		$locationfields = ical_location_values();
		$amr_fields_needed = array_merge ($amr_fields_needed , $locationfields );
		
	}
	// what if using other plugin ? eg geomashup


	
	$amr_fields_needed = array_merge ($amr_fields_needed , 
	array('_VEVENT',
		'_tztype',
		'_timezone',
		'_allday',
		'_DTSTART',
		'_EventDate',
		'_RRULE',
		'_EXDATE',
		'_RDATE',
		'_DURATION',
		'_last'));
	
	//var_dump($amr_fields_needed);
	
return $amr_fields_needed; 
}

function amr_create_date_time ($datetimestring, $tzobj) { // date time create with exception trap to avoid fatal errors
		try {	$dt = new DateTime($datetimestring,	$tzobj); }
		catch(Exception $e) {
			$text = '<br />Unable to create DateTime object from '.$datetimestring.' <br />'.$e->getMessage();
			amr_tell_admin_the_error ($text);
			return (false);
		}
	return ($dt);
}	

function amr_newDateTime($text='') {  // create new datetime object with the global timezone
// because wordpress insists that the php default must stay as UTC
global $amr_globaltz;
	//$d = new DateTime($text,$amr_globaltz);
	$d = amr_create_date_time($text,$amr_globaltz);  // traps exceptions
	return ($d);

}
 
function amr_a_nested_event_shortcode () { // in case we have a dummy - yes truly
	global $amr_been_here;
	
	if (empty($amr_been_here) or (!($amr_been_here))) { 
		$amr_been_here = true;
		return false;
		}
	else {	
		echo '<hr />Event List Execution halted - looping detected. Did some one enter an event list shortcode into event content?  That will cause a loop for sure.';
		return true;
		}
}
 
function amr_convert_date_string_to_object ($a) {   // year month day

	if (checkdate(substr($a,4,2), /* month */
			substr($a,6,2), /* day*/
			substr($a,0,4)) /* year */ )
			$e = amr_newDateTime($a);
	else {
		_e('Invalid Event Date', 'amr-ical-events-list');
		}
	return ($e);				
}
 
function amr_tell_admin_the_error ($text) {
	// only report the error to admin if logged in ?
	// else report with comment
	if (function_exists('is_user_logged_in') // sometimes not yet loaded ???
	and (current_user_can('manage_options')) ) { 
		echo '<br /><b>Message displayed to logged in admin only:</b><br />'.$text.'<br />';
	}
	else if (WP_DEBUG)  
		echo '<br /><b>Message (only displayed if WP_DEBUG):</b><br />'.$text;
	else	
		echo '<!--'.$text.'-->'; // give some notification at least rather than just failing silently
	
	}
	
function amr_tell_error ($text) {
	echo '<br /><p><b>'.$text.'</b><p>';
	}	
 
function amr_external_url ($url) {
	// if it is an external url, then open in new window
		if (stristr( $url, get_bloginfo('url'))) {
			return false;
		}
		else	return(true);
	}
 
function amr_debug_time () {  // track php runtime if debugging
	global $amr_start_time_track,$amr_last_time_track;
	if (isset($_GET['debugtime'])) {
		$amr_start_time_track = microtime();
		$amr_last_time_track = $amr_start_time_track;
		echo '<br />Starting tracking now after plugins loaded '.$amr_start_time_track;
		echo '<br />Note: measuring runtime may affect the runtime too, as will debug messages.  if using ics file, check timing on refresh &refresh';
	}
}
 
function amr_track_run_time ($text='') {  // track php runtime if debugging
	global $amr_start_time_track;
	global $amr_last_time_track;
	if (isset($_GET['debugtime'])) {
		$end = microtime();
		$startt = (float) array_sum(explode(' ',$amr_start_time_track));
		$endt = (float) array_sum(explode(' ',$end));
		$lastt = (float) array_sum(explode(' ',$amr_last_time_track));
		$amr_last_time_track = $end;
		echo '<br />Since start '. sprintf("%.4f", ($endt-$startt))." seconds.  Since last:"
		.sprintf("%.4f", ($endt-$lastt)).' seconds.  In "'.$text.'"';

	}
}	
 
function amr_memory_convert($size) {
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
 }
  
function amrical_mem_debug($text) {
	if (isset($_GET['memdebug'])) {
		$mem = memory_get_usage (true);
		$peak = memory_get_peak_usage (true);
		echo '<br />memory: '.amr_memory_convert($mem).' peak:'.amr_memory_convert($peak).' '.$text;
		//echo '<br />memory: '.$mem.' peak:'.$peak.' '.$text;
	}
	if (isset($_GET['debugtime'])) { amr_track_run_time($text);}	
 }
 
if (!function_exists('esc_textarea') ) {
	function esc_textarea( $text ) {
	$safe_text = htmlspecialchars( $text, ENT_QUOTES );
	}
}
 
function amr_check_for_wpml_lang_parameter ($link) {
 	if (isset($_REQUEST['lang'])) {
		$lang = $_REQUEST['lang'];
		$link = remove_query_arg( 'lang', $link );  //is there a wpml bug ? or wp bug, we are getting lang twice
		$link = add_query_arg( 'lang', $lang, $link );
		}
	return ($link);
}
 
function amr_clean_link() { /* get cleaned up version of current url remove other parameters */
global $page_id;
global $wp;

	//$link = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );    this is now returning a pagename query parameter on top of the permalink, which breaks things
	
	$link = get_permalink();

	/* not sure if we still need, but some folks with main eventlist on home page with other weird stuff on frontpage  had problems before, so keeping for now  */
	if (is_front_page() and (!empty($page_id))) {  /* don't use if page_id = 0  v4.0.28*/
		// use page_id not $post->id, because if have a post on home page too $post gets overwritten
		$link = add_query_arg(array('page_id'=>$page_id),$link);
	}

	return ($link);
}
 
function amr_allowed_html () {
//	return ('<p><br /><hr /><h2><h3><<h4><h5><h6><strong><em>');
	return (array(
		'br' => array(),
		'em' => array(),
		'span' => array(),
		'h1' => array(),
		'h2' => array(),
		'h3' => array(),
		'h4' => array(),
		'h5' => array(),
		'h6' => array(),
		'strong' => array(),
		'p' => array(),
		'abbr' => array(
		'title' => array ()),
		'acronym' => array(
			'title' => array ()),
		'b' => array(),
		'blockquote' => array(
			'cite' => array ()),
		'cite' => array (),
		'code' => array(),
		'del' => array(
			'datetime' => array ()),
		'em' => array (), 'i' => array (),
		'q' => array(
			'cite' => array ()),
		'strike' => array(),
		'div' => array(),
		'a' => array('href' => array(),'title' => array()),  // maybe add - need to see if quotes will work too
		'img' => array('src' => array(),'title' => array(),'alt' => array())  // not sure whether his is a good idea - rather use a pluggable format function.
		));
	}
	 
function amr_make_sticky_url($url) {

	$page_id = url_to_postid($url); // doesnt work on homepage - returns 0;
	
	if ($page_id == 0) {// must be static homepage? so force finding the id
		$page_id = get_option('page_on_front');
	}

	if (!$page_id) return false ;
	else {  
		$sticky_url  = add_query_arg('p',$page_id, get_bloginfo('url'));
		//p safer than page_id - wp 'keeps' the p, but forces pageid to base home if is front page
		return( $sticky_url) ;
	}
}
 
function amr_invalid_url() {
?><div class="error fade"><?php	_e('Invalid Url','amr-ical-events-list');?></div><?php
}
 
function amr_invalid_file() {
?><div class="error fade"><?php	_e('Invalid Url','amr-ical-events-list');?></div><?php
}
 
function amr_click_and_trim($text) { /* Copy code from make_clickable so we can trim the text */

	$text = make_clickable($text);    
	amr_trim_url($text);
	return $text;
}
 
function amr_trim_url(&$ret) { /* trim urls longer than 30 chars, but not if the link text does not have http */
	$links = explode('<a', $ret);
    $countlinks = count($links);

	for ($i = 0; $i < $countlinks; $i++) {
		$link    = $links[$i];
		$link    = (preg_match('#(.*)(href=")#is', $link)) ? '<a' . $link : $link;
		$begin   = strpos($link, '>');

		if ($begin) {

			$begin   = $begin + 1;

			$end     = strpos($link, '<', $begin);

			$length  = $end - $begin;

			$urlname = substr($link, $begin, $length);

			//$trimmed = (strlen($urlname) > 50 && preg_match('#^(http://|ftp://|www\.)#is', $urlname)) ? substr_replace($urlname, '.....', 30, -5) : $urlname;
			//$trimmed = str_replace('http://','',$trimmed);
			// 2016112016  Trim https as well
			$trimmed = (strlen($urlname) > 50 && preg_match('#^(http://|https://|ftp://|www\.)#is', $urlname)) ? substr_replace($urlname, '.....', 30, -5) : $urlname;
           $trimmed = str_replace(array('http://','https://'),'',$trimmed);

			$ret     = str_replace('>' . $urlname . '<', '>' . $trimmed . '<', $ret);
		}
	}
   	return ($ret);
}
 
if (!function_exists('amr_simpledropdown')) {
	function amr_simpledropdown($name, $options, $selected) {
//
		$html = '<select name=\''.$name.'\'>';
		foreach ($options as $i => $option) {
//
			$sel = selected($i, $selected, false); //wordpress function returns with single quotes, not double
			$html .= '<OPTION '.$sel.' label=\''.$option.'\' value=\''.$i.'\'>'.$option.'</OPTION>';
		}
		$html .= '</select>';
		return ($html);
	}
}

function amr_check_set_debug() {  // obfuscate a bit so only admin or developer can run debug
	if (isset($_GET["debug"]) )   /* for debug and support - calendar data is public anyway, so no danger*/
			define('ICAL_EVENTS_DEBUG', true);
	else 	
			define('ICAL_EVENTS_DEBUG', false);
	}

function amr_is_trying_to_help() {  // obfuscate a bit so only admin or developer can run debug, &isme=mmdd sydney time
	$tz = timezone_open('Australia/Sydney');
	$now = date_create('now',$tz );
	if (isset($_REQUEST['isme']) and ($_REQUEST['isme']=== $now->format('md'))) return true;
	else return false;
}	
?>