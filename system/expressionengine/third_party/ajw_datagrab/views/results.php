<?php 
	if( $batch ): 

		$redirect_url = $batch_action.'&skip='.($skip+$limit).'&limit='.$limit;
		$redirect_url = str_replace("&amp;", "&", $redirect_url);
		// print '<p><a href="' . $redirect_url . '">Do next batch</a></p>';

?>

<script type="text/javascript">

function redirect() {
  window.location = "<?php echo $redirect_url ?>";
}

$(document).ready( function() {
	setTimeout('redirect()', 3000);
});

</script>

<?php

	echo '<p class="info"><img src="'.$cp_theme_url.'images/indicator.gif" /> Importing next batch of entries (' . ($skip+$limit) . ' to ' . ($skip+$limit+$limit-1) . ')</p>';

	endif;

?>

<?php 

$this->table->set_template($cp_table_template);
$this->table->set_heading("Results");

$this->table->add_row(
	$results
);

echo $this->table->generate();

?>

<?php if( $id == 0 ): ?>

	<p>

		<?php echo form_open( $form_action ); ?>
		<?php echo form_hidden( 'id', 0 ); ?>
		<input type="submit" value="Save" class="submit" />
		<?php echo form_close(); ?>

	</p>

<?php else: ?>

	<p>

		<?php echo form_open( $form_action ); ?>
		<?php echo form_hidden( 'id',  $id ); ?>
		<input type="submit" value="Update saved import" class="submit" />
		<?php echo form_close(); ?>

	</p>
	<p>

		<?php echo form_open( $form_action ); ?>
		<?php echo form_hidden( 'id', 0 ); ?>
		<input type="submit" value="Save as new import" class="submit" />
		<?php echo form_close(); ?>

	</p>

<?php endif; ?>

