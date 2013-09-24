<?php
	$this->table->set_template( $cp_table_template );
	$this->table->set_heading(
		lang( 'ce_cache_channel' ),
		'&nbsp;'
	);

	$this->table->add_row(
		lang( 'ce_cache_any_channel' ),
		'<a href="' . BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=ce_cache' . AMP . 'method=breaking_settings' . AMP . 'channel_id=0">' . lang( 'ce_cache_break_settings' ) . '</a>'
		);
	foreach( $channels as $channel )
	{
		$this->table->add_row(
			$channel['title'],
			'<a href="' . BASE . AMP . 'C=addons_modules' . AMP . 'M=show_module_cp' . AMP . 'module=ce_cache' . AMP . 'method=breaking_settings' . AMP . 'channel_id=' . $channel['id'] . '">' . lang( 'ce_cache_break_settings' ) . '</a>'
		);
	}

	echo $this->table->generate();
?>