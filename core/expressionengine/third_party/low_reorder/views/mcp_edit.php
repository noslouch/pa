<form method="post" action="<?=$base_url?>&amp;method=save_set" id="reorder-settings">
	<div>
		<input type="hidden" name="XID" value="<?=XID_SECURE_HASH?>" />
		<input type="hidden" name="set_id" value="<?=($set['set_id']?$set['set_id']:'new')?>" />
	</div>

	<table class="mainTable" cellspacing="0" cellpadding="0">
		<colgroup>
			<col class="key" />
			<col class="val" />
		</colgroup>
		<thead>
			<tr>
				<th colspan="2"><?=lang('set_details')?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="<?=low_zebra()?>">
				<td>
					<label for="set_label"><em>*</em> <?=lang('set_label')?></label>
					<div class="low-notes"><?=lang('set_label_help')?></div>
				</td>
				<td><input class="medium" type="text" name="set_label" id="set_label" value="<?=htmlspecialchars($set['set_label'])?>" /></td>
			</tr>
			<tr class="<?=low_zebra()?>">
				<td>
					<label for="set_name"><em>*</em> <?=lang('set_name')?></label>
					<div class="low-notes"><?=lang('set_name_help')?></div>
				</td>
				<td><input class="medium" type="text" name="set_name" id="set_name" value="<?=htmlspecialchars($set['set_name'])?>" /></td>
			</tr>
			<tr class="<?=low_zebra()?>">
				<td class="multi">
					<label for="set_notes"><?=lang('set_notes')?></label>
					<div class="low-notes"><?=lang('set_notes_help')?></div>
				</td>
				<td><textarea name="set_notes" id="set_notes" rows="4" cols="40"><?=htmlspecialchars($set['set_notes'])?></textarea></td>
			</tr>
			<tr class="<?=low_zebra()?>">
				<td class="multi">
					<label for="new_entries"><?=lang('new_entries')?></label>
					<div class="low-notes"><?=lang('new_entries_help')?></div>
				</td>
				<td>
					<select name="new_entries" id="new_entries">
						<?php foreach (array('append', 'prepend') AS $pend): ?>
							<option value="<?=$pend?>"
								<?php if ($set['new_entries'] == $pend): ?> selected="selected"<?php endif; ?>>
								<?=lang($pend)?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class="<?=low_zebra()?>">
				<td><span class="label"><?=lang('check_cache_label')?></span></td>
				<td><?=$yesno_cache?></td>
			</tr>
		</tbody>
	</table>
	<table class="mainTable" cellspacing="0" cellpadding="0">
		<colgroup>
			<col class="key" />
			<col class="val" />
		</colgroup>
		<thead>
			<tr>
				<th colspan="2"><?=lang('filter_options')?></th>
			</tr>
		</thead>
		<tbody>

			<tr class="<?=low_zebra()?>">
				<td><span class="label"><?=lang('show_expired_entries')?></span></td>
				<td><?=$yesno_expired?></td>
			</tr>

			<tr class="<?=low_zebra()?>">
				<td><span class="label"><?=lang('show_future_entries')?></span></td>
				<td><?=$yesno_future?></td>
			</tr>

			<!-- List of channels -->
			<tr class="<?=low_zebra()?>">
				<td class="multi"><span class="label"><em>*</em> <?=lang('channels')?></span></td>
				<td id="channel-settings"><?=$select_channel?></td>
			</tr>

			<!-- List of statuses -->
			<tr class="<?=low_zebra()?>">
				<td class="multi"><label for="settings-status"><em>*</em> <?=lang('statuses')?></label></td>
				<td id="status-settings"><?=$select_status?></td>
			</tr>

			<!-- List of categories -->
			<tr class="<?=low_zebra()?>">
				<td class="multi">
					<label for="settings-category-options"><?=lang('categories')?></label>
				</td>
				<td id="category-settings">

					<div id="category-options">
						<select name="cat_option" id="settings-category-options">
							<?php foreach (array('all', 'some', 'one') AS $option): ?>
								<option value="<?=$option?>"
								<?php if ($set['cat_option'] == $option): ?> selected="selected"<?php endif; ?>>
									<?=lang('show_'.$option)?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<!-- List of categories -->
					<div class="category-options" id="category-some"
						<?php if ($set['cat_option'] != 'some'): ?> style="display:none"<?php endif; ?>>
						<label><span><?=lang('select_categories')?></span>
						<select name="parameters[category][]" multiple="multiple" size="10">
						<?php foreach ($category_groups AS $group): ?>
							<optgroup label="<?=htmlspecialchars($group['group_name'])?>">
							<?php foreach ($group['categories'] AS $v): ?>
								<?php $indent = ($i = ($v[5] - 1)) ? repeater(NBS.NBS, $i) : ''; ?>
								<option value="<?=$v[0]?>"<?php if (in_array($v[0], $selected_category_ids)): ?> selected="selected"<?php endif; ?>>
									<?=$indent.htmlspecialchars($v[1])?>
								</option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
						</select></label>
					</div>

					<!-- List of category groups -->
					<div class="category-options" id="category-one"
						<?php if ($set['cat_option'] != 'one'): ?> style="display:none"<?php endif; ?>>
						<label><span><?=lang('select_category_groups')?></span>
						<?=$select_category_groups?></label>
					</div>
				</td>
			</tr>

			<!-- Search filter options -->
			<tr class="<?=low_zebra()?>">
				<td class="multi"><label><?=lang('search_fields')?></label></td>
				<td id="search-settings"><button type="button"><b>+</b> <?=lang('add_search_filter')?></button></td>
			</tr>

		</tbody>
	</table>

	<?php if (count($member_groups)) : ?>
		<table class="mainTable" cellspacing="0" cellpadding="0">
			<colgroup>
				<col class="key" />
				<col class="val" />
			</colgroup>
			<thead>
				<tr>
					<th colspan="2"><?=lang('permissions')?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($member_groups AS $group_id => $group_name) : ?>
					<tr class="<?=low_zebra()?>">
						<td>
							<span class="label"><?=htmlspecialchars($group_name)?></span>
						</td>
						<td class="permissions">
							<?php for ($p = 0; $p <= 2; $p++): ?>
								<label>
									<input type="radio" name="permissions[<?=$group_id?>]" value="<?=$p?>"
									<?php if (@$set['permissions'][$group_id] == $p): ?>checked="checked"<?php endif; ?> />
									<?=lang('permissions_'.$p)?>
								</label>
							<?php endfor; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<p>
		<input type="submit" value="<?=lang('save')?>" class="submit" />
		<input type="submit" name="reorder" value="<?=lang('save_and_reorder')?>" class="submit" />
	</p>
</form>

<div id="field-template" style="display:none">
	<?=$select_field_name?> = <?=$select_field_value?> <button type="button" class="remove"><b>&minus;</b> <?=lang('remove')?></button>
</div>

<script>
	var LOW_Reorder_fields = <?=$json_fields?>;
</script>