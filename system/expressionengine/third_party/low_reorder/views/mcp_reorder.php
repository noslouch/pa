
<?php if ( ! empty($set['set_notes'])): ?>
	<div id="reorder-instructions"><?=$set['set_notes']?></div>
<?php endif; ?>

<form method="post" action="<?=$base_url?>&amp;method=save_order">
	<div>
		<input type="hidden" name="XID" value="<?=XID_SECURE_HASH?>" />
		<input type="hidden" name="set_id" value="<?=$set['set_id']?>" />
		<input type="hidden" name="cat_id" value="<?=$set['cat_id']?>" />
		<input type="hidden" name="sort" value="<?=@$params['sort']?>" />
	</div>

	<?php if ($select_category): ?>
		<div id="reorder-category-select">
			<input type="hidden" name="url" value="<?=$url?>" />
			<select name="category">
				<?php if ( ! $show_entries): ?><option><?=lang('select_category')?>&hellip;</option><?php endif; ?>
				<?php foreach ($category_groups AS $key => $group): ?>
					<?php if ($total_groups > 1): ?><optgroup label="<?=htmlspecialchars($group['group_name'])?>"><?php endif; ?>
					<?php foreach ($group['categories'] AS $v): ?>
						<?php $indent = ($i = ($v[5] - 1)) ? repeater(NBS.NBS, $i) : ''; ?>
						<option value="<?=$v[0]?>"<?php if ($v[0] == $selected_category): ?> selected="selected"<?php endif; ?>>
							<?=$indent.$v[1]?>
						</option>
					<?php endforeach; ?>
					<?php if ($total_groups > 1): ?></optgroup><?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<?php if ($show_entries): ?>
		<div id="reorder-entries">

			<?php if (empty($entries)): ?>
				<p class="alert"><?=lang('no_entries_found')?></p>
			<?php else: ?>
				<div id="reorder-container" class="odd">
				<ul id="low-reorder">
					<?php for($i = 0, $total = count($entries); $i < $total; $i++): $row = $entries[$i]; ?>
						<li id="entry-<?=$row['entry_id']?>">
							<input type="hidden" name="entries[]" value="<?=$row['entry_id']?>" />
							<div class="order"><?=($i + 1)?></div>
							<div class="title"><?=$row['title']?></div>
							<?php if ( ! empty($row['hidden'])): ?>
								<?php foreach ($row['hidden'] AS $j => $hidden): ?>
									<div class="hidden" class="hidden-<?=$j?>"><?=$hidden?></div>
								<?php endforeach; ?>
							<?php endif; ?>
						</li>
					<?php endfor; ?>
				</ul>
				</div>
				<p id="reorder-save">
					<input type="submit" class="submit" value="<?=lang('save')?>" />
					<label>
						<input type="checkbox" name="clear_caching" value="y"
						<?php if (@$set['clear_cache'] == 'y'): ?> checked="checked"<?php endif; ?> />
						<?=lang('clear_cache')?>
					</label>
				</p>	
			<?php endif; ?>
		</div>
	<?php endif; ?>

</form>