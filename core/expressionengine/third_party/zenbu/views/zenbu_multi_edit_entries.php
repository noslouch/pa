<?=form_open($action_url)?>
	<?=$hidden_fields?>
	<table width="100%" class="mainTable" cellpadding="0" cellspacing="0">
		<tr>
			<th width="1%" class="center">#</th>
			<th><?=lang('title')?></th>
			<th>
				<?=lang('url_title')?><br />
			</th>
			<th style="white-space: nowrap;">
				<?=lang('status')?><br />
				<span class="" style="font-size: 70%;">(<?=lang('multi_set_all_status_to')?> <?=$status_header_dropdown?>&nbsp;)</span>
			</th>
			<th><?=lang('entry_date')?></th>
			
		<?php $header_sticky_checked = TRUE;?>
		<?php $header_allow_comments_checked = TRUE;?>
		<?php foreach($entry as $entry_date):?>
				<?php $header_sticky_checked = (isset($entry_date['sticky_checked']) && $entry_date['sticky_checked'] === TRUE) ? TRUE : FALSE; ?>
				<?php $header_allow_comments_checked = (isset($entry_date['allow_comments_checked']) && $entry_date['allow_comments_checked'] === TRUE) ? TRUE : FALSE; ?>
		<?php endforeach;?>

			<th><?=form_checkbox('', '', $header_sticky_checked, 'id="sticky" class="toggleAll"')?> <?=lang('sticky')?></th>
			<th><?=form_checkbox('', '', $header_allow_comments_checked, 'id="allow_comments" class="toggleAll"')?> <?=lang('allow_comments')?></th>
		</tr>
		<?php $c = 1;?>
		<?php foreach($entry as $entry_data):?>
			<?php $class = ($c % 2 == 0) ? 'even' : 'odd';?>
			<tr class="<?=$class?>">
				<td class="hoverable">
					<?=$entry_data['entry_id']?>
				</td>
				<td class="hoverable">
					<?=$entry_data['title']?>
				</td>
				<td class="hoverable">
					<?=$entry_data['url_title']?>
				</td>
				<td class="hoverable">
					<?=$entry_data['status']?>
				</td>
				<td class="hoverable">
					<?=$entry_data['entry_date']?>
				</td>
				<td class="hoverable clickable">
					<?=$entry_data['sticky']?>
				</td>
				<td class="hoverable clickable">
					<?=$entry_data['allow_comments']?>
				</td>
			</tr>
			<?php $c++;?>
		<?php endforeach;?>
	</table>
	
	<br />
	
	<button type="submit" class="submit left withloader">
		<span><?=lang('submit')?></span>
		<span class="onsubmit invisible"><?=lang('saving')?></span>
	</button><span class="loader left invisible"></span>
	<p>&nbsp;</p>
<?=form_close();?>
<div class="class"></div>