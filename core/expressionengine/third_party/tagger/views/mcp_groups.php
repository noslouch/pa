<?php echo $this->view('mcp_header.php'); ?>

<div class="dbody" style="padding:20px;">

<span class="cp_button"><a href="<?=$base_url?>&method=add_group"><?=lang('tagger:create_group')?></a></span>
<br clear="left">

<?php if ($total_groups < 1):?>
<p style="color:red; font-weight:bold;"><?=lang('tagger:no_groups')?></p>

<?php else:?>

<table class="mainTable">
	<thead>
		<tr>
			<th><?=lang('tagger:group_title')?></th>
			<th><?=lang('tagger:group_name')?></th>
			<th><?=lang('tagger:group_desc')?></th>
			<th><?=lang('tagger:delete')?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($groups as $group):?>
		<tr>
			<td><a href="<?=$base_url?>&method=add_group&group_id=<?=$group->group_id?>"><?=$group->group_title?></a></td>
			<td><?=$group->group_name?></td>
			<td><?=$group->group_desc?></td>
			<td><a href="<?=$base_url?>&method=update_group&delete=yes&group_id=<?=$group->group_id?>" class="DeleteIcon DeleteGroup" rel="<?=lang('rel:del_group_warning')?>"></a></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>


<?php endif;?>


</div> <!-- dbody -->
