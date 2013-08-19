<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable" style="width:80%">
	<thead>
		<tr>
			<th><?=lang('ci:rotate:degrees')?></th>
			<th><?=lang('ci:rotate:only_if')?></th>
			<th><?=lang('ci:rotate:bg_color')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[degrees]', $degrees, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_dropdown($action_field_name.'[only_if]', array('always' => lang('ci:rotate:always'), 'width_bigger' => lang('ci:rotate:width_bigger'), 'height_bigger' => lang('ci:rotate:height_bigger')), $only_if); ?></td>
			<td><?=form_input($action_field_name.'[background_color]', $background_color, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ci:rotate:exp')?></small>