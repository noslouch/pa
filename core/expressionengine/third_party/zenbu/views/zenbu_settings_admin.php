<?=form_open($action_url)?>
<form action="<?=$action_url?>" method="POST">

	<?php if(!empty($member_groups)):?>
		
		<?php foreach($permissions as $key => $val) :?>
			<?php $header_checked[$val] = array();?>
			<?php foreach($member_groups as $member_group):?>
				<?php if(isset($member_group[$val]) && $member_group[$val] == 'y'):?>
					<?php $header_checked[$val][] = ''; ?>
				<?php endif;?>
			<?php endforeach;?>
		<?php endforeach;?>
		
		<table class="mainTable" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<th><?=lang('member_group_name')?></th>
				<?php foreach($permissions as $key => $val) :?>
					<?php $checked = count($header_checked[$val]) == count($member_groups) ? TRUE : FALSE; ?>
					<th id="<?=$val.'_toggle'?>"><?=form_checkbox('', '', $checked, 'class="toggleAll" id="'.$val.'_toggle"')?>&nbsp;<?=($val == 'edit_replace' && version_compare(APP_VER, '2.4', '>')) ? lang('replace_links_for_zenbu') : lang($val)?></th>
				<?php endforeach;?>
			</tr>
			<tr>
				<td></td>
				<?php foreach($permissions as $key => $val) :?>
				<td><span class="subtext"><?=lang($val.'_subtext')?></span></td>
				<?php endforeach ?>
			</tr>
		<?php $c = 1;?>
		<?php foreach($member_groups as $member_group):?>
			<?php $disabled = ($member_group['group_id'] == 1 || $member_group['group_id'] == $current_member_group) ? ' disabled="disabled"' : '';?>
			<?php $clickable = ($member_group['group_id'] == 1 || $member_group['group_id'] == $current_member_group) ? '' : 'clickable';?>
			<?php $row_class = ($c % 2 == 0) ? 'even' : 'odd';?>
			<tr class="<?=$row_class?>">
			
				<td class="hoverable" style="cursor: auto">
					<?='&nbsp;'.$member_group['group_title']?>
					<?php $class = 'member_group_id'; $name = 'members['.$member_group['group_id'].']['.$class.']';?>
					<?=form_hidden($name, $member_group['group_id'])?>
				</td>
				
				<td class="hoverable <?=$clickable?>" style="cursor: auto">
					<?php 
						$class = 'can_admin';
						$toggle_class = (empty($disabled)) ? $class.'_toggle' : '';
						$name = 'members['.$member_group['group_id'].']['.$class.']';
					?>
					<?=form_checkbox($name, 'y', ((isset($member_group[$class]) && $member_group[$class] == 'y') || $member_group['group_id'] == 1) ? TRUE : FALSE, 'class="'.$toggle_class.'"'.$disabled)?>
					<?php if (!empty($disabled)) :?>
						<?=form_hidden($name, 'y')?>
					<?php endif;?>
				</td>
				
				<td class="hoverable <?=$clickable?>" style="cursor: auto">
					<?php 
						$class = 'can_copy_profile';
						$toggle_class = (empty($disabled)) ? $class.'_toggle' : '';
						$name = 'members['.$member_group['group_id'].']['.$class.']';
					?>
					<?=form_checkbox($name, 'y', ((isset($member_group[$class]) && $member_group[$class] == 'y') || $member_group['group_id'] == 1) ? TRUE : FALSE, 'class="'.$toggle_class.'"'.$disabled)?>
					<?php if (!empty($disabled)) :?>
						<?=form_hidden($name, 'y')?>
					<?php endif;?>
				</td>
				
				<td class="hoverable <?=$clickable?>" style="cursor: auto">
					<?php
						$class = 'can_access_settings';
						$toggle_class = (empty($disabled)) ? $class.'_toggle' : '';
						$name = 'members['.$member_group['group_id'].']['.$class.']';
					?>
					<?=form_checkbox($name, 'y', ((isset($member_group[$class]) && $member_group[$class] == 'y') || $member_group['group_id'] == 1) ? TRUE : FALSE, 'class="'.$toggle_class.'"'.$disabled)?>
					<?php if (!empty($disabled)) :?>
						<?=form_hidden($name, 'y')?>
					<?php endif;?>
				</td>
				
				<td class="hoverable <?=($member_group['group_id'] != 0) ? 'clickable' : ''?>" style="cursor: auto">
					<?php
						$class = 'edit_replace';
						$toggle_class = $class.'_toggle';
						$name = 'members['.$member_group['group_id'].']['.$class.']';
					?>
					<?=form_checkbox($name, 'y', ((isset($member_group[$class]) && $member_group[$class] == 'y')) ? TRUE : FALSE, 'class="'.$toggle_class.'"')?>
				</td>

				<td class="hoverable <?=($member_group['group_id'] != 0) ? 'clickable' : ''?>" style="cursor: auto">
					<?php
						$class = 'can_view_group_searches';
						$toggle_class = $class.'_toggle';
						$name = 'members['.$member_group['group_id'].']['.$class.']';
					?>
					<?=form_checkbox($name, 'y', ((isset($member_group[$class]) && $member_group[$class] == 'y')) ? TRUE : FALSE, 'class="'.$toggle_class.'"')?>
				</td>

				<td class="hoverable <?=($member_group['group_id'] != 0) ? 'clickable' : ''?>" style="cursor: auto">
					<?php
						$class = 'can_admin_group_searches';
						$toggle_class = $class.'_toggle';
						$name = 'members['.$member_group['group_id'].']['.$class.']';
					?>
					<?=form_checkbox($name, 'y', ((isset($member_group[$class]) && $member_group[$class] == 'y')) ? TRUE : FALSE, 'class="'.$toggle_class.'"')?>
				</td>
				
			</tr>
			<?php $c++;?>
		<?php endforeach;?>
		</table>
		<br />


		<?php if( ! empty($enable_module_members)) :?>
			
			<?php
			/**
			 * Header checkbox check verification
			 */
			?>
			<?php $header_checked = array();?>
			<?php foreach($enable_module_members as $group_id => $group_title):?>
				<?php if( in_array($group_id, $module_enabled_for) || $group_id == 1) : ?>
					<?php $header_checked[$group_id] = 'y'; ?> 
				<?php endif;?>
			<?php endforeach;?>
			<?php $header_checked = count($header_checked) == count($enable_module_members) ? TRUE : FALSE;?>
			
			<div class="copySettingsTo">
					<table class="mainTable left" style="width: 50%;" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<th width="1%"><?=form_checkbox('', '', $header_checked, 'class="toggleAll" id="toggle-copy"')?></th>
							<th><?=lang('enable_module_for')?></th>
						</tr>
						<tr>
							<td colspan="2">
								<span class="subtext"><?=lang('enable_module_for_subtext')?></span>
							</td>
						</tr>
					<?=form_hidden('enable_module[]', '1')?>
					<?php $c = 1;?>
					<?php foreach($enable_module_members as $group_id => $group_title):?>
						<?php $row_class = ($c % 2 == 0) ? 'even' : 'odd';?>
						<tr class="<?=$row_class?>">
							<?php $checked = in_array($group_id, $module_enabled_for) || $group_id == 1 ? TRUE : FALSE ;?>
							<?php $disabled = $group_id == 1 ? 'disabled="disabled"' : ''; ?>

							<td width="1%" class="hoverable <?php if(empty($disabled)):?>clickable<?php endif; ?>" style="background-image: none; cursor: auto"><?=form_checkbox('enable_module[]', $group_id, $checked, 'class="toggle-copy" ' . $disabled)?></td>
							<td class="hoverable" style="background-image: none; cursor: auto"><?='&nbsp;'.$group_title?></td>
						</tr>
						<?php $c++;?>
					<?php endforeach;?>
					</table>
					<div class="clear"></div>
			</div>
		<?php endif;?>
		
		<br />

		<button type="submit" class="submit left withloader" tabindex="1000">
			<span><?=lang('save_settings')?></span>
			<span class="onsubmit invisible"><?=lang('saving')?> <i class="icon-spinner icon-spin"></i></span>
		</button>
	
	<?php endif;?>
	
	<div class="clear"></div>
</form>