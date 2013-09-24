<?php
if ( $show_form ): //show the form

	//open form
	echo form_open( $action_url, '' );

	//channel id
	echo form_hidden( 'channel_id', $channel_id );
?>

	<?php echo ( $channel_id != 0 ) ? sprintf( lang( 'ce_cache_break_intro_html' ), $channel_title ) : lang( 'ce_cache_break_intro_any' ) ?>

	<p><input type="checkbox" name="ce_cache_refresh" id="ce_cache_refresh" value="y" <?php
if ( $refresh )
{
	echo 'checked="checked" ';
}

?>> <label for="ce_cache_refresh"><?php echo lang( 'ce_cache_refresh_cached_items_question' ) ?></label></p>

	<div id="ce_cache_refresh_holder" style="display: none;">
		<?php
			echo lang( 'ce_cache_refresh_cached_items_instructions_html' );
			echo form_dropdown( 'ce_cache_refresh_time', array( 0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5 ), $refresh_time, 'id="ce_cache_refresh_time"' );

			if ( ! empty( $errors['ce_cache_refresh_time'] ) )
			{
				echo '<p class="error">' . lang( $errors['ce_cache_refresh_time'] ) . '</p>';
			}
		?>
		<br>
	</div><!-- #ce_cache_refresh_holder -->

	<div class="ce_cache_clear"><!-- --></div><!-- .ce_cache_clear -->

	<div id="ce_cache_variables">
		<h3><?php echo lang( 'ce_cache_variables' ) ?></h3>
		<?php echo lang( 'ce_cache_breaking_variables_html' ); ?>
	</div><!-- #ce_cache_variables -->

	<div id="ce_cache_tag">
		<h3><?php echo lang( 'ce_cache_tags' ) ?></h3>
		<?php echo lang( 'ce_cache_breaking_tags_instructions_html' ); ?>
		<div id="ce_cache_tags_holder">
<?php
			foreach ( $tags as $index => $tag )
			{
				echo "\t\t\t" . '<span class="ce_cache_tag_wrapper"><input name="ce_cache_tag[' . $index . ']" type="text" value="' . $tag . '" /> <a class="ce_cache_remove_tag" href="#ce_cache_remove_tag"></a>';
				if ( isset( $errors['ce_cache_tag'][$index] ) )
				{
					echo '<span class="error">' . lang( $errors['ce_cache_tag'][$index] ) . '</span>';
				}
				echo '</span>' . PHP_EOL;
			}
?>
			<a class="ce_cache_add_tag" href="#ce_cache_add_tag" title="<?php echo lang('ce_cache_add') ?>"></a>
		</div><!-- #ce_cache_tags_holder -->

		<?php echo lang( 'ce_cache_breaking_tags_examples_html' ); ?>
	</div><!-- #ce_cache_tag -->

	<div id="ce_cache_items">
		<h3><?php echo lang( 'ce_cache_items' ) ?></h3>
		<?php echo lang( 'ce_cache_breaking_items_instructions_html' ); ?>
		<div id="ce_cache_items_holder">
<?php
			foreach ( $items as $index => $item )
			{
				echo "\t\t\t" . '<span class="ce_cache_item_wrapper"><input name="ce_cache_item[' . $index . ']" type="text" value="' . $item . '" /> <a class="ce_cache_remove_item" href="#ce_cache_remove_item"></a>';

				if ( isset( $errors['ce_cache_item'][$index] ) )
				{
					echo '<span class="error">' . lang( $errors['ce_cache_item'][$index] ) . '</span>';
				}
				echo '</span>' . PHP_EOL;
			}
?>
			<a class="ce_cache_add_item" href="#ce_cache_add_item" title="<?php echo lang('ce_cache_add') ?>"></a>
		</div><!-- #ce_cache_items_holder -->

		<?php echo lang( 'ce_cache_breaking_items_examples_html' ); ?>

	</div><!-- #ce_cache_items -->

	<div class="ce_cache_clear"><!-- --></div><!-- .ce_cache_clear -->

	<br>
<?php
	//submit
	echo form_submit( array( 'name' => 'submit', 'value' => lang( "ce_cache_save_settings" ), 'class' => 'submit' ) );

	//close form
	echo form_close();
else: //show the success message
	echo '<p>' . lang( "ce_cache_save_settings_success" ) . '</p>';
	echo '<p>' . $back_link . '</p>';
endif;
?>