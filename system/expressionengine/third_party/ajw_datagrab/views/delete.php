<?php echo form_open( $form_action ); ?>

<p class="shun">Are you sure you want to delete this saved import?</p>

<p class="notice">This action cannot be undone</p>

<p><input type="submit" value="Delete" class="submit" /></p>

<?php echo form_hidden('id', $id)?>

<?php echo form_close(); ?>
