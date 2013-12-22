<?=form_open($action_url)?>
	<?=$hidden_fields?>
	
	<?php if(count($entry) == 1):?>
		<?=lang('delete_entry_confirm')?>
	<?php else:?>
		<?=lang('delete_entries_confirm')?>
	<?php endif;?>
	<p class="notice">* <?=lang('action_can_not_be_undone')?> *</p>
	
	<table width="" class="mainTable" cellpadding="0" cellspacing="0">
		<tr>
			<th width="1%" class="center">#</th>
			<th><?=lang('title')?></th>
		</tr>
		<?php $c = 1;?>
		<?php foreach($entry as $entry_data):?>
			<?php $class = ($c % 2 == 0) ? 'even' : 'odd';?>
			<tr class="<?=$class?>">
				<td>
					<?=$entry_data['entry_id']?>
				</td>
				<td>
					<?=$entry_data['title']?>
				</td>
			</tr>
			<?php $c++;?>
		<?php endforeach;?>
	</table>
	
	<br />
	
	<button type="submit" class="submit left withloader">
		<span><?=lang('delete')?></span>
		<span class="onsubmit invisible"><?=lang('deleting')?></span>
	</button><span class="loader left invisible"></span>
	
	<a href="<?=BASE.AMP.'C=addons_modules&M=show_module_cp&module=zenbu&return_to_zenbu=y'?>" class="left cancel"><?=lang('cancel_and_return');?></a>
	

<?=form_close();?>
<div class="class"></div>

<style>
	.cancel
	{
		padding: 5px 0 0 15px;
	}
</style>