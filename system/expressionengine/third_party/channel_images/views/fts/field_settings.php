<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable" style="width:80%">
	<thead>
		<tr>
			<th style="width:180px"><?=lang('ci:pref')?></th>
			<th><?=lang('ci:value')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:categories')?></td>
			<td>
				<?=form_input('channel_images[categories]', implode(',', $categories), 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:categories_explain')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_stored_images')?></td>
			<td><?=form_dropdown('channel_images[show_stored_images]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $show_stored_images)?></td>
		</tr>
		<tr>
			<td><?=lang('ci:limt_stored_images_author')?></td>
			<td>
				<?=form_dropdown('channel_images[stored_images_by_author]', array('no' => lang('ci:no'), 'yes' => lang('ci:yes')), $stored_images_by_author)?>
				<small><?=lang('ci:limt_stored_images_author_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:stored_images_search_type')?></td>
			<td>
				<?=form_dropdown('channel_images[stored_images_search_type]', array('entry' => lang('ci:entry_based'), 'image' => lang('ci:image_based')), $stored_images_search_type)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_import_files')?></td>
			<td>
				<?=form_dropdown('channel_images[show_import_files]', array('no' => lang('ci:no'), 'yes' => lang('ci:yes')), $show_import_files)?>
				<small><?=lang('ci:show_import_files_exp')?></small>
				<?=lang('ci:import_path')?> <?=form_input('channel_images[import_path]', $import_path, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:import_path_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_image_edit')?></td>
			<td>
				<?=form_dropdown('channel_images[show_image_edit]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $show_image_edit)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_image_replace')?></td>
			<td>
				<?=form_dropdown('channel_images[show_image_replace]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $show_image_replace)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:allow_per_image_action')?></td>
			<td>
				<?=form_dropdown('channel_images[allow_per_image_action]', array('no' => lang('ci:no'), 'yes' => lang('ci:yes')), $allow_per_image_action)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:image_limit')?></td>
			<td>
				<?=form_input('channel_images[image_limit]', $image_limit, 'style="border:1px solid #ccc; width:50px;"')?>
				<small><?=lang('ci:image_limit_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:hybrid_upload')?></td>
			<td>
				<?=form_dropdown('channel_images[hybrid_upload]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $hybrid_upload)?>
				<small><?=lang('ci:hybrid_upload_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:progressive_jpeg')?></td>
			<td>
				<?=form_dropdown('channel_images[progressive_jpeg]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $progressive_jpeg)?>
				<small><?=lang('ci:progressive_jpeg_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:wysiwyg_original')?></td>
			<td>
				<?=form_dropdown('channel_images[wysiwyg_original]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $wysiwyg_original)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:save_data_in_field')?></td>
			<td>
				<?=form_dropdown('channel_images[save_data_in_field]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $save_data_in_field)?>
				<small><?=lang('ci:save_data_in_field_exp')?></small>
			</td>
		</tr>
		<!--
		<tr>
			<td><?=lang('ci:locked_url_fieldtype')?></td>
			<td>
				<?=form_dropdown('channel_images[locked_url_fieldtype]', array('no' => lang('ci:no'), 'yes' => lang('ci:yes')), $locked_url_fieldtype)?>
				<small><?=lang('ci:locked_url_fieldtype_exp')?></small>
			</td>
		</tr>
		-->
		<tr>
			<td><?=lang('ci:disable_cover')?></td>
			<td>
				<?=form_dropdown('channel_images[disable_cover]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $disable_cover)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:convert_jpg')?></td>
			<td>
				<?=form_dropdown('channel_images[convert_jpg]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $convert_jpg)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:cover_first')?></td>
			<td>
				<?=form_dropdown('channel_images[cover_first]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $cover_first)?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:embedded_data')?></td>
			<td>
				<?=form_dropdown('channel_images[parse_iptc]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $parse_iptc)?> <?=lang('ci:parse_iptc')?>&nbsp;&nbsp;&nbsp;
				<?=form_dropdown('channel_images[parse_exif]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $parse_exif, ((!function_exists('exif_read_data')) ? 'disabled' : '') )?> <?=lang('ci:parse_exif')?>&nbsp;&nbsp;&nbsp;
				<?=form_dropdown('channel_images[parse_xmp]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $parse_xmp)?> <?=lang('ci:parse_xmp')?>&nbsp;&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:act_url')?></td>
			<td>
				<strong><a href="<?=$act_url?>" target="_blank"><?=$act_url?></a></strong>
				<small><?=lang('ci:act_url:exp')?></small>
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:field_columns')?>
					<small><?=lang('ci:field_columns_exp')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($columns as $name => $val):?>
		<tr>
			<td><?=lang('ci:'.$name)?></td>
			<td><?=form_input('channel_images[columns]['.$name.']', $val)?></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
