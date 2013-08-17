<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('ce:thickness')?></th>
			<th <?=$thstyle?>><?=lang('ce:color')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[thickness]', $thickness, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[color]', $color, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ce:border_exp')?></small>
<small>
<strong><?=lang('ce:thickness')?>:</strong> <?=lang('ce:thickness:exp')?> <br />
<strong><?=lang('ce:color')?>:</strong> <?=lang('ce:color:exp')?> <br />
</small>