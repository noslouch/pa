<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('ce:amount')?></th>
			<th <?=$thstyle?>><?=lang('ce:radius')?></th>
			<th <?=$thstyle?>><?=lang('ce:threshold')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[amount]', $amount, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[radius]', $radius, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[threshold]', $threshold, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ce:sharpen_exp')?></small>
<small>
<strong><?=lang('ce:amount')?>:</strong> <?=lang('ce:amount:exp')?> <br />
<strong><?=lang('ce:radius')?>:</strong> <?=lang('ce:radius:exp')?> <br />
<strong><?=lang('ce:threshold')?>:</strong> <?=lang('ce:threshold_sharpen:exp')?> <br />
</small>
