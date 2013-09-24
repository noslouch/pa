<?php
if ( $show_form ): //show the form

	//open form
	echo form_open( $action_url, '' );

	echo '<p>' . lang( 'ce_cache_confirm_clear_all_driver' ) . '</p>';

	//submit
	echo form_submit( array( 'name' => 'submit', 'value' => lang( 'ce_cache_confirm_clear_sites_button' ), 'class' => 'submit' ) );

	//close form
	echo form_close();
else: //show the success message
	echo '<p>' . lang( 'ce_cache_clear_site_cache_success' ) . '</p>';
	echo '<p>' . $back_link . '</p>';
endif;
?>