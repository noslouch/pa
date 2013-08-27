<?php echo $this->view('mcp_header'); ?>

<div class="dbody" style="padding:20px;">

<?=form_open($base_url_short.AMP.'method=update_group')?>
<table class="mainTable">
	<thead>
		<tr>
			<th width="40%"><?=lang('tagger:question')?></th>
			<th width="60%"><?=lang('tagger:answer')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong><?=lang('tagger:group_title')?></strong></td>
			<td><input name="group_title" type="text" value="<?=$group_title?>"/></td>
		</tr>
		<tr>
			<td><strong><?=lang('tagger:group_name')?></strong> <br /> <?=lang('tagger:group_name_exp')?></td>
			<td><input name="group_name" type="text" value="<?=$group_name?>"/></td>
		</tr>
		<tr>
			<td><strong><?=lang('tagger:group_desc')?></strong></td>
			<td><input name="group_desc" type="text" class="fullfield" value="<?=$group_desc?>"/></td>
		</tr>
	</tbody>
</table>

<input name="group_id" type="hidden" value="<?=$group_id?>" />

<input name="submit" class="submit" type="submit" value="Save"/>

<?=form_close()?>


</div> <!-- dbody -->
