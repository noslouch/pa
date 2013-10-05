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
				<input type="text" name="channel_images[categories]" <?php if (isset($override['categories']) === true):?>disabled<?php endif;?> value="<?=implode(',', $categories)?>" style="border:1px solid #ccc; width:80%;">
				<small><?=lang('ci:categories_explain')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:default_category')?></td>
			<td>
				<input type="text" name="channel_images[default_category]" <?php if (isset($override['default_category']) === true):?>disabled<?php endif;?> value="<?=$default_category?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_stored_images')?></td>
			<td>
				<input name="channel_images[show_stored_images]" <?php if (isset($override['show_stored_images'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($show_stored_images == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[show_stored_images]" <?php if (isset($override['show_stored_images'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($show_stored_images == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:limt_stored_images_author')?></td>
			<td>
				<input name="channel_images[stored_images_by_author]" <?php if (isset($override['stored_images_by_author'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($stored_images_by_author == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[stored_images_by_author]" <?php if (isset($override['stored_images_by_author'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($stored_images_by_author == 'no') echo 'checked'?>> <?=lang('ci:no')?>
				<small><?=lang('ci:limt_stored_images_author_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:stored_images_search_type')?></td>
			<td>
				<input name="channel_images[stored_images_search_type]" <?php if (isset($override['stored_images_search_type'])):?>disabled="disabled"<?php endif;?> type="radio" value="entry" <?php if ($stored_images_search_type == 'entry') echo 'checked'?>> <?=lang('ci:entry_based')?>&nbsp;&nbsp;
				<input name="channel_images[stored_images_search_type]" <?php if (isset($override['stored_images_search_type'])):?>disabled="disabled"<?php endif;?> type="radio" value="image" <?php if ($stored_images_search_type == 'image') echo 'checked'?>> <?=lang('ci:image_based')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_import_files')?></td>
			<td>
				<input name="channel_images[show_import_files]" <?php if (isset($override['show_import_files'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($show_import_files == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[show_import_files]" <?php if (isset($override['show_import_files'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($show_import_files == 'no') echo 'checked'?>> <?=lang('ci:no')?>
				<small><?=lang('ci:show_import_files_exp')?></small>
				<?=lang('ci:import_path')?>
				<input type="text" name="channel_images[import_path]" <?php if (isset($override['import_path']) === true):?>disabled<?php endif;?> value="<?=$import_path?>" style="border:1px solid #ccc; width:80%;">
				<small><?=lang('ci:import_path_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_image_edit')?></td>
			<td>
				<input name="channel_images[show_image_edit]" <?php if (isset($override['show_image_edit'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($show_image_edit == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[show_image_edit]" <?php if (isset($override['show_image_edit'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($show_image_edit == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:show_image_replace')?></td>
			<td>
				<input name="channel_images[show_image_replace]" <?php if (isset($override['show_image_replace'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($show_image_replace == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[show_image_replace]" <?php if (isset($override['show_image_replace'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($show_image_replace == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:allow_per_image_action')?></td>
			<td>
				<input name="channel_images[allow_per_image_action]" <?php if (isset($override['allow_per_image_action'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($allow_per_image_action == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[allow_per_image_action]" <?php if (isset($override['allow_per_image_action'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($allow_per_image_action == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:image_limit')?></td>
			<td>
				<input type="text" name="channel_images[image_limit]" <?php if (isset($override['image_limit']) === true):?>disabled<?php endif;?> value="<?=$image_limit?>" style="border:1px solid #ccc; width:50px;">
				<small><?=lang('ci:image_limit_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:hybrid_upload')?></td>
			<td>
				<input name="channel_images[hybrid_upload]" <?php if (isset($override['hybrid_upload'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($hybrid_upload == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[hybrid_upload]" <?php if (isset($override['hybrid_upload'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($hybrid_upload == 'no') echo 'checked'?>> <?=lang('ci:no')?>
				<small><?=lang('ci:hybrid_upload_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:progressive_jpeg')?></td>
			<td>
				<input name="channel_images[progressive_jpeg]" <?php if (isset($override['progressive_jpeg'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($progressive_jpeg == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[progressive_jpeg]" <?php if (isset($override['progressive_jpeg'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($progressive_jpeg == 'no') echo 'checked'?>> <?=lang('ci:no')?>
				<small><?=lang('ci:progressive_jpeg_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:wysiwyg_original')?></td>
			<td>
				<input name="channel_images[wysiwyg_original]" <?php if (isset($override['wysiwyg_original'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($wysiwyg_original == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[wysiwyg_original]" <?php if (isset($override['wysiwyg_original'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($wysiwyg_original == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:save_data_in_field')?></td>
			<td>
				<input name="channel_images[save_data_in_field]" <?php if (isset($override['save_data_in_field'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($save_data_in_field == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[save_data_in_field]" <?php if (isset($override['save_data_in_field'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($save_data_in_field == 'no') echo 'checked'?>> <?=lang('ci:no')?>
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
				<input name="channel_images[disable_cover]" <?php if (isset($override['disable_cover'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($disable_cover == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[disable_cover]" <?php if (isset($override['disable_cover'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($disable_cover == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:convert_jpg')?></td>
			<td>
				<input name="channel_images[convert_jpg]" <?php if (isset($override['convert_jpg'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($convert_jpg == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[convert_jpg]" <?php if (isset($override['convert_jpg'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($convert_jpg == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:cover_first')?></td>
			<td>
				<input name="channel_images[cover_first]" <?php if (isset($override['cover_first'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($cover_first == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[cover_first]" <?php if (isset($override['cover_first'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($cover_first == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:wysiwyg_output')?></td>
			<td>
				<input name="channel_images[wysiwyg_output]" <?php if (isset($override['wysiwyg_output'])):?>disabled="disabled"<?php endif;?> type="radio" value="image_url" <?php if ($wysiwyg_output == 'image_url') echo 'checked'?>> <?=lang('ci:image_url')?>&nbsp;&nbsp;
				<input name="channel_images[wysiwyg_output]" <?php if (isset($override['wysiwyg_output'])):?>disabled="disabled"<?php endif;?> type="radio" value="static_image" <?php if ($wysiwyg_output == 'static_image') echo 'checked'?>> <?=lang('ci:static_image:var')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:parse_iptc')?></td>
			<td>
				<input name="channel_images[parse_iptc]" <?php if (isset($override['parse_iptc'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($parse_iptc == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[parse_iptc]" <?php if (isset($override['parse_iptc'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($parse_iptc == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:parse_exif')?></td>
			<td>
				<input name="channel_images[parse_exif]" <?php if (isset($override['parse_exif'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($parse_exif == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[parse_exif]" <?php if (isset($override['parse_exif'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($parse_exif == 'no') echo 'checked'?>> <?=lang('ci:no')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:parse_xmp')?></td>
			<td>
				<input name="channel_images[parse_xmp]" <?php if (isset($override['parse_xmp'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($parse_xmp == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[parse_xmp]" <?php if (isset($override['parse_xmp'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($parse_xmp == 'no') echo 'checked'?>> <?=lang('ci:no')?>
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
			<td>
				<input type="text" name="channel_images[columns][<?=$name?>]'" <?php if (isset($override['columns'][$name]) === true):?>disabled value="<?=$override['columns'][$name]?>" <?php else:?> value="<?=$val?>"  <?php endif;?> style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
