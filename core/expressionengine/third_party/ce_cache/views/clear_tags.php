<?php
if ( $show_form ) //show the form
{
	if ( count( $tags ) > 0 ) //we've got tags
	{
		//tag instructions
		echo '<p>' . lang( 'ce_cache_clear_tags_instructions' ) . '</p>';

		//open form
		echo form_open( $action_url, '' );

		//create the table
		$this->table->set_template( $cp_table_template );
		$this->table->set_heading( '<input type="checkbox" id="ce_cache_tag_master" name="ce_cache_tag_master" /> ' . lang( 'ce_cache_tag' ) );

		//loop through the tags
		foreach( $tags as $index => $tag )
		{
			$this->table->add_row(
				form_checkbox(
					array(
						'name' => 'ce_cache_tags[]',
						'id' => 'ce_cache_tag_' . $index,
						'class' => 'ce_cache_tag_item',
						'value' => $tag,
						'checked' =>  in_array( $tag, $selected ),
					 )
				) . ' ' . form_label( $tag, 'ce_cache_tag_' . $index )
			);
		}

		//generate the table
		echo $this->table->generate();

		//submit
		echo form_submit( array( 'name' => 'submit', 'value' => lang( "ce_cache_confirm_delete_tags_button" ), 'class' => 'submit' ) );

		//close form
		echo form_close();
	}
	else //no tags
	{
		echo '<p>' . lang( 'ce_cache_no_tags' ) . '</p>';
		echo '<p>' . $back_link . '</p>';
	}
}
else //show the success message
{
	echo '<p>' . lang( "ce_cache_delete_tags_success" ) . '</p>';
	echo '<p>' . $back_link . '</p>';
}
?>