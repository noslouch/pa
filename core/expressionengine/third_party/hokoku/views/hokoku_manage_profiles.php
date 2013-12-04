<?php $this->EE =& get_instance(); ?>

<?php if(empty($out)) :?>
	<p><?=lang('no_profiles')?></p>
	<a href="<?=$return_to_zenbu_url?>"><?=lang('return_to_zenbu')?></a>
<?php else : ?>

	<?php
	/**
	 * INDIVIDUAL PROFILES
	 */
	?>
	<?php if( isset($access_settings['can_admin_own_profiles']) && $access_settings['can_admin_own_profiles'] == 'y' ) :?>

		<?php 
		/* Check if there are any profiles to start with here */ 
		?>
		<?php $has_profiles = FALSE; ?>
		<?php foreach($out['by_group'][$this->EE->session->userdata('group_id')] as $profile_id => $data) : ?>
			<?php if($data['profile_type'] == 'single') { $has_profiles = TRUE; } ?>
		<?php endforeach ?>

		<?php if($has_profiles) : ?>
			<h2><i class="icon-user"></i> <?=lang('your_profiles')?></h2>
			<table class="mainTable" class="mainTable resultTable" width="100%" cellspacing="0">
				<tr>
					<th width="1%"><?=lang('id')?></th>
					<?php if($this->EE->session->userdata['can_admin_templates'] == 'y') :?>
						<th width="10%"><?=lang('template_tags')?></th>
					<?php endif?>
					<th><?=lang('profile_name')?></th>
					<th width="10%"><?=lang('file_format')?></th>
					<th width="1%"></th>
					<th width="1%"></th>
				</tr>
				<?php foreach($out['by_group'][$this->EE->session->userdata('group_id')] as $profile_id => $data) :?>
				<?php if($data['profile_type'] == 'single'):?>
				<tr class="<?=alternator('odd', 'even')?>">
					<td><?=$data['id']?></td>
					<?php if($this->EE->session->userdata['can_admin_templates'] == 'y') :?>
						<td><?=anchor($base_url.'&method=tag_builder&profile_id=' . $data['id'], lang('get_template_tag'), 'class="dialog"')?></td>
					<?php endif?>
					<td><?=$data['label']?></td>
					<td><?=$data['export_format']?></td>
					<td><?=anchor($base_url.'&method=edit_profiles&profile_id=' . $data['id'], '<i class="icon-edit icon-large"></i>', 'class="editprofile" title="'.lang('edit').'"')?></td>
					<td><?=anchor($base_url.'&method=delete_profile&profile_id=' . $data['id'], '<i class="icon-trash icon-large"></i>', 'class="deleteprofile" title="'.lang('delete').'"')?></td>
				</tr>
				<?php endif ?>
				<?php endforeach ?>
			</table>
		<?php endif ?>

	<?php endif ?>


	<?php
	/**
	 * PROFILES FOR OWN GROUP
	 */
	?>
	<?php if( isset($access_settings['can_view_group_profiles']) && $access_settings['can_view_group_profiles'] == 'y' ) :?>
		<?php foreach($out['by_group'] as $group_id => $profiles):?>

			<?php 
			/* Check if there are any profiles to start with here */ 
			?>
			<?php $has_profiles = FALSE; ?>
			<?php foreach($profiles as $key => $data) :?>
				<?php if($data['profile_type'] == 'group') { $has_profiles = TRUE; } ?>
			<?php endforeach ?>

			<?php if($has_profiles) : ?>
				<br /><h2><i class="icon-group"></i> <?=lang('group_profile')?>: <?=$out['group_name'][$group_id]?></h2>
				<table class="mainTable" class="mainTable resultTable" width="100%" cellspacing="0">
					<tr>
						<th width="1%"><?=lang('id')?></th>
						<?php if($this->EE->session->userdata['can_admin_templates'] == 'y') :?>
							<th width="10%"><?=lang('template_tags')?></th>
						<?php endif?>
						<th><?=lang('profile_name')?></th>
						<th width="10%"><?=lang('file_format')?></th>
						<?php if( isset($access_settings['can_admin_group_profiles']) && $access_settings['can_admin_group_profiles'] == 'y' ) :?>
							<th width="1%"></th>
							<th width="1%"></th>
						<?php endif ?>
					</tr>
					<?php foreach($profiles as $key => $data) :?>
					<?php if($data['profile_type'] == 'group'):?>
					<tr class="<?=alternator('odd', 'even')?>">
						<td><?=$data['id']?></td>
						<?php if($this->EE->session->userdata['can_admin_templates'] == 'y') :?>
							<td><?=anchor($base_url.'&method=tag_builder&profile_id=' . $data['id'], lang('get_template_tag'), 'class="dialog"')?></td>
						<?php endif?>
						<td><?=$data['label']?></td>
						<td><?=$data['export_format']?></td>
						<?php if( isset($access_settings['can_admin_group_profiles']) && $access_settings['can_admin_group_profiles'] == 'y' ) :?>
							<td><?=anchor($base_url.'&method=edit_profiles&profile_id=' . $data['id'], '<i class="icon-edit icon-large"></i>', 'class="editprofile" title="'.lang('edit').'"')?></td>
							<td><?=anchor($base_url.'&method=delete_profile&profile_id=' . $data['id'], '<i class="icon-trash icon-large"></i>', 'class="deleteprofile" title="'.lang('delete').'"')?></td>
						<?php endif ?>
					</tr>
					<?php endif ?>
					<?php endforeach ?>
				</table>
			<?php endif ?>

		<?php endforeach ?>
	<?php endif ?>


	<div class="rightNav">
		<span class="button">
			<a href="<?=$return_to_zenbu_url?>" class="submit"><?=lang('return_to_zenbu_main_page')?></a>
		</span>
	</div>
<?php endif ?>
<div id="profiledeletewarning" class="invisible"><?=lang('profile_delete_warning')?></div>
<div id="hokoku-tag-builder" style="display: none"></div>

<style>

#mainContent table.mainTable
{
	margin-top: 5px;
}

.invisible
{
	display: none;
}

.contents .rightNav
{
	padding:0;
}

.contents .rightNav .button
{
	float: left;
}
</style>
