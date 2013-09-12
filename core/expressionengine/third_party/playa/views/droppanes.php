<input type="hidden" name="<?=$field_name?>[selections][]" value=""/>

<div id="<?=$field_id?>" class="playa playa-dp" style="margin: <?=$margin?>">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="playa-dp-pane playa-dp-left">
<?php
	if ($show_filters):
?>
				<div class="playa-dp-filters">
					<div class="playa-dp-filter playa-dp-search">
						<a class="playa-dp-remove playa-dp-disabled" title="<?=lang('remove_filter')?>"></a>
						<a class="playa-dp-add" title="<?=lang('add_filter')?>"></a>
						<label><span><span><?=lang('keywords_label')?></span></span><input type="text" /></label>
						<a class="playa-dp-erase" title="<?=lang('erase_keywords')?>"></a>
					</div>
				</div>
<?php
	endif;
?>
				<div class="playa-entries playa-dp-options" tabindex="0">
					<div class="playa-scrollpane" style="height: <?=$options_height?>px">
						<ul>
<?php
	$this->load->view('droppanes_options_list', array(
		'field_id'           => $field_id,
		'field_name'         => $field_name,
		'entries'            => $entries,
		'selected_entry_ids' => $selected_entry_ids
	));
?>
						</ul>
					</div>
				</div>
			</td>

			<td class="playa-dp-buttons">
				<a class="playa-dp-select playa-dp-disabled" title="Select entry"></a>
				<a class="playa-dp-deselect playa-dp-disabled" title="Deselect entry"></a>
			</td>

			<td class="playa-dp-pane playa-dp-right playa-entries playa-dp-selections" tabindex="0">
				<div class="playa-scrollpane" style="height: <?=$selections_height?>px">
					<ul>
<?php
	foreach ($selected_entries as $entry):
		$this->load->view('entry', array(
			'field_id'   => $field_id,
			'field_name' => $field_name,
			'entry'      => $entry,
			'selected'   => TRUE
		));
	endforeach;
?>
						<li class="playa-entry playa-dp-caboose"></li>
					</ul>
				</div>
			</td>
		</tr>
	</table>
</div>
