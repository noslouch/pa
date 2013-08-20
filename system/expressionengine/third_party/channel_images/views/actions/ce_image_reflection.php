<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('ce:gap_height')?></th>
			<th <?=$thstyle?>><?=lang('ce:start_opacity')?></th>
			<th <?=$thstyle?>><?=lang('ce:end_opacity')?></th>
			<th <?=$thstyle?>><?=lang('ce:reflection_height')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[gap_height]', $gap_height, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[start_opacity]', $start_opacity, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[end_opacity]', $end_opacity, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[reflection_height]', $reflection_height, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ce:reflection_exp')?></small>
<small>
<strong><?=lang('ce:gap_height')?>:</strong> <?=lang('ce:gap_height:exp')?> <br />
<strong><?=lang('ce:start_opacity')?>:</strong> <?=lang('ce:start_opacity:exp')?> <br />
<strong><?=lang('ce:end_opacity')?>:</strong> <?=lang('ce:end_opacity:exp')?> <br />
<strong><?=lang('ce:reflection_height')?>:</strong> <?=lang('ce:reflection_height:exp')?> <br />
</small>