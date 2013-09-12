<input type="hidden" name="<?=$field_name?>[selections][]" value=""/>

<div id="<?=$field_id?>" class="playa playa-ss" style="margin: <?=$margin?>" tabindex="0">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="playa-ss-input">
<?php
	if (! $selected_entry):
?>
				<li class="playa-entry playa-ss-noval"><a><?=lang('select_an_entry')?></a></li>
<?php
	else:
		$this->load->view('entry', array(
			'field_id'   => $field_id,
			'field_name' => $field_name,
			'entry'      => $selected_entry,
			'selected'   => TRUE
		));
	endif;
?>
			</td>
			<td class="playa-ss-button"><img src="<?=$theme_url?>images/select_btn_arrow.png" alt="" /></td>
		</tr>
	</table>

	<div class="playa-entries playa-ss-entries">
		<div class="playa-scrollpane">
			<ul>
				<li class="playa-entry playa-ss-noval"><a><?=lang('select_an_entry')?></a></li>
<?php
	foreach ($entries as $entry):
		$this->load->view('entry', array(
			'field_id'   => $field_id,
			'field_name' => $field_name,
			'entry'      => $entry,
			'selected'   => FALSE
		));
	endforeach;
?>
			</ul>
		</div>
	</div>
</div>