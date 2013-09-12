<div class="assets-filedirs">
	<div class="assets-all assets-settingheader">
		<label class="assets-checkbox"><?php echo form_checkbox('assets[filedirs]', 'all', ($data == 'all'), 'onchange="Assets.onAllFiledirsChange(this)"') ?>&nbsp;&nbsp;<?php echo lang('all') ?></label>
	</div>
	<div class="assets-list">
		<?php if (empty($filedirs)): ?>
			<?php echo lang('no_file_upload_directories') ?>
		<?php else: ?>
			<?php foreach ($filedirs as $value => $title): ?>
				<label class="assets-checkbox"><?php echo form_checkbox('assets[filedirs][]', $value, ($data == 'all' || in_array($value, $data)), ($data == 'all' ? 'disabled="disabled"' : '')) ?>&nbsp;&nbsp;<?php echo $title ?></label><br/>
			<?php endforeach ?>
		<?php endif ?>
	</div>
</div>
