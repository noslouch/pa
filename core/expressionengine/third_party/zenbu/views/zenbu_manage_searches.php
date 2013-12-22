<!--
	/*	------------------
	 *	Main tables
	 *	------------------
	 */
-->
<?php if(empty($searches_member)) :?>

	<p><?=lang('no_searches_individual')?></p>

<?php else :?>

	<h2><i class="icon-user"></i> <?=lang('your_searches')?></h2>
	<table class="mainTable settingsTable" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<th width="1%"></th>
			<th width="1%"><?=lang('ID')?></th>
			<th><?=lang('saved_searches_list')?></th>
			<th width="1%"></th>
			<?php if( $search_listing_type == 'all_member_groups' ) :?>
			<th width="1%"></th>
			<?php endif ?>
		</tr>

		<?php foreach($searches_member as $search) :?>
		<tr>
			<td class="label"><i class="icon-sort icon-large"></i></td>
			<td class="not-sortable"><?=$search['rule_id']?> 
				<input type="hidden" name="rule_order[]" class="rule_order" value="<?=$search['rule_id']?>" />
			</td>
			<td class="not-sortable"><span class="rule_label"><?=$search['rule_label']?><span class="invisible"><i class="icon-edit icon-large" title="<?=lang('edit_name')?>"></i></span></span>
				<div class="search_title_form invisible">
				<?=form_open("&C=addons_modules&M=show_module_cp&module=zenbu&method=update_search&rule_id=" . $search['rule_id'])?>
					<input type="text" name="search_title" value="<?=$search['rule_label']?>" tabindex="1" />
				<?=form_close()?>
				</div>
			</td>
			<td class="center not-sortable"><a href="<?=BASE?>&C=addons_modules&M=show_module_cp&module=zenbu&method=delete_search&rule_id=<?=$search['rule_id']?>" tabindex="2" class="delete" title="<?=lang('delete')?>"><i class="icon-trash icon-large"></i></a></td>
			<?php if( $search_listing_type == 'all_member_groups' ) :?>
			<td class="not-sortable">
				<a class="copy fancybox-inline" href="javascript:;" title="<?=lang('copy')?>"><i class="icon-copy icon-large"></i></a>
				<div id="member_copy_<?=$search['rule_id']?>" style="display: none" title="ID: <?=$search['rule_id']?> <?=lang('copy_this_search_to')?>">
					<?=form_open($copy_search_action_url)?>
						<input type="hidden" name="rule_id" value="<?=$search['rule_id']?>" />
						<table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<th width="1%"> </th>
								<th width="" style="text-align: left"><?=lang('member_group_name')?></th>
							</tr>
							<?php foreach($member_groups as $id => $name) :?>
							<tr>
								<td><?=form_checkbox('group_id[]', $id, FALSE)?></td>
								<td><?=$name?></td>
							</tr>
							<?php endforeach ?>
						</table>
						<br />
						<button type="submit" class="submit left withloader" tabindex="1000">
							<span><?=lang('copy')?></span>
							<span class="onsubmit invisible"><?=lang('saving')?> <i class="icon-spinner icon-spin"></i></span>
						</button>
					<?=form_close()?>
				</div>
			</td>
			<?php endif ?>
		</tr>
		<?php endforeach ?>
	</table>

<?php endif ?>

<?php foreach($searches_group as $search_g) :?>
	
	<br />
	<h2><i class="icon-group"></i> <?=$search_g['group_name']?></h2>
	<table class="mainTable settingsTable" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<th width="1%"></th>
			<th width="1%"><?=lang('ID')?></th>
			<th><?=lang('saved_searches_list')?></th>
			<th width="1%"></th>
			<th width="1%"></th>
		</tr>
		
		<?php foreach($search_g as $search) :?>
		<?php if(is_array($search)) :?>
		<tr>
			<td class="label"><i class="icon-sort icon-large"></i></td>
			<td class="not-sortable"><?=$search['rule_id']?>
				<input type="hidden" name="rule_order[]" class="rule_order" value="<?=$search['rule_id']?>" />
			</td>
			<td class="not-sortable"><span class="rule_label"><?=$search['rule_label']?><span class="invisible"><i class="icon-edit icon-large" title="<?=lang('edit_name')?>"></i></span></span>
				<div class="search_title_form invisible">
				<?=form_open("&C=addons_modules&M=show_module_cp&module=zenbu&method=update_search&rule_id=" . $search['rule_id'])?>
					<input type="text" name="search_title" value="<?=$search['rule_label']?>" />
				<?=form_close()?>
				</div>
			</td>
			<td class="not-sortable"><a href="<?=BASE?>&C=addons_modules&M=show_module_cp&module=zenbu&method=delete_search&rule_id=<?=$search['rule_id']?>" tabindex="2" class="delete" title="<?=lang('delete')?>"><i class="icon-trash icon-large"></i></a></td>
			<td class="not-sortable">
				<a class="copy fancybox-inline" href="javascript:;" title="<?=lang('copy')?>"><i class="icon-copy icon-large"></i></a>
				<div id="member_copy_<?=$search['rule_id']?>" style="display: none" title="ID: <?=$search['rule_id']?> <?=lang('copy_this_search_to')?>">
					<?=form_open($copy_search_action_url)?>
						<input type="hidden" name="rule_id" value="<?=$search['rule_id']?>" />
						<table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<th width="1%"> </th>
								<th width="" style="text-align: left"><?=lang('member_group_name')?></th>
							</tr>
							<?php foreach($member_groups as $id => $name) :?>
							<tr>
								<td><?=form_checkbox('group_id[]', $id, FALSE)?></td>
								<td><?=$name?></td>
							</tr>
							<?php endforeach ?>
						</table>
						<br />
						<button type="submit" class="submit left withloader" tabindex="1000">
							<span><?=lang('copy')?></span>
							<span class="onsubmit invisible"><?=lang('saving')?> <i class="icon-spinner icon-spin"></i></span>
						</button>
					<?=form_close()?>
				</div>
			</td>
		</tr>
		<?php endif ?>
		<?php endforeach ?>

	</table>

<?php endforeach ?>


<?php /* -- Javascript error messages: multilingual, too! -- */ ?>
<div class="warnings invisible">
	<span class="saved_search_delete_warning"><?=lang('saved_search_delete_warning')?></span>
</div>