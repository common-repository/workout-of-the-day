nothin<?php
/* 
Plugin Name: Workout of the Day
Plugin URI: http://www.workoutbox.com
Description: Daily Workouts from the personal training team at WorkoutBOX.
Version: 1.04
Author: WorkoutBOX Workouts
Author URI: http://www.workoutbox.com

QBKL Media Studio - Custom, smart WordPress themes design
http://QBKL.net

Version History

v1.1
-------
- Added option to display post dates;
- Added dashboard widget for plugin version tracking (Only available for WP 2.7+ users).

*/


$wpVersion = get_bloginfo('version');
if($wpVersion >= '2.7') {

	/*function get_version($url)
	{
		$ch = curl_init();

		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_HEADER, 0);

		ob_start();

		curl_exec ($ch);
		curl_close ($ch);
		$string = ob_get_contents();

		ob_end_clean();
	   
		return $string;    
	}*/

	function workout_of_the_day_dashboard_widget(){
		$currentVersion = '1.0';
		//$versionFile = 'http://blogsessive.com/wp-content/uploads/lpbc-update/version.txt';
		//$latestVersion = get_version($versionFile);
		?>
		<p>Your plugin version is <strong><?php echo $currentVersion; ?></strong>.	The latest version is <strong><?php echo $latestVersion; ?></strong>.<br />
		<?php
		if ($currentVersion == $latestVersion) { $lpbcUpdate = "Your plugin is <strong>up to date</strong>!"; }
		else {
			$lpbcUpdate = 'Your plugin is <strong>NOT up to date</strong>. You can download the latest version <a href="http://www.workoutbox.com" target="_blank"><strong>here</strong></a>';
		}
		$lpbcUpdate .= '</p><p>Thanks for using this plugin and remember to visit <a href="http://www.workoutbox.com" target="_blank"><strong>Blogsessive.com</strong></a> for more blogging tools and tips!</p>';
		echo $lpbcUpdate;
	}

	function insert_workout_of_the_day_dash(){
		wp_add_dashboard_widget('mydash', 'Work Out Of The Day', 'workout_of_the_day_dashboard_widget');
	}

	add_action('wp_dashboard_setup', 'insert_workout_of_the_day_dash');
}

add_shortcode('workoutbox', 'workout_of_the_day_shortcode');

function workout_of_the_day_output($categories = "", $posts = "1", $date = false, $excerpts = false, $length = "36", $more = "") {
	global $wpdb;

	$categories = explode(",",$categories);

	$exclude = "";
	$include = "";
	foreach ($categories as $catID) {
		$catID = trim($catID);
		
		if ($catID < 0) {
			if ($exclude!="") {
				$exclude .= ",";
			}
			$exclude .= abs($catID);
		}

		if ($catID > 0) {
			if ($include!="") {
				$include .= ",";
			}
			$include .= $catID;
		}
	}

	if (isset($exclude) && $exclude!="") { $excludeQuery = " AND wterms.term_id NOT IN (".$exclude.")"; }
	if (isset($include) && $include!="") { $includeQuery = " AND wterms.term_id IN (".$include.")"; $excludeQuery = ""; }

	if(isset($posts)) {
		if(is_numeric($posts)) {
			$posts = abs(round($posts));
			$limit = "&numberposts=".$posts;
		}

		if(strtolower($posts)=="all") {
			$limit = "&numberposts=-1";
		}
	}

	$catQuery = $wpdb->get_results("SELECT * FROM $wpdb->terms AS wterms INNER JOIN $wpdb->term_taxonomy AS wtaxonomy ON ( wterms.term_id = wtaxonomy.term_id ) WHERE wtaxonomy.taxonomy = 'category' AND wtaxonomy.parent = 0 AND wtaxonomy.count > 0".$excludeQuery.$includeQuery);

	$output = '<div class="lpbcArchive"><div>';

	$catCounter = 0;

	foreach ($catQuery as $category) {

		$catCounter++;

		$catStyle = '';
		if (is_int($catCounter / 2)) $catStyle = ' class="lpbcAlt"';

		$catLink = get_category_link($category->term_id);

		$output .= '<div'.$catStyle.' style="list-style: none !important;"><h4><a href="'.$catLink.'" title="'.$category->name.'">'.$category->name.'</a></h4>';
		$output .= '<div>';

		$postQuery = get_posts('order=DESC&orderby=ID'.$limit.'&category='.$category->term_id);

		$queriedCount = count($postQuery);

		foreach($postQuery as $postinfo):
			$thedate = "";
			if ($date == true) {
				$thedate = '<span class="lpbcDate"> - '.apply_filters('the_date',substr($postinfo->post_date,0,10)).'</span>';
			}
			$output .= '<div><a href="'.get_permalink($postinfo->ID).'" rel="bookmark" title="'.apply_filters('the_title',$postinfo->post_title).'" class="lpbcTitle">'.apply_filters('the_title',$postinfo->post_title).'</a>'.$thedate;

			if ( empty($post->post_excerpt) )
				$excerpt = workout_of_the_day_excerpt($postinfo->post_content,$length);
			else
				$excerpt = apply_filters('the_excerpt', $postinfo->post_excerpt);
				$excerpt = str_replace(']]>', ']]&gt;', $excerpt);
				$excerpt = strip_tags($excerpt);

				if (isset($excerpts) && $excerpts==true)
					$output .= '<br />'.$excerpt;

			$output .= '</div>';
		endforeach;
		if(!isset($more) || $more == NULL || $more == "") {
			$currentmore = 'More from <strong>'.$category->name.'</strong>';
		}

		else {
			$currentmore = str_replace ('%name%',$category->name,$more);
		}

		if (strtolower($posts)!="all") {
			if(abs($queriedCount) >= abs($posts)) { $output .= '<div><a href="'.$catLink.'" title="'.$category->name.'">'.$currentmore.'</a></div>'; }
		}
		$output .= '</div>';
		$output .= '</div>';
	}
	$output .= '</div></div>';
	return $output;
}

