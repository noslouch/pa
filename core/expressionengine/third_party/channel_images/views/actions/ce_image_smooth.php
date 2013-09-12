<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('ce:smooth')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?=form_input($action_field_name.'[smooth]', $smooth, 'style="border:1px solid #ccc; width:80%;"')?>
				<small><?=lang('ce:smooth:exp')?></small>
			</td>

		</tr>
	</tbody>
</table>
