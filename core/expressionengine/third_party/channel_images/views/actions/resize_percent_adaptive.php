<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable" style="width:80%">
	<thead>
		<tr>
			<th><?=lang('ci:resize:width')?></th>
			<th><?=lang('ci:resize:height')?></th>
			<th><?=lang('ci:resize:percent')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[width]', $width, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[height]', $height, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[percent]', $percent, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ci:resize:percent_adaptive_exp')?></small>