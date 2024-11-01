<?php
echo workouts_of_the_day_widget_output();

function workouts_of_the_day_widget_output($categories = "", $posts = "1", $date = false, $excerpts = true, $length = "15", $more = "", $linklove = true) {
	global $wpdb;

	$categories = explode(",",$categories);
	$doc = new DOMDocument();
	$doc->load('http://feeds.workoutbox.com/workoutbox/workouts');
	$recent_post = $doc->getElementsByTagName("item");
	$title1 = array();
	foreach($recent_post as $key=>$recent){
		$title = $recent->getElementsByTagName("title");
		$title = $title->item(0)->nodeValue;
		$title1['title'][] = $title;
		$link = $recent->getElementsByTagName("link");
		$link = $link->item(0)->nodeValue;
		$title1['link'][] = $link;
		$description = $recent->getElementsByTagName("description");
		$description = $description->item(0)->nodeValue;
		$title1['description'][] = $description;
		//list($title, $link, $description) = explode(",", );
		//break;
	}
	$array_reverse_title = array_reverse($title1['title']);
	$array_reverse_link = array_reverse($title1['link']);
	$array_reverse_description = array_reverse($title1['description']);

	
	$date_of_year = date('z');
	// $timer = $date - 1;
	// $timer = 26;
	$total_count = count($array_reverse_title);
	$total_count = ($total_count - 1);

	$timer = ($date_of_year % $total_count);
	// $timer = 11;
	if(($timer < 0) || ($timer > $total_count) || ($array_reverse_title[$timer] == '')){
		$timer = 0 ;
	}
	//$timer = 11;
	for($i = $timer; $i < $total_count; $i++){
		$title = $array_reverse_title[$i];
		$link = $array_reverse_link[$i];
		$description = $array_reverse_description[$i];
		break;
	}

	$output = '<div class="Workout_day_Widget" style="margin-bottom:10px;"> <span class="logo_wid">&nbsp;</span><div style="margin-bottom:0px;">';

	if(!isset($more) || $more == NULL || $more == "") {
		$currentmore = 'read on &raquo;';
	}else {
		$currentmore = 'read on &raquo;';
	}
	
	preg_match('/WorkoutImageURL.+?src="(.+?)"/i', $description, $matches);	

	if(!empty($matches[1])){
		$str = $matches[1];
		 $img_http = substr($str, 0, 4);
		if($img_http != 'http'){
			if((substr($str, 0, 1)) == ("/")){
				$str = substr($str, 1);
				$image_path = "http://www.workoutbox.com/".$str;
			}else{
				$image_path = "http://www.workoutbox.com".$str;
			}
		}else{
			$image_path = $str;
		}
		$image = '<a href="'.$link.'" target="_blank" title="'.$title.'"><img width=75 height=75 src="'.$image_path.'"/></a>';
	}
	$description = strip_selected_tags($description, "<a>", true);
	$description = strip_selected_tags($description, "<workoutimageurl>");
	$description = substr($description, 0, 100)."...";
	if((strlen($title)) > 47){
		$title = substr($title, 0, 47)."...";
	}else{
		$title = $title;
	}
	$output .= '<div class="workout_day" style="margin-bottom:0px;">';
	$output .= '<div style="margin-bottom:0px;">';
	$output .= '<div class="workout_day_WidgetPost" style="margin-bottom:0px;"><div class="workout_day_WidgetPost_top" style="margin-bottom:5px;"><a target="_blank" href="'.$link.'" rel="bookmark" title="'.$title.'" class="workout_WidgetPostTitle">'.$title.'</a></div>';
	$output .= '<div class="workout_day_WidgetPost_wrap">';
		$output .= '<div class="workout_day_WidgetPost_imag">'.$image.'</div>';
		$output .= '<div class="workout_day_WidgetPost_desc">'.htmlspecialchars_decode($description, ENT_NOQUOTES).'</div>';
		$output .= '</div>'; 
		$output .= '<div class="workout_day_WidgetMore"><a href="'.$link.'" target="_blank" title="'.$title.'">'.$currentmore.'</a></div>'; 
	$output .= '</div>';
	
	
	
	$output .= '</div>';
	$output .= '<div class="workout_day_bot_text">Visit <a href="http://www.workoutbox.com" target="_blank">WorkoutBOX</a> for more <a href="http://www.workoutbox.com/workouts/" target="_blank">Workouts.</a></div></div></div><div class="Workout_day_wid_bott" style="margin-bottom:0px;"></div></div>';
	return $output;
}
function strip_selected_tags($str, $tags = "", $stripContent = false)
{
    preg_match_all("/<([^>]+)>/i", $tags, $allTags, PREG_PATTERN_ORDER);
    foreach ($allTags[1] as $tag) {
        $replace = "%(<$tag.*?>)(.*?)(<\/$tag.*?>)%is";
        $replace2 = "%(<$tag.*?>)%is";
       // echo $replace;
        if ($stripContent) {
            $str = preg_replace($replace,'',$str);
            $str = preg_replace($replace2,'',$str);
        }
            $str = preg_replace($replace,'${2}',$str);
            $str = preg_replace($replace2,'${2}',$str);
    }
    return $str;
} 

function lpbc_excerpt_workout_of_the_day($text, $length, $action=null) {
	$excerpt_length = '';
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
	$text = str_replace('Â ', '', $text);
	//echo strlen($text)."  ".$action ."<br />";
	
	return $text;
}

function workout_of_the_day_sksort(&$array, $subkey="id", $sort_ascending=false) {

    if (count($array))
        $temp_array[key($array)] = array_shift($array);

    foreach($array as $key => $val){
        $offset = 0;
        $found = false;
        foreach($temp_array as $tmp_key => $tmp_val)
        {
            if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
            {
                $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                            array($key => $val),
                                            array_slice($temp_array,$offset)
                                          );
                $found = true;
            }
            $offset++;
        }
        if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
    }

    if ($sort_ascending) $array = array_reverse($temp_array);

    else $array = $temp_array;
}

?>