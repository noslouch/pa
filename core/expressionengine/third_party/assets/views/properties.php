<h2><?php echo $file->filename() ?></h2>

<form class="assets-filedata">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr class="assets-fileinfo">
			<th scope="row"><?php echo lang('size') ?></th>
			<td><?php echo Assets_helper::format_filesize($file->size()) ?></td>
		</tr>
		<tr class="assets-fileinfo">
			<th scope="row"><?php echo lang('kind') ?></th>
			<td><?php echo ucfirst(lang($file->kind())) ?></td>
		</tr>
	<?php if ($file->kind() == 'image'): ?>
		<tr class="assets-fileinfo">
			<th scope="row"><?php echo lang('image_size') ?></th>
			<td><?php echo $file->width().' &times; '.$file->height() ?></td>
		</tr>
	<?php endif ?>

		<tr class="assets-spacer"><th></th><td></td></tr>

		<tr>
			<th scope="row"><?php echo lang('title') ?></th>
			<td><textarea name="title" rows="1" data-maxl="100"><?php echo $file->row_field('title') ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><?php echo lang('date') ?></th>
			<td><input name="date" type="text" data-type="date" <?php if ($file->row_field('date')): ?>data-default-date="<?php echo $timestamp ?>" value="<?php echo $human_readable_time ?>"<?php endif ?> /></td>
		</tr>
		<tr>
			<th scope="row"><?php echo lang('alt_text') ?></th>
			<td><textarea name="alt_text" rows="1" data-maxl="255"><?php echo $file->row_field('alt_text') ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><?php echo lang('caption') ?></th>
			<td><textarea name="caption" rows="1" data-maxl="255"><?php echo $file->row_field('caption') ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><?php echo lang('description') ?></th>
			<td><textarea name="desc" rows="1" data-maxl="65535" data-multiline="1"><?php echo $file->row_field('desc') ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><?php echo lang($author_lang) ?></th>
			<td><textarea name="author" rows="1" data-maxl="255"><?php echo $file->row_field('author') ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><?php echo lang('location') ?></th>
			<td><textarea name="location" rows="1" data-maxl="255"><?php echo $file->row_field('location') ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><?php echo lang('keywords') ?></th>
			<td><textarea name="keywords" rows="1" data-maxl="65535" data-multiline="1"><?php echo $file->row_field('keywords') ?></textarea></td>
		</tr>
<?php
	// -------------------------------------------
	//  'assets_meta_add_row' hook
	//   - Allows extensions to add extra metadata rows to the file properties HUD
	//
		if ($this->extensions->active_hook('assets_file_meta_add_row'))
		{
			echo $this->extensions->call('assets_file_meta_add_row', $file);
		}
	//
	// -------------------------------------------
?>
	</table>

	<div class="assets-buttons">
		<input type="submit" class="assets-btn assets-submit assets-disabled" value="<?php echo lang('save_changes') ?>" />
		<div class="assets-btn assets-cancel"><?php echo lang('cancel') ?></div>
	</div>
</form>
