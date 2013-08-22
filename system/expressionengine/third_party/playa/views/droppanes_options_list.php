<?php
	foreach ($entries as $entry):
		if (in_array($entry->entry_id, $selected_entry_ids)):
?>
<li class="playa-entry playa-dp-placeholder" id="<?=$field_id ?>-option-<?=$entry->entry_id?>-placeholder"></li>
<?php
		else:
			$this->load->view('entry', array(
				'field_id'   => $field_id,
				'field_name' => $field_name,
				'entry'      => $entry,
				'selected'   => FALSE
			));
		endif;
	endforeach;
?>