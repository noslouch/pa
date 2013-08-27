<div class="TaggerField" id="TaggerField_<?=$field_id?>" data-fieldname="<?=$field_name?>" data-fieldid="<?=$field_id?>">
<table cellspacing="0" cellpadding="0" border="0" style="width:100%">
<tbody>
<tr>
<td <?php if ($config['show_most_used'] == 'yes') echo 'style="width:50%"';?> >

<?php if ($config['single_field'] == 'no'):?>
	<div class="TagBox">
		<h4><?=lang('tagger:insert_tags')?> <small><?=lang('tagger:insert_tags_exp')?></small> </h4>
		<div class="inner">
			<input type="text" class="InstantInsert" />
		</div>
	</div>

	<div class="TagBox TagBoxBottom AssignedTags">
		<h4><?=lang('tagger:assigned_tags')?></h4>
		<div class="inner AssignedTags">
			<?php if (empty($assigned_tags) == TRUE):?> <span class="NoTagsAssigned"><?=lang('tagger:no_assigned_tags')?></span> <?php endif;?>
			<?php foreach($assigned_tags as $tag):?>
				<div class="tag">
					<?=$tag?>
					<input type="hidden" value="<?=$tag?>" name="<?=$field_name?>[tags][]">
					<a href="#"></a>
				</div>
			<?php endforeach;?>
			<br clear="all"/>
		</div>
	</div>
<?php else:?>
	<div class="TagBox">
		<h4><?=lang('tagger:insert_tags')?> <small><?=lang('tagger:insert_tags_exp')?></small> </h4>
		<div class="inner">
			<input type="text" class="SingleTagsInput" name="<?=$field_name?>[single_field]" id="SingleTagsInput_<?=$field_id?>" value="<?=implode('||', $assigned_tags)?>"/>
		</div>
	</div>
<?php endif;?>
</td>
<?php if ($config['show_most_used'] == 'yes'): ?>
<td style="width:50%">
	<div class="TagBox">
		<h4><?=lang('tagger:most_used_tags')?></h4>
		<div class="inner MostUsedTags">
			<?php if (empty($most_used_tags) == TRUE):?> <?=lang('tagger:no_tags_used')?> <?php endif;?>
			<?php foreach($most_used_tags as $tag):?>
				<div class="tag"><span><?=$tag?></span><a href="javascript:void(0)"></a></div>
			<?php endforeach;?>
			<br clear="all"/>
		</div>
	</div>
</td>
<?php endif;?>
</tr>
</tbody>
</table>

	<input name="field_ft_<?=$field_id?>" value="none" type="hidden"/>
</div>
