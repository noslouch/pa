<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:watermark:position_settings')?>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:watermark:padding')?> </td>
			<td>
				<?=form_input($action_field_name.'[padding]', $padding, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:padding:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:horalign')?> </td>
			<td>
				<?=form_input($action_field_name.'[horizontal_alignment]', $horizontal_alignment, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:horalign:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:vrtalign')?> </td>
			<td>
				<?=form_input($action_field_name.'[vertical_alignment]', $vertical_alignment, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:vrtalign:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:horoffset')?> </td>
			<td>
				<?=form_input($action_field_name.'[horizontal_offset]', $horizontal_offset, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:horoffset:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:vrtoffset')?> </td>
			<td>
				<?=form_input($action_field_name.'[vertical_offset]', $vertical_offset, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:vrtoffset:exp')?></small>
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:watermark:text_pref')?>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:watermark:text')?> </td>
			<td>
				<?=form_input($action_field_name.'[text]', $text, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:text:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:font_path')?> </td>
			<td>
				<?=form_input($action_field_name.'[font_path]', $font_path, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:font_path:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:font_size')?> </td>
			<td>
				<?=form_input($action_field_name.'[font_size]', $font_size, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:font_size:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:font_color')?> </td>
			<td>
				<?=form_input($action_field_name.'[font_color]', $font_color, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:font_color:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:shadow_color')?> </td>
			<td>
				<?=form_input($action_field_name.'[shadow_color]', $shadow_color, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:shadow_color:exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:shadow_distance')?> </td>
			<td>
				<?=form_input($action_field_name.'[shadow_distance]', $shadow_distance, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ci:watermark:shadow_distance:exp')?></small>
			</td>
		</tr>
	</tbody>
</table>