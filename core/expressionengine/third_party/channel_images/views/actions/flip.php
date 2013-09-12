<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable" style="width:80%">
	<thead>
		<tr>
			<th><?=lang('ci:flip:axis')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_dropdown($action_field_name.'[axis]', array('horizontal' => lang('ci:flip:horizontal'), 'vertical' => lang('ci:flip:vertical'), 'both' => lang('ci:flip:both')), $axis); ?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ci:flip:exp')?></small>