<?php
	if (! function_exists('assets_radio_group'))
	{
		function assets_radio_group($setting_name, $options, $selected_value)
		{
			$output = '';

			foreach ($options as $option_value => $option)
			{
				if ($output)
				{
					$output .= NBS.NBS.NBS.NBS.NL;
				}

				if (! is_array($option))
				{
					$option = array('label' => $option);
				}

				if (is_numeric($option_value))
				{
					$option_value = $option['label'];
					$option['label'] = lang($option['label']);
				}

				$is_selected = ($option_value == $selected_value);
				$extra = (isset($option['extra']) ? $option['extra'] : '');

				$output .= '<label>' .
					form_radio("assets[{$setting_name}]", $option_value, $is_selected, $extra) . ' ' .
					$option['label'] .
					'</label>' . NL;
			}

			return $output;
		}
	}
?>

<div class="assets-settingheader">
	<label>
		<?php echo lang('view_files_as').NBS.NBS ?>
		<?php echo form_dropdown('assets[view]', array('thumbs'=>lang('thumbnails'), 'list'=>lang('list')), $settings['view'],
			'onchange="jQuery(this).parent().parent().next().children(\'.assets-options-\'+this.value).show().siblings().hide()"') ?>
	</label>
</div>

<div>
	<div class="assets-options-thumbs"<?php if ($settings['view'] != 'thumbs'): ?> style="display: none"<?php endif ?>>
		<p>
			<?php echo lang('thumb_size', 'thumb_size').NBS.NBS ?>
			<?php echo form_dropdown('assets[thumb_size]', array('small'=>lang('small'), 'large'=>lang('large')), $settings['thumb_size']) ?>
		</p>
		<p style="margin-bottom: 0">
			<?php echo lang('show_filenames', 'show_filenames').NBS.NBS ?>
			<?php echo form_dropdown('assets[show_filenames]', array('y'=>lang('yes'), 'n'=>lang('no')), $settings['show_filenames']) ?>
		</p>
	</div>

	<div class="assets-options-list"<?php if ($settings['view'] != 'list'): ?> style="display: none"<?php endif ?>>
		<?php echo lang('columns', 'show_cols') ?><br/>
		<?php echo form_hidden('assets[show_cols][]', 'name') ?>
		<label class="assets-checkbox"><?php echo form_checkbox(NULL, NULL, TRUE, 'disabled="disabled"').NBS.NBS.lang('name') ?></label><br/>
		<label class="assets-checkbox"><?php echo form_checkbox('assets[show_cols][]', 'folder', in_array('folder', $settings['show_cols'])).NBS.NBS.lang('folder') ?></label><br/>
		<label class="assets-checkbox"><?php echo form_checkbox('assets[show_cols][]', 'date',   in_array('date',   $settings['show_cols'])).NBS.NBS.lang('date') ?></label><br/>
		<label class="assets-checkbox"><?php echo form_checkbox('assets[show_cols][]', 'size',   in_array('size',   $settings['show_cols'])).NBS.NBS.lang('size') ?></label><br/>
	</div>
</div>
