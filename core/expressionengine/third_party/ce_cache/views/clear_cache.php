<?php
if ( $show_form ): //show the form

	//open form
	echo form_open( $action_url, '' );

	//driver name
	echo form_hidden( 'driver', $driver );

	if ( ! $site_only || ($driver == 'memcached') || ($driver == 'memcache') ) //multiple sites or driver that clears all
	{
		echo '<p>' . sprintf( lang('ce_cache_confirm_clear_all_drivers'), lang("ce_cache_driver_{$driver}") ) . '</p>';
	}
	else //clear the driver cache for the current site
	{
		echo '<p>' . sprintf( lang('ce_cache_confirm_clear_site_driver'), lang("ce_cache_driver_{$driver}") ) . '</p>';
	}


	//submit
	echo form_submit( array( 'name' => 'submit', 'value' => lang( 'ce_cache_confirm_clear_button' ), 'class' => 'submit' ) );

	//close form
	echo form_close();
else: //show the success message
	echo '<p>' . lang( "{$module}_clear_cache_success" ) . '</p>';
	echo '<p>' . $back_link . '</p>';
endif;
?>