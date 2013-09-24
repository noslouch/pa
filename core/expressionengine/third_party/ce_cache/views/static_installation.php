<?php echo str_replace(
	array(
		'{site}',
		'{ce_cache_home_link}'
	),
	array(
		rtrim( $site, '/' ),
		BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . "module=ce_cache" . AMP . 'method=index'
	),
	lang( 'ce_cache_static_instructions' )
);
?>