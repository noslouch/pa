<?=form_open($action_url)?>
<form action="<?=$action_url?>" method="POST">

<div class="editAccordion collapsed">
	<h3 class="collapsed"><?=lang('general_settings')?></h3>
	<div style="display: none">
	<table class="templateTable" cellpadding="0" cellspacing="0" border="0">
	<?php foreach($general_settings as $row) :?>
		<tr>
			<td><?=$row['label']?></td>
			<td><?=$row['form_field']?></td>
		</tr>
	<?php endforeach;?>
	</table>
	</div>
</div>




<br />
<div class="clear"></div>

<?php
/**
 * The Channel Switch tabs/dropdown
 */
?>
<?php if(count($channels['channel_data']) > 10) :?>

	<select id="channel_select">
		
		<?php foreach($channels['channel_data'] as $key => $channel):?>
			<?php $current = $current_channel == $channel['id'] || ($current_channel === '0' && $channel['id'] === '0') ? 'selected="selected"' : ''; ?>
			<option value="<?=$channel['id']?>" <?=$current?>><?=$channel['channel_title']?></option>
		<?php endforeach;?>

			<?php $current = empty($current_channel) && $current_channel !== '0' ? 'selected="selected"' : ''; ?>
			<option value="" <?=$current?>>- <?=lang('all_channels')?> -</option>
	</select>

<?php else : ?>

	<ul class="tab_menu" id="tab_menu_tabs">
		
		
		<?php foreach($channels['channel_data'] as $key => $channel):?>
			<?php $current = $current_channel == $channel['id'] || ($current_channel === '0' && $channel['id'] === '0') ? 'current' : ''; ?>
			<li class="content_tab <?=$current?>"><a class="nav_ch" rel="nav_ch_<?=$channel['id']?>" href="<?=$settings_view_url?>&channel_id=<?=$channel['id']?>"><?=$channel['channel_title']?></a></li>
		<?php endforeach;?>
		
			<?php $current = empty($current_channel) && $current_channel !== '0' ? 'current' : ''; ?>
			<li class="content_tab <?=$current?>"><a class="nav_ch" rel="nav_ch_all" href="<?=$settings_view_url?>">- <?=lang('all_channels')?> -</a></li>
	</ul>

<?php endif; ?>

<span class="loader right invisible"></span>
<div class="clear"></div>
<br />

<?php if( empty($current_channel) && strlen($current_channel) == 0 ):?>
	<!-- 
	/*
	 * Mass check fields
	 */
	 -->
	<?php $break = round(count($mass_check_fields) / 4); ?>
	<?php $c = 1;?>
	<ul class="left" style="margin-right: 2em;">
	<?php foreach($mass_check_fields as $data => $lang) :?>
		
		<li><label for="<?=$data?>"><input type="checkbox" class="mass_checker" id="<?=$data?>" data-check="<?=$data?>" /> <?=lang('check_all')?> <?=$lang?></label></li>
		<?php if($c % $break == 0) :?></ul><ul class="left" style="margin-right: 2em;"><?php endif ?>
		<?php $c++ ;?>

	<?php endforeach ?>
	</ul>
<?php endif ?>

