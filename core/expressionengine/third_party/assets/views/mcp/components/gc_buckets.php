<?php

echo '<select name="gc_bucket">';

foreach ($bucket_list as $bucket_name => $bucket_data)
{
	$selected = '';
	if ( $bucket_name == $source_settings->bucket)
	{
		$selected = ' selected="selected"';
	}
	echo '<option value="'.$bucket_name.'" data-location="'.$bucket_data->location.'" data-url-prefix="'.$bucket_data->url_prefix.'"'.$selected.'>' .
		$bucket_name .
		'</option>';
}

echo '</select>';
