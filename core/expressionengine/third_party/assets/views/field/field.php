<?php
	$namespace = !empty($ce_options) ? uniqid('CE_') : '';
?>
<div id="<?php echo $field_id ?>" class="assets-field" ce_namespace="<?= $namespace ?>">
	<?php echo $this->load->view($file_view) ?>
</div>
<div class="assets-buttons <?php if (! $multi): ?>assets-single<?php endif ?>">
	<a class="assets-btn assets-add <?php if (! $multi && $files): ?>assets-disabled<?php endif ?>"><span></span><?php echo lang($multi ? 'add_files' : 'add_file') ?></a>
	<a class="assets-btn assets-remove <?php if ($multi || ! $files): ?>assets-disabled<?php endif ?>"><span></span><?php echo lang($multi ? 'remove_files' : 'remove_file') ?></a>
</div>

<?php if ($namespace) : ?>
	<script>
		$('[ce_namespace="<?= $namespace ?>"]').data('ce_options', <?=$ce_options?>);
	</script>
<?php endif; ?>