function workout_of_the_day_widget_output($categories = "", $posts = "5", $date = false, $excerpts = false, $length = "36", $more = "", $linklove = true) {
	global $wpdb;

	$categories = explode(",",$categories);

	$exclude = "";
	$include = "";
	//var_dump($categories);
	foreach ($categories as $catID) {
		$catID = trim($catID);
		
		if ($catID < 0) {
			if ($exclude!="") {
				$exclude .= ",";
			}
			$exclude .= abs($catID);
		}

		if ($catID > 0) {
			if ($include!="") {
				$include .= ",";
			}
			$include .= $catID;
		}
	}

	if (isset($exclude) && $exclude!="") { $excludeQuery = " AND wterms.term_id NOT IN (".$exclude.")"; }
	if (isset($include) && $include!="") { $includeQuery = " AND wterms.term_id IN (".$include.")"; $excludeQuery = ""; }

	if(isset($posts)) {
		if(is_numeric($posts)) {
			$posts = abs(round($posts));
			$limit = "&numberposts=1";
		}

		if(strtolower($posts)=="all") {
			$limit = "&numberposts=-1";
		}
	}

	$catQuery = $wpdb->get_results("SELECT * FROM $wpdb->terms AS wterms INNER JOIN $wpdb->term_taxonomy AS wtaxonomy ON ( wterms.term_id = wtaxonomy.term_id ) WHERE wtaxonomy.taxonomy = 'category' AND wtaxonomy.parent = 0 AND wtaxonomy.count > 0".$excludeQuery.$includeQuery);

	$output = '<div>';

	$catCounter = 0;

	foreach ($catQuery as $category) {

		$catCounter++;

		$catLink = get_category_link($category->term_id);

		$output .= '<div class="lpbcWidgetCategory">';
		$output .= '<div>';

		$postQuery = get_posts('order=DESC&orderby=ID'.$limit.'&category='.$category->term_id);

		$queriedCount = count($postQuery);

		foreach($postQuery as $postinfo):
			$thedate = "";
			if ($date == true) {
				$thedate = '<br /><span class="lpbcWidgetDate">'.apply_filters('the_date',substr($postinfo->post_date,0,10)).'</span>';
			}
			$output .= '<div class="lpbcWidgetPost_top"><a href="'.get_permalink($postinfo->ID).'" rel="bookmark" title="'.apply_filters('the_title',$postinfo->post_title).'" class="lpbcWidgetPostTitle">'.apply_filters('the_title',$postinfo->post_title).'</a></div><br />'.$thedate;

			if ( empty($post->post_excerpt) )
				$excerpt = workout_of_the_day_excerpt($postinfo->post_content,$length);
			else
				$excerpt = apply_filters('the_excerpt', $postinfo->post_excerpt);
				$excerpt = str_replace(']]>', ']]&gt;', $excerpt);
				$excerpt = strip_tags($excerpt);

				if (isset($excerpts) && $excerpts==true)
					$output .= '<br />'.$excerpt;

			$output .= '</div>';
		endforeach;
		if(!isset($more) || $more == NULL || $more == "") {
			$currentmore = 'read on &raquo;';
		}

		else {
			$currentmore = 'read on &raquo;';
		}

		if (strtolower($posts)!="all") {
			if(abs($queriedCount) >= abs($posts)) { $output .= '<span class="lpbcWidgetMore"><a href="'.get_permalink($postinfo->ID).'" title="'.$category->name.'">'.$currentmore.'</a></span>'; }
		}
		$output .= '</div>';
		$output .= '</div>';
		break;
	}
	if($linklove == true) {
		$output .= '<div class="lpbcLinkLove"><small>Plugin by <a href="http://blogsessive.com" title="Blog Tips" target="_blank">Blogsessive</a></small></div>';
	}
	$output .= '</div><br /><div class="wid_bott"><span> Make online fitness simple with<a href='. $_SERVER['SERVER_NAME'].'>WorkoutBox</a></span></div>';
	return $output;
}

