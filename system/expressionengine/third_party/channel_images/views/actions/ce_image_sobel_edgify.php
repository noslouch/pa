<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('ce:threshold')?></th>
			<th <?=$thstyle?>><?=lang('ce:foreground')?></th>
			<th <?=$thstyle?>><?=lang('ce:background')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[threshold]', $threshold, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[foreground]', $foreground, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[background]', $background, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ce:sobel_edgify_exp')?></small>
<small>
<strong><?=lang('ce:threshold')?>:</strong> <?=lang('ce:threshold:exp')?> <br />
<strong><?=lang('ce:foreground')?>:</strong> <?=lang('ce:foreground:exp')?> <br />
<strong><?=lang('ce:background')?>:</strong> <?=lang('ce:background:exp')?> <br />
</small>