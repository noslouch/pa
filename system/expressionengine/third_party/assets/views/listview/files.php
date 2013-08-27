<?php
	if (! isset($cols))             $cols = array('date', 'size');
	if (! isset($files))            $files = array();
	if (! isset($start_index))      $start_index = 0;
	if (! isset($field_id))         $field_id = FALSE;
	if (! isset($field_name))       $field_name = FALSE;
	if (! isset($disabled_files))   $disabled_files = array();

	$css_namespace = $field_name ? 'field-' . preg_replace('/\[.*\]/U', '', $field_name) . '-' : '';
	$thumbs = array();
	$max_thumb_width = 21;
	$max_thumb_height = 14;

	foreach ($files as $i => $file):

		$file_class = $css_namespace.'assets-file-'.($field_id ? $field_id.'-' : '').$file->file_id();

		// assemble the <tr> class name
		$tr_class = $file_class;
		if (in_array($file->file_id(), $disabled_files)) $tr_class .= ' assets-disabled';
		if ($file->selected) $tr_class .= ' assets-selected';

		// get the thumb data
		$thumb_data = $file->get_thumb_data($max_thumb_width, $max_thumb_height);

		if ($thumb_data !== FALSE)
		{
			$margin_left = round(($max_thumb_width - $thumb_data->width) / 2);
			$margin_right = $max_thumb_width - ($thumb_data->width + $margin_left);
			$margin_top = round(($max_thumb_height - $thumb_data->height) / 2);
			$margin_bottom = $max_thumb_height - ($thumb_data->height + $margin_top);

			$thumb_data->margin = $margin_top.'px '.$margin_right.'px '.$margin_bottom.'px '.$margin_left.'px';

			$thumbs[$file_class] = $thumb_data;
			$thumb_class = 'assets-thumb';
		}
		else
		{
			$thumb_class = 'assets-fileicon';
		}
?>
	<tr data-id="<?php echo $file->file_id() ?>" data-folder="<?php echo $file->row_field('folder_id') ?>" data-file_name="<?php echo $file->filename() ?>" data-file-url="<?php echo $file->url() ?>" class="<?php echo $tr_class ?>">

		<td class="assets-lv-name"><div class="assets-lv-thumb"><div class="<?php echo $thumb_class ?>"></div></div><?php echo $file->filename() ?><?php if ($field_name): ?><input type="hidden" name="<?php echo $field_name ?>[]" value="<?php echo $file->file_id() ?>" /><?php endif ?></td>

		<?php if (in_array('folder', $cols)): ?>
			<td class="assets-lv-folder"><?php echo $file->folder() ?></td>
		<?php endif ?>

		<?php if (in_array('date', $cols)): ?>
			<td class="assets-lv-date"><?php echo Assets_helper::format_date($file->date()) ?></td>
		<?php endif ?>

		<?php if (in_array('size', $cols)): ?>
			<td class="assets-lv-size"><?php echo Assets_helper::format_filesize($file->size()) ?></td>
		<?php endif ?>
	</tr>
<?php
	endforeach;

	if ($thumbs)
	{
		Assets_helper::queue_thumb_css($thumbs, FALSE);
	}
?>
