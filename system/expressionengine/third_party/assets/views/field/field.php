<div id="<?php echo $field_id ?>" class="assets-field">
	<?php echo $this->load->view($file_view) ?>
</div>
<div class="assets-buttons <?php if (! $multi): ?>assets-single<?php endif ?>">
	<a class="assets-btn assets-add <?php if (! $multi && $files): ?>assets-disabled<?php endif ?>"><span></span><?php echo lang($multi ? 'add_files' : 'add_file') ?></a>
	<a class="assets-btn assets-remove <?php if ($multi || ! $files): ?>assets-disabled<?php endif ?>"><span></span><?php echo lang($multi ? 'remove_files' : 'remove_file') ?></a>
</div>
