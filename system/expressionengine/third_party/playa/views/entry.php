<li class="playa-entry <?=($selected ? 'playa-dp-selected' : '')?>" id="<?=$field_id ?>-option-<?=$entry->entry_id?>" unselectable="on">
	<a><span class="playa-entry-status <?=str_replace(' ', '_', $entry->status)?>">&bull;</span><?=$entry->title?></a>
	<input type="hidden" name="<?=$field_name?>[<?=($selected ? 'selections' : 'options')?>][]" value="<?=$entry->entry_id?>" <?=($selected ? '' : 'disabled="disabled"')?> />
</li>
