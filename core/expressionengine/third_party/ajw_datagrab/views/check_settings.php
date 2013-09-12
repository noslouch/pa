<?php echo form_open( $form_action ); ?>

<p>There are <?php echo count( $rows ); ?> fields per entry.</p>
<p></p>

<?php 

$this->table->set_template($cp_table_template);
$this->table->set_heading("Fields");

echo $this->table->generate( $rows );

?>


<input type="submit" value="Configure import" class="submit" />

<?php echo form_close(); ?>
