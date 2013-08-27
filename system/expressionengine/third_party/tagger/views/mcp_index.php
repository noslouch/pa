<?php echo $this->view('mcp_header'); ?>


<div class="dbody" style="padding:20px;">

<table class="TaggerTable" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
			<th style="width:50px"><?=lang('tagger:id')?></th>
			<th><?=lang('tagger:tag_name')?></th>
			<th style="width:80px"><?=lang('tagger:total_entries')?></th>
			<th><?=lang('tagger:groups')?></th>
			<th style="width:50px"><?=lang('tagger:action')?></th>
		</tr>
	</thead>
	<tbody>

	</tbody>
</table>


<table class="mainTable MergeTags">
	<thead>
		<tr>
			<th colspan="3"><?=lang('tagger:merge_tags')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong><?=lang('tagger:tagids')?></strong></td>
			<td><?=lang('tagger:merge_exp')?><br /><input name="tag_ids" type="text" value="" class="tagids"/> </td>
			<td><input name="submit" class="submit" type="submit" value="<?=lang('tagger:merge_tags')?>"/></td>
		</tr>
	</tbody>
</table>

</div> <!-- dbody -->
