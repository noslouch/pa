<div class="PerImgActionWrapper">
	<div class="CIField">
		<table cellspacing="0" cellpadding="0" border="0" class="CITable">
			<thead>
				<tr>
					<th colspan="2">
						<h4>
							<?=lang('ci:apply_action')?>
							<small><?=lang('ci:apply_action_exp')?></small>
						</h4>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="width:75px"><strong>1: <?=lang('ci:action')?></strong></td>
					<td>
						<select class="SelectAction">
						<option value=""><?=lang('ci:select_action')?></option>
						<?php foreach($actions as $action_name => $actionobj):?>
							<option value="<?=$actionobj->info['name']?>"><?=$actionobj->info['title']?></option>
						<?php endforeach;?>
						</select>
						<div class="ActionHolder"></div>
					</td>
				</tr>
				<tr class="ImageSizes">
					<td><strong>2: <?=lang('ci:sizes')?></strong></td>
					<td style="text-align:left">
						<input type="radio" name="size" value="ORIGINAL" checked/>&nbsp; <?=lang('ci:original')?>&nbsp;&nbsp;
						<?php foreach($settings['action_groups'] as $count => $group):?>
						<input type="radio" name="size" value="<?=$group['group_name']?>"/>&nbsp; <?=ucfirst($group['group_name'])?>&nbsp;&nbsp;
						<?php endforeach;?>
					</td>
				</tr>
				<tr>
					<td><strong>3: <?=lang('ci:preview')?></strong></td>
					<td><a href="#" class="Button PreviewImage" style="width:50%"><?=lang('ci:preview')?></a></td>
				</tr>
				<tr>
					<td><strong>4: <?=lang('ci:save')?></strong></td>
					<td><a href="#" class="Button SaveImage" style="width:50%"><?=lang('ci:save')?></a></td>
				</tr>
			</tbody>
			<tfoot>
				<tr class="ApplyingAction">
					<td colspan="2"><p><?=lang('ci:applying_action')?></p></td>
				</tr>
			</tfoot>
		</table>
	</div>

	<div class="ActionSettings" style="display:none;">
	<?php foreach($actions as $action_name => &$actionobj):?>
		<div class="<?=$actionobj->info['name']?>">
			<?=base64_encode($actions[$action_name]->display_settings());?>
		</div>
	<?php endforeach;?>
	</div>

	<div class="PreviewHolder"></div>
</div>