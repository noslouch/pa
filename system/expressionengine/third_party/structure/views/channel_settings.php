<?php if ( ! $permissions['admin_channels']) $this->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');?>
<?php if ($channel_check === TRUE): ?>

<div class="padder">

	<?php if( ! $are_page_channels):?>
	<div class="ootb-message">
		<p><?=lang('ootb_message_channel_settings')?>. <span class="ootb-message-go"><a href="http://buildwithstructure.com/documentation/channel_settings/"><?=lang('ootb_message_channel_settings_read')?> &rarr;</a></span></p>
	</div> <!-- close .ootb-message -->
	<?php endif; ?>
	<?php if ($page_count == 0 && $are_page_channels):?>
	<div class="ootb-message-go">
		<p>
			<?=lang('ootb_message_channel_settings_go')?>!
			<span class="ootb-message-btn" id="tree-controls">
				<a href="<?=$add_page_url?>" class="pop <?php if (count($page_choices) > 1): ?>tree-add-solo<?php endif ?>" title="pop"><?=lang('ootb_add_first_page')?></a></li>
			</span>
		</p>
	</div> <!-- close .ootb-message -->
	<?php endif; ?>
		
<?=form_open($action_url, $attributes)?>
<?php if (isset($channel_data)):?>
<table class="structure-table" border="0" cellspacing="0" cellpadding="0" id="channel-settings">
	<thead>
		<tr class="odd">
			<th><?=lang('channel')?></th>
			<th><?=lang('type')?> <a class="structure-help-link" href="http://buildwithstructure.com/documentation/page_types_whats_the_difference_between_a_page_listing_and_asset#types">What are types?</a></th>
			<th><?=lang('settings_options')?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($channel_data as $channel_id => $value):?>
		<?php $type = $channel_data[$channel_id]['type']; ?>
		<tr>
			<td class="channel-titles"><?=$value['channel_title']?></td>
			<td class="type-picker">
				<select name="<?=$channel_id?>[type]" class="select">
					<option value="unmanaged"<?=set_select($channel_id, 'unmanaged', (($type == 'unmanaged') ? TRUE : FALSE));?> ><?=lang('unmanaged')?></option>
					<option value="page"<?=set_select($channel_id, 'page', (($type == 'page') ? TRUE : FALSE));?> ><?=lang('page')?></option>
					<option value="listing"<?=set_select($channel_id, 'listing', (($type == 'listing') ? TRUE : FALSE));?> ><?=lang('listing')?></option>
					<option value="asset"<?=set_select($channel_id, 'asset', (($type == 'asset') ? TRUE : FALSE));?> ><?=lang('asset')?></option>
				</select>
				<div class="show-option">
					<div class="option<?php if($type == 'page'):?> active<?php endif;?>">
						<label for="<?=$channel_id?>[show_in_page_selector]">
							<input type="checkbox" id="<?=$channel_id?>[show_in_page_selector]" name="<?=$channel_id?>[show_in_page_selector]" class="checkbox" value="y" <?php if ($channel_data[$channel_id]['show_in_page_selector'] != 'n') echo ' checked="checked"'; ?> /><?=lang('show_in_page_selector')?>
						</label>
					</div>
				</div>
			</td>
			<td class="setting-options" data-type="<?=$type?>">
				<div class="option unmanaged<?php if($type == 'unmanaged' || $type === NULL):?> active<?php endif;?>">
					<em><?=lang('settings_n_a')?> </em>
				</div>
				<div class="option page listing<?php if($type == 'page' || $type == 'listing'):?> active<?php endif;?>">
					<select name="<?=$channel_id?>[template_id]">
						<option value="0"><?=lang('select_template')?></option>
						<?php foreach ($templates as $template):?>
						<option value="<?=$template['template_id']?>"
							<?=set_select($template['template_id'], $template['group_name'].'/'.$template['template_name'], (($template['template_id'] == $channel_data[$channel_id]['template_id']) ? TRUE : FALSE));?>>
							<?=$template['group_name'].'/'.$template['template_name'];?>
						</option>
						<?php endforeach; ?>
					</select>
					
				</div>
				<div class="option asset<?php if($type == 'asset'):?> active<?php endif;?>">
					<label for="<?=$channel_id?>[split_assets]">
						<input type="checkbox" id="<?=$channel_id?>[split_assets]" name="<?=$channel_id?>[split_assets]" class="checkbox" value="y" <?php if ($channel_data[$channel_id]['split_assets'] == 'y') echo ' checked="checked"'; ?> /><?=lang('split_assets')?>
					</label>
				</div>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<div class="table-controls"><a href="#" class="submit">Save Channel Settings</a></div>
</form>
<?php endif; ?>

<?php else: ?>

	<div class="empty-state">
		<p><?=lang('channel_settings_none')?>: <strong><a href="<?=BASE.AMP.'C=admin_content'.AMP.'M=channel_management';?>"><?=lang('channel_settings_add')?> &rarr;</a></strong></p>
	</div> <!-- close .empty-state -->

</div> <!-- close .padder -->

<?php endif; ?>