<div class="clear"></div>


	<!-- 
	/*
	 * Channels
	 */
	 -->
	<?php foreach($channels['channel_data'] as $key => $channel):?>
	<?php if( $current_channel == $channel['id'] || ($current_channel === '0' && $channel['id'] == '0') || (empty($current_channel) && $current_channel !== '0') ) : ?>
	<div class="ch_tables nav_ch_<?=$channel['id']?>">
		<br />
		<h2><?=$channel['channel_title']?></h2>
		<table class="mainTable settingsTable" cellpadding="0" cellspacing="0" border="0">
				<thead>
					<tr>
						<th width="1%">&nbsp;</th>
						<th><?=lang('field')?></th>
						<th width="5%" class="center" style="white-space: nowrap;">
							<?php $header_checked = TRUE;?>
							<?php foreach($setting_labels[$channel["id"]] as $order) :?>
								<?php asort($order);?>
								<?php foreach($order as $setting_label) :?>
									<?php if($setting_label['checked'] === FALSE) { $header_checked = FALSE; }; ?>
								<?php endforeach;?>
							<?php endforeach;?>
							<?=form_checkbox('', '', $header_checked, 'class="toggleAll" id="toggle-'.$channel['id'].'"')?>&nbsp;
							<?=lang('show')?>
						</th>
						<th width="5%" class="center" style="white-space: nowrap;"><?=lang('field_order')?></th>
						<th width="30%" style="white-space: nowrap;"><?=lang('extra_options')?></th>
					</tr>
				</thead>
			
			<?php /* -- Labels -- */ ?>
			<?php $c = 1;?>
			<?php foreach($setting_labels[$channel["id"]] as $order) :?>
				<?php asort($order);?>
				<?php foreach($order as $setting_label) :?>
					<?php $row_class = ($c % 2 == 0) ? 'even' : 'odd';?>
					
					<tr class="<?=$row_class?>">
						<td class="label"><i class="icon-sort icon-large"></i></td>
						<td class="cursor hoverable"><?=$setting_label['option_title']?></td>
						<td class="hoverable center clickable not-sortable">
							<?php $selected = '';?>
							<?=form_checkbox($setting_label['input_name'], 'y', $setting_label['checked'], 'class="toggle-'.$channel['id'].'"')?>
						</td>
						<td class="hoverable field-order center not-sortable">
							<span class="table_<?=$channel["id"]?>"></span>
							<input type="hidden" name="<?=$setting_label['order_input_name']?>" value="" class="table_<?=$channel["id"]?>" />
						</td>
						<td class="hoverable not-sortable">
							<?php foreach($extra_settings as $key => $option_name) :?>
								<?=(isset($setting_label[$option_name])) ? $setting_label[$option_name] : ''?>
							<?php endforeach;?>
							&nbsp;
						</td>
					</tr>
					<?php $c++;?>
				<?php endforeach;?>
			<?php endforeach;?>
			
			
			
		</table>

	
	</div>
	<?php endif ?>

	<?php endforeach;?>
	
	<!-- 
	/*
	 * Copy settings to...
	 */
	 -->
	<?php if( ! empty($member_groups) ):?>
		<div class="clear"></div>
		<br />
		<a href="javascript:;" id="copySettingsTo" style="display: none;"><?=lang('save_this_profile_for_link')?></a>
		<div class="copySettingsTo" style="display: block;">
			<table class="mainTable left" style="width: 50%;" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<th width="1%"><?=form_checkbox('', '', '', 'class="selectAll" id="toggle-copy"')?></th>
					<th><label for="toggle-copy"><?=lang('save_this_profile_for')?></label></th>
				</tr>
			<?php foreach($member_groups as $member_group):?>
				<tr>
					<?php $checked = FALSE;?>
					<td width="1%" class="hoverable selectable" style="background-image: none; cursor: auto"><?=form_checkbox('copy_to_members[]', $member_group['group_id'], $checked, 'class="toggle-copy"')?></td>
					<td class="hoverable" style="background-image: none; cursor: auto"><?='&nbsp;'.$member_group['group_title']?></td>
				</tr>
			<?php endforeach;?>
			</table>
			<div class="clear"></div>
			<br />
			<?=form_label(form_checkbox('clear_individual_settings', 'y', FALSE, 'class="" id=""') . ' ' . lang('clear_individual_settings'))?>
		</div>
		
	<?php endif;?>

	<br />

	<button type="submit" class="submit left withloader" tabindex="1000">
		<span><?=lang('save_settings')?></span>
		<span class="onsubmit invisible"><?=lang('saving')?> <i class="icon-spinner icon-spin"></i></span>
	</button>
	
	<div class="clear"></div>
</form>
<div class="warnings invisible">
<span class="part1"><?=lang('warning_channel_fields_no_display')?></span>
<span class="part2"><?=lang('warning_save_confirm')?></span>
<span class="forgottosave"><?=lang('warning_forgot_to_save')?></span>
</div>

<style>
.ui-sortable-helper
{
	-webkit-box-shadow: 0px 2px 3px 1px rgba(0, 0, 0, 0.1);
	-moz-box-shadow: 0px 2px 3px 1px rgba(0, 0, 0, 0.1);
	box-shadow: 0px 2px 3px 1px rgba(0, 0, 0, 0.1);
	border: 1px solid;
}

.ui-state-highlight td
{
	background: #DDD !important;
	-webkit-box-shadow: inset 0px 2px 2px 0px rgba(0, 0, 0, 0.4);
	-moz-box-shadow: inset 0px 2px 2px 0px rgba(0, 0, 0, 0.4);
	box-shadow: inset 0px 2px 2px 0px rgba(0, 0, 0, 0.4);	
}
</style>