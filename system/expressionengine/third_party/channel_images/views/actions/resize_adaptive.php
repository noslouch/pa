<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('ci:resize:width')?></th>
			<th><?=lang('ci:resize:height')?></th>
			<th><?=lang('ci:resize:quality')?></th>
			<th><?=lang('ci:resize:upsizing')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[width]', $width, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[height]', $height, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[quality]', $quality, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_dropdown($action_field_name.'[upsizing]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $upsizing)?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ci:resize:adaptive_exp')?></small>