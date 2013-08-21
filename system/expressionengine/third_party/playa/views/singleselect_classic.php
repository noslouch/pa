<input type="hidden" name="<?=$field_name?>[selections][]" value=""/>

<select name="<?=$field_name?>[selections][]">
<?php foreach ($entries as $entry): ?>
	<option value="<?=$entry->entry_id?>" <?php if ($selected_entry && $entry->entry_id == $selected_entry->entry_id): ?>selected="selected"<?php endif ?>>
		<?=$entry->title?>
	</option>
<?php endforeach ?>
</select>
