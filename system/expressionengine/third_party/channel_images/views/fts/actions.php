<a href="#" class="AddActionGroup"><img src="<?=CHANNELIMAGES_THEME_URL?>img/add.png" /><?=lang('ci:add_action_group')?></a>

<div class="default_actions">
<?php foreach($actions as $action_name => &$actionobj):?>
	<div class="<?=$actionobj->info['name']?>">
		<?=base64_encode('
		<tr>
			<td></td>
			<td>'.$actions[$action_name]->info['title'].'</td>
			<td>
			'.$actions[$action_name]->display_settings().'
			<input type="hidden" class="action_step" name="channel_images[action_groups][][actions]['.$action_name.'][step]" value="">
		</td>
		<td><a href="#" class="MoveAction">&nbsp;</a><a href="#" class="DelAction">&nbsp;</a></td>
		</tr>
		');?>
	</div>
<?php endforeach;?>
</div>

<script id="ChannelImagesActionGroup" type="text/x-jquery-tmpl">
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable ActionGroup">
	<thead>
		<tr class="group_name">
			<th colspan="4">
				<h4>{{{group_name}}}</h4> <small><?=lang('ci:click2edit')?></small>
				<input type="hidden" class="gname" name="channel_images[action_groups][][group_name]" value="{{{group_name}}}">
				<span class="imageprev">
					<input type="checkbox" name="channel_images[action_groups][][wysiwyg]" value="yes" class="wysiwyg" {{#wysiwyg}}checked{{/wysiwyg}}> <?=lang('ci:wysiwyg')?> &nbsp;&nbsp;
					<input type="checkbox" name="channel_images[action_groups][][editable]" value="yes" class="editable" {{#editable}}checked{{/editable}}> <?=lang('ci:editable')?> &nbsp;&nbsp;
					<input type="radio" name="channel_images[small_preview]" value="" class="small_preview" {{#small_preview}}checked{{/small_preview}}> <?=lang('ci:small_prev')?> &nbsp;&nbsp;
					<input type="radio" name="channel_images[big_preview]" value="" class="big_preview" {{#big_preview}}checked{{/big_preview}} > <?=lang('ci:big_prev')?>
					<a href="#" class="DelActionGroup">&nbsp;</a>
				</span>
			</th>
		</tr>
		<tr class="action_cols">
			<th style="width:50px"><?=lang('ci:step')?></th>
			<th style="width:150px"><?=lang('ci:action')?></th>
			<th><?=lang('ci:settings')?></th>
			<th style="width:40px"></th>
		</tr>
	</thead>
	<tbody>
	{{#actions}}
		<tr>
			<td>1</td>
			<td>{{{action_name}}}</td>
			<td>
				<a href="#" class="SettingsToggler sHidden" rel="<?=lang('ci:hide_settings')?>"><?=lang('ci:show_settings')?></a>
				<div class="actionsettings" style="display:none">
				{{{action_settings}}}
				</div>
				<input type="hidden" class="action_step" name="channel_images[action_groups][][actions][{{{action}}}][step]" value="">
			</td>
			<td><a href="#" class="MoveAction">&nbsp;</a><a href="#" class="DelAction">&nbsp;</a></td>
		</tr>
	{{/actions}}
	{{^actions}}
		<tr class="NoActions"><td colspan="4"><?=lang('ci:no_actions')?></td></tr>
	{{/actions}}
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4">
				<select>
				<option value=""><?=lang('ci:add_action')?></option>
				<?php foreach($actions as $action_name => &$actionobj):?>
					<option value="<?=$actionobj->info['name']?>"><?=$actionobj->info['title']?></option>
				<?php endforeach;?>
				</select>
			</td>
		</tr>
	</tfoot>
	</table>
</script>