function workout_of_the_day($args = '') {
	$default = array(
		"categories" => "",
		"posts" => "1",
		"date" => false,
		"excerpts" => false,
		"length" => "30",
		"more" => "");

	$new_args = wp_parse_args( $args, $default );

	$output = workout_of_the_day_output($new_args["categories"],$new_args["posts"],$new_args["date"],$new_args["excerpts"],$new_args["length"],$new_args["more"]);
	echo $output;
}

function workout_of_the_day_widget($args = '') {
	$default = array(
		"categories" => "",
		"posts" => "1",
		"date" => false,
		"excerpts" => false,
		"length" => "36",
		"more" => "",
		"linklove" => false
	);

	$new_args = wp_parse_args( $args, $default );

	$output = workout_of_the_day_widget_output($new_args["categories"],$new_args["posts"],$new_args["date"],$new_args["excerpts"],$new_args["length"],$new_args["more"],$new_args["linklove"]);
	echo $output;
}


function workout_of_the_day_shortcode($atts) {
	extract(shortcode_atts(array(
		"categories" => '',
		"posts" => '1',
		"date" => false,
		"excerpts" => false,
		"length" => '36',
		"more" => ''
	), $atts));

	$output = workout_of_the_day_output($categories,$posts,$date,$excerpts,$length,$more);

	return $output;
}


function workout_of_the_day_excerpt($text, $length) {

	$text = strip_shortcodes( $text );

	$text = apply_filters('the_content', $text);
	$text = str_replace(']]>', ']]&amp;amp;gt;', $text);
	$text = strip_tags($text);
	$excerpt_length = apply_filters('excerpt_length', $length);
	$words = explode(' ', $text, $excerpt_length + 1);
	if (count($words) > $excerpt_length) {
		array_pop($words);
		array_push($words, '...');
		$text = implode(' ', $words);
	}
	return $text;
}

function workout_of_the_day_widget_generator() {
$options = get_option('lpbcWidget');

	if($options['lpbcWidgetTitle']) {
		$title = attribute_escape($options['lpbcWidgetTitle']);
	}
	else {
		$title = "Daily Fitness Tips";
	}

	//echo '<div class="widget lpbcWidget"><h2>'.$title.'</h2>';
	$output = '<div class="widget lpbcWidget"> <span class="logo_wid">&nbsp;</span><br />';


	if($options['lpbcWidgetCats']) {
		$categories = $options['lpbcWidgetCats'];
	}
	else {
		$categories = '';
	}

	if($options['lpbcWidgetPosts']) {
		$posts = attribute_escape($options['lpbcWidgetPosts']);
	}
	else {
		$posts = "5";
	}

	if($options['lpbcWidgetDate']=="on") {
		$date = false;
	}
	else {
		$date = false;
	}

	if($options['lpbcWidgetExcerpt']=="on") {
		$excerpts = true;
	}
	else {
		$excerpts = true;
	}

	if($options['lpbcWidgetExLength']) {
		$length = "15";
	}
	else {
		$length = "15";
	}

	if($options['lpbcWidgetArchiveLink']) {
		$more = attribute_escape($options['lpbcWidgetArchiveLink']);
	}
	else {
		$more = '';
	}

	if($options['lpbcWidgetLink']=="on") {
		$linklove = false;
	}
	else {
		$linklove = false;
	}
	include_once('workout_day_post.php');
}

