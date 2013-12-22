<?php if(count($searches) == 0) : ?>

	<div id="no_saved_searches"><?=lang('no_saved_searches')?></div>

<?php else :?>

	<div id="instructions"><?=lang('export_tag_template_instructions')?></div>

	<table class="mainTable" width="100%" cellspacing="0">
		<tr>
			<th width="1%">ID</th>
			<th><?=lang('saved_searches')?></th>
			<th><?=lang('template_code_example')?></th>
		</tr>
		<?php foreach($searches as $id => $data) :?>
		<tr class="savedrules <?=alternator('odd', 'even')?>">
			<td><?=$id?></td>
			<td><?=$data['rule_label']?></td>
			<td><input type="text" class="input-copy" name="field_name" readonly="readonly" value='{exp:hokoku:export profile_id="<?=$profile_id?>" search_id="<?=$id?>" member_id="<?=$member_id?>"}' /></td>
		</tr>
		<?php endforeach ?>
	</table>

<?php endif ?>

<style>
div#instructions p
{
	line-height: 1.4em;
	margin-bottom: 1em;
}

tr.savedrules:hover td
{
	background: #FFF !important;
}

div#no_saved_searches
{
	padding: 10px;
	background: #f2f2f2;
	border: 1px solid #777; 
}
</style>