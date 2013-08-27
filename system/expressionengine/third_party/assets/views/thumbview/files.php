<?php
	if (! isset($thumb_size))       $thumb_size = 'small';
	if (! isset($show_filenames))   $show_filenames = FALSE;
	if (! isset($files))            $files = array();
	if (! isset($field_id))         $field_id = FALSE;
	if (! isset($field_name))       $field_name = FALSE;
	if (! isset($disabled_files))   $disabled_files = array();

	$css_namespace = $field_name ? 'field-' . preg_replace('/\[.*\]/U', '', $field_name) . '-' : '';
	$thumbs = array();
	$max_thumb_width = ($thumb_size == 'small' ? 100 : 230);
	$max_thumb_height = round($max_thumb_width * 2/3);

	foreach ($files as $i => &$file):

		$file_class = $css_namespace.'assets-file-'.$max_thumb_width.'x'.$max_thumb_height.'-'.$file->file_id();

		// get the thumb data
		$thumb_data = $file->get_thumb_data($max_thumb_width, $max_thumb_height);

		if ($thumb_data !== FALSE)
		{
			if (!$field_name)
			{
				$margin_left = round(($max_thumb_width - $thumb_data->width) / 2);
				$margin_right = $max_thumb_width - ($thumb_data->width + $margin_left);
			}
			else
			{
				$margin_left = $margin_right = 0;
			}

			$margin_top = round(($max_thumb_height - $thumb_data->height) / 2);
			$margin_bottom = $max_thumb_height - ($thumb_data->height + $margin_top);

			$thumb_data->margin = $margin_top.'px '.$margin_right.'px '.$margin_bottom.'px '.$margin_left.'px';

			$thumbs[$file_class] = $thumb_data;

			$thumb_html = '<div class="assets-thumb-wrapper"><div class="assets-thumb"></div></div>';
		}
		else
		{
			$extension = strtoupper($file->extension());
			$thumb_html = '<div class="assets-fileicon"><div class="assets-extension">'.$extension.'</div></div>';
		}
?>
	<li title="<?php echo $file->filename().$file->ext() ?>" data-id="<?php echo $file->file_id() ?>" data-folder="<?php echo $file->row_field('folder_id') ?>" data-file-url="<?php echo $file->url() ?>" data-file_name="<?php echo $file->filename().$file->ext() ?>" class="assets-tv-file <?php echo $file_class ?><?php if ($thumb_size == 'large'): ?> assets-tv-bigthumb<?php endif ?><?php if (in_array($file->file_id(), $disabled_files)): ?> assets-disabled<?php endif ?><?php if ($file->selected): ?> assets-selected<?php endif ?>">
		<?php echo $thumb_html ?>
		<?php if ($show_filenames): ?>
			<div class="assets-tv-filename"><?php echo $file->filename() ?></div>
		<?php endif ?>
		<?php if ($field_name): ?>
			<input type="hidden" name="<?php echo $field_name ?>[]" value="<?php echo $file->file_id() ?>" />
		<?php endif ?>
	</li>
<?php
	endforeach;

	if ($thumbs)
	{
		Assets_helper::queue_thumb_css($thumbs, !empty($field_name));
	}
?>
