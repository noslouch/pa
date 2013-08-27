<?php

echo '<select name="rs_container">';

foreach ($container_list as $container_name => $container_url)
{
	$selected = '';
	if ( isset($source_settings->container) && $container_name == $source_settings->container)
	{
		$selected = ' selected="selected"';
	}
	echo '<option value="'.htmlentities($container_name).'" data-url-prefix="'.$container_url.'"'.$selected.'>' .
		$container_name .
		'</option>';
}

echo '</select>';
