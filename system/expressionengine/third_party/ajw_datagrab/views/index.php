<h3>Create a new import</h3>

<?php 
	echo form_open( $form_action ); 
	echo form_hidden( "datagrab_step", "index" ); 
?>

<p>
	<select name="type">
<?php foreach( $types as $type => $type_label ): ?>
		<option value="<?php echo $type; ?>"><?php echo $type_label ?></option>
<?php endforeach; ?>
	</select>

	<input type="submit" value="Create new import" class="submit" />
</p>

<?php echo form_close(); ?>

<p><a href="http://brandnewbox.co.uk/products/datatypes">Download additional datatypes &raquo;</a><br/><br/></p>

<?php if ( count( $saved_imports ) ): ?>

<h3>Use a saved import</h3>

<?php echo form_open( $form_action ); ?>

<?php

$this->table->set_template($cp_table_template);
$this->table->set_heading('ID', 'Name', 'Description', 'Configure', 'Run import', "Import URL", 'Delete');

echo $this->table->generate($saved_imports);

?>

<p class="info">
	<strong>Saved imports</strong> can be run from outside the 
	Control Panel (eg, using a cron job), using the <em>Import URL</em>.<br/><br/>
	Copy the <em>Import URL</em> by right-clicking on the link and selecting 
	"Copy Link" (or similar).
</p>

<script type="text/javascript">

$(function(){

	var link;

	var dialog = $('<div id="popup"></div>')
				.html('Loading...')
				.dialog({
					autoOpen: false,
					width: 640,
					height: 80,
					resizable: false,
					position: ["center", "center"],
					modal: true,
					draggable: true,
					title: 'The URL to run this import for outside of the Control Panel',
					open: function (event, ui) {
						$("#popup").html( link );
					},
					close: function (q, r) {
					}
				});

		$(".passkey").click( function() {
			link = this.href;
			dialog.dialog('open');
			return false;
		});

});

</script>

<?php echo form_close(); ?>

<?php endif; ?>