function workout_of_the_day_widget_control(){
	$doc = new DOMDocument();
	$doc->load("http://www.workoutbox.com/blog/daily_fitness_tips/recent.php");
	$categories = $doc->getElementsByTagName("category");
	$catquery = array();
	$i = 0;
	foreach($categories as $cate){
		$cat_id = $cate->getElementsByTagName("cat_id");
		$cat_id = $cat_id->item(0)->nodeValue;
	  
		$cat_name = $cate->getElementsByTagName("cat_name");
		$cat_name = $cat_name->item(0)->nodeValue;
		$catquery[$i]['term_id'] = $cat_id;
		$catquery[$i]['name'] = $cat_name;
		$i++;
	}
	
	$options = $newoptions = get_option('lpbcWidget');
	if ( $_POST['menu-submit'] ) {
		$newoptions['lpbcWidgetTitle'] = strip_tags(stripslashes($_POST['lpbcWidgetTitle']));

		if ($_POST['lpbcWidgetCats']){
			$lpbcCats = "";
			foreach ($_POST['lpbcWidgetCats'] as $lpbcCat){ $lpbcCats .= $lpbcCat.",";}

			$lpbcCats = rTrim($lpbcCats,',');
			$newoptions['lpbcWidgetCats'] = $lpbcCats;
		}

	    $newoptions['lpbcWidgetPosts'] =  strip_tags(stripslashes($_POST['lpbcWidgetPosts']));

		$newoptions['lpbcWidgetDate'] = $_POST['lpbcWidgetDate'];

		$newoptions['lpbcWidgetExcerpt'] = $_POST['lpbcWidgetExcerpt'];

	    $newoptions['lpbcWidgetExLength'] = strip_tags(stripslashes($_POST['lpbcWidgetExLength']));

		$newoptions['lpbcWidgetArchiveLink'] = strip_tags(stripslashes($_POST['lpbcWidgetArchiveLink']));

		$newoptions['lpbcWidgetLink'] = $_POST['lpbcWidgetLink'];
	}
	if ( $options != $newoptions ) {
	    $options = $newoptions;
	    update_option('lpbcWidget', $options);
		
	}

	$lpbcWidgetTitle = attribute_escape($options['lpbcWidgetTitle']);
	$lpbcWidgetCats = explode(',',$options['lpbcWidgetCats']);
	$lpbcWidgetPosts = $options['lpbcWidgetPosts'];
	if ($options['lpbcWidgetDate'] == 'on') {
		$lpbcWidgetDateCheck = ' checked="checked"';
	}
	if ($options['lpbcWidgetExcerpt'] == 'on') {
		$lpbcWidgetExcerptCheck = ' checked="checked"';
	}
	$lpbcWidgetExLength = $options['lpbcWidgetExLength'];
	$lpbcWidgetArchiveLink = attribute_escape($options['lpbcWidgetArchiveLink']);
	if ($options['lpbcWidgetLink'] == 'on') {
		$lpbcWidgetLink = ' checked="checked"';
	}
	?>
	<input type="hidden" id="lpbcWidgetTitle" name="lpbcWidgetTitle" class="widefat" value="Daily Fitness Tips" />
	<p><label for="lpbcWidgetCats"><strong>Select the type of fitness tips to display:</strong></label><br />
	<?php
	global $wpdb;
	//$catquery = $wpdb->get_results("SELECT * FROM $wpdb->terms AS wterms INNER JOIN $wpdb->term_taxonomy AS wtaxonomy ON ( wterms.term_id = wtaxonomy.term_id ) WHERE wtaxonomy.taxonomy = 'category' AND wtaxonomy.parent = 0 AND wtaxonomy.count > 0");
	
	foreach ($catquery as $category) {
		$checked = '';
		if (in_array($category['term_id'],$lpbcWidgetCats)) {
		
			$checked = ' checked="checked"';
		}
		?>
		<input type="checkbox" name="lpbcWidgetCats[]" value="<?php echo $category['term_id']; ?>"<?php echo $checked; ?> /> <?php echo $category['name']; ?><br />
	<?php } ?>
	</p>
	<input type="hidden" id="lpbcWidgetPosts" name="lpbcWidgetPosts" class="widefat" style="width: 11%;" value="1" />
	<input type="hidden" id="lpbcWidgetDate" name="lpbcWidgetDate" />
	<input type="hidden" id="lpbcWidgetExcerpt" name="lpbcWidgetExcerpt" <?php echo $lpbcWidgetExcerptCheck; ?> /> 
	<input type="hidden" id="lpbcWidgetExLength" name="lpbcWidgetExLength" class="widefat" style="width: 11%;" value="36" />
	<input type="hidden" id="lpbcWidgetArchiveLink" name="lpbcWidgetArchiveLink" class="widefat" value="" />
	<input type="hidden" id="lpbcWidgetLink" name="lpbcWidgetLink" "checked" />
	<input type="hidden" id="menu-submit" name="menu-submit" value="1" />
	<?php
}

function workout_of_the_day_widget_init() {
	add_action('wp_head','workout_of_the_day_css');
	register_sidebar_widget("Workout Of The Day", "workout_of_the_day_widget_generator");
	//register_widget_control("Daily Fitness Tips","workout_of_the_day_widget_control");
}

function workout_of_the_day_css() {
	echo '<link rel="stylesheet" type="text/css" href="'.trailingslashit(get_option('siteurl')).'wp-content/plugins/workout-of-the-day/workout_of_the_day.css" />'."\n";
	
}

add_action("plugins_loaded", "workout_of_the_day_widget_init");
?>