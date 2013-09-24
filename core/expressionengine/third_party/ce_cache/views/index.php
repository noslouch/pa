<?php

	if ( $disabled )
	{
		echo lang( 'ce_cache_off' );
	}

	$this->table->set_template( $cp_table_template );
	$this->table->set_heading(
		lang( "{$module}_driver" ),
		lang( "{$module}_view_items" ),
		lang( "{$module}_clear_cache_question_site" ),
		lang( "{$module}_clear_cache_question_driver" )
	);

	foreach( $drivers as $driver )
	{
		$this->table->add_row(
			lang( "{$module}_driver_{$driver}" ),
			( $driver != 'memcached' && $driver != 'memcache' && $driver != 'dummy' ) ? '<a href="' . BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . "module={$module}" . AMP . 'method=view_items' . AMP . "driver={$driver}" . '">' . lang( "{$module}_view_items" ) . '</a>' : '&ndash;',
			( $driver != 'memcached' && $driver != 'memcache' && $driver != 'dummy' ) ? '<a href="' . BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . "module={$module}" . AMP . 'method=clear_cache' . AMP . "driver={$driver}" . AMP . 'site_only=y">' . lang( "{$module}_clear_cache_site" ) . '</a>' : '&ndash;',
			( $driver != 'dummy' ) ? '<a href="' . BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . "module={$module}" . AMP . 'method=clear_cache' . AMP . "driver={$driver}" . '">' . lang( "{$module}_clear_cache_driver" ) . '</a>' : '&ndash;'
		);
	}

	$this->table->add_row(
		'&ndash;',
		'&ndash;',
		'<a href="' . BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . "module={$module}" . AMP . 'method=clear_site_caches' . '">' . lang( 'ce_cache_clear_cache_site_all' ) . '</a>',
		'<a href="' . BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . "module={$module}" . AMP . 'method=clear_all_caches' . '">' . lang( 'ce_cache_clear_cache_all_drivers' ) . '</a>'
	);

	echo $this->table->generate();
?>