<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('im:radius')?></th>
			<th <?=$thstyle?>><?=lang('im:sigma')?></th>
			<th <?=$thstyle?>><?=lang('im:amount')?></th>
			<th <?=$thstyle?>><?=lang('im:threshold')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[radius]', $radius, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[sigma]', $sigma, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[amount]', $amount, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[threshold]', $threshold, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small>

<strong><?=lang('im:radius')?>:</strong> <?=lang('im:radius:exp')?> <br />
<strong><?=lang('im:sigma')?>:</strong> <?=lang('im:sigma:exp')?> <br />
<strong><?=lang('im:amount')?>:</strong> <?=lang('im:amount:exp')?> <br />
<strong><?=lang('im:threshold')?>:</strong> <?=lang('im:threshold:exp')?> <br />
</small>
