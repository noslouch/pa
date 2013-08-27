<?php echo form_open( $form_action ); ?>

<?php 

$this->table->set_template($cp_table_template);
$this->table->set_heading('Setting', 'Value');
echo $this->table->generate($form);

?>

<?php

	if( isset( $id ) && $id > 0 ) {
		echo form_hidden("id", $id);
	}

?>

<input type="submit" value="Save" class="submit" />

<?php echo form_close(); ?>
