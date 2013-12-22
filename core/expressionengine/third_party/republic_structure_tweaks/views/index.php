<?php if ($structure_less_than_3_1) : ?>
	<p><?php echo lang('republic_structure_tweaks_newer_structure_required');?></p>
<?php elseif( empty($channels)):?>
	<p><?php echo lang('republic_structure_tweaks_no_structure_channel');?></p>
<?php else:?>
	<?php if ( ! empty($channels)) : ?>
	<?php echo form_open($action_url); ?>
	<div style="margin-bottom:10px;">
	<p><?php echo lang('republic_structure_tweaks_description'); ?></p>

	<div style="margin-top: 10px;">
	<h3><?php echo lang('republic_structure_tweaks_channels_heading');?></h3>
	</div>

	<table class="mainTable" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th colspan="2">Tweaks</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="width: 50%">
					Show channel title in Structure
				</td>
				<td>
					<?php $yes_selected = (! empty($settings['show_channel_title'][$site_id]) && $settings['show_channel_title'][$site_id] === 'y') ? "checked=checked" : "" ?>
					<?php $no_selected	= (empty($settings['show_channel_title'][$site_id]) || $settings['show_channel_title'][$site_id] !== 'y') ? "checked=checked" : "" ?>
					<input name="show_channel_title" id="y_show_channel_title" type="radio" value="y" <?php echo $yes_selected; ?>>
					<label for="y_show_channel_title"><?php echo lang('yes');?></label>
					<input name="show_channel_title" id="n_show_channel_title" type="radio" value="n" <?php echo $no_selected; ?> style="margin-left: 12px">
					<label for="n_show_channel_title"><?php echo lang('no');?></label>
				</td>
			</tr>
			<tr>
				<td style="width: 50%">
					Mark pages with status closed
				</td>
				<td>
					<?php $yes_selected = (! empty($settings['show_status_closed'][$site_id]) && $settings['show_status_closed'][$site_id] === 'y') ? "checked=checked" : "" ?>
					<?php $no_selected	= (empty($settings['show_status_closed'][$site_id]) || $settings['show_status_closed'][$site_id] !== 'y') ? "checked=checked" : "" ?>
					<input name="show_status_closed" id="y_show_status_closed" type="radio" value="y" <?php echo $yes_selected; ?>>
					<label for="y_show_status_closed"><?php echo lang('yes');?></label>
					<input name="show_status_closed" id="n_show_status_closed" type="radio" value="n" <?php echo $no_selected; ?> style="margin-left: 12px">
					<label for="n_show_status_closed"><?php echo lang('no');?></label>
				</td>
			</tr>
		</tbody>
	</table>

	<?php foreach ($channels as $channel) : ?>
	<table class="mainTable" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th colspan="2"><?php echo $channel['channel_title']; ?></th>
			</tr>
		</thead>
		<tbody>
			<tr id="sub_channel_<?php echo $channel['channel_id'];?>" class="odd channel_child_hide" <?php if(! empty($settings['channel_hide_from_child_picker'][$site_id][$channel['channel_id']])):?>style="display: none"<?php endif;?>>
				<td width="50%"><?php echo lang('republic_structure_tweaks_channel_child_hide')?> <strong><?php echo $channel['channel_title']?></strong></td>
				<td>
					<?php foreach ($structure_channels AS $sub_channel) : ?>
						<span class="sub_channel_<?php echo $sub_channel['channel_id'];?>" <?php if(! empty($settings['channel_hide_from_child_picker'][$site_id][$sub_channel['channel_id']])):?>style="display: none"<?php endif;?>>
						<?php echo form_label(
							form_checkbox('channel_child_hide['.$site_id.']['.$channel['channel_id'].'][]', $sub_channel['channel_id'],
														(! empty($settings['channel_child_hide'][$site_id][$channel['channel_id']]) && in_array($sub_channel['channel_id'],$settings['channel_child_hide'][$site_id][$channel['channel_id']])) ).'&nbsp;'.$sub_channel['channel_title'])?><br />
						</span>
					<?php endforeach; ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php endforeach; ?>

	<div style="margin-top: 30px;">
	<h3><?php echo lang('republic_structure_tweaks_entries_heading');?></h3>
	</div>

	<table id="entries_table" class="mainTable" border="0" cellspacing="0" cellpadding="0">
		<thead>
			<tr>
				<th><?php echo lang('republic_structure_tweaks_pages');?></th>
				<th><?php echo lang('republic_structure_tweaks_hide_channels_from_entry');?></th>
				<th><?php echo lang('republic_structure_tweaks_apply_to_children');?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 0;?>
			<?php if(isset($settings['entry_data'])) : ?>
				<?php foreach($settings['entry_data'][$site_id] AS $entry_id => $entry) : ?>
				<tr class="<?php if($i++ % 2 === 0):?>even<?php else:?>odd<?php endif;?>">
					<td style="width: 50%">
						<?php echo form_dropdown('entry_settings['.$site_id.'][entry][]', $entries, $entry_id)?>
					</td>
					<td>
						<?php foreach ($structure_channels AS $sub_channel) : ?>
							<span class="sub_channel_<?php echo $sub_channel['channel_id'];?>"  <?php if(! empty($settings['channel_hide_from_child_picker'][$site_id][$sub_channel['channel_id']])):?>style="display: none"<?php endif;?>>
							<?php echo form_label(form_checkbox('entry_settings['.$site_id.'][channel_child_hide]['.$entry_id.'][]', $sub_channel['channel_id'],
														(! empty($settings['entry_data'][$site_id][$entry_id]['channel_child_hide']) && in_array($sub_channel['channel_id'],$settings['entry_data'][$site_id][$entry_id]['channel_child_hide']))).'&nbsp;'.$sub_channel['channel_title'])?><br />
							</span>
						<?php endforeach; ?>
					</td>
					<td>
						<?php echo form_label(form_checkbox('entry_settings['.$site_id.'][append_rule_on_children]['.$entry_id.']', '1', ( ! empty($settings['entry_data'][$site_id][$entry_id]['append_rule_on_children']) )).'&nbsp;'.lang('yes'))?>
					</td>
					<td><a href="#" class="delete_row"><?php echo lang('republic_structure_tweaks_delete_row');?></a></td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>

		</tbody>
	</table>
	<a href="#" id="add_new_row" class="add"><?php echo lang('republic_structure_tweaks_add_row');?></a>


	<div class="tableFooter">
		<div class="tableSubmit">
			<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))?>
		</div>
	</div>

	<?php echo form_close(); ?>
	<?php else: ?>
	<p><?php echo lang('republic_structure_tweaks_no_structure_channels');?></p>
	<?php endif; ?>

	<div style="display:none">
		<table>
			<tr class="new-entry" id="new_entry_row">
				<td style="width: 50%">
					<?php echo form_dropdown('entry_settings['.$site_id.'][entry][]', $entries); ?>
				</td>
				<td>
					<?php foreach ($channels AS $sub_channel) : ?>
						<span class="sub_channel_<?php echo $sub_channel['channel_id'];?>"  <?php if ( !  empty($settings['channel_hide_from_child_picker'][$site_id][$sub_channel['channel_id']])) : ?>style="display: none"<?php endif;?>>
						<?php echo form_label(form_checkbox('entry_settings['.$site_id.'][channel_child_hide][xxx][]', $sub_channel['channel_id']).'&nbsp;'.$sub_channel['channel_title']);?>
						<br /></span>
					<?php endforeach; ?>
				</td>
				<td>
					<?php echo form_label(form_checkbox('entry_settings['.$site_id.'][append_rule_on_children][xxx]', '1').'&nbsp;'.lang('yes')); ?>
				</td>
				<td><a href="#" class="delete_row">Delete</a></td>
			</tr>
		</table>
	</div>


	<script>
		function republic_structure_tweaks_toggle_fields(channel_id) {
			$('#sub_channel_'+channel_id).toggle();
			if($('#sub_channel_'+channel_id).is(':hidden')) {
				$('.sub_channel_'+channel_id + ' input').each(function(){
					$(this).closest('span').hide();
					$(this).attr('checked', false);
				});
			} else {
				$('.sub_channel_'+channel_id + ' input').each(function(){
					$(this).closest('span').show();
					$(this).attr('checked', false);
				});
			}
		}

		$('form').submit(function () {
			$('tr.new-entry select').each(function () {
				$entry_id = $(this).val();
				$(this).closest('tr').find('input').each(function(){
					$(this).attr('name', $(this).attr('name').replace('xxx', $entry_id));
				});
			});
		});

		$("#add_new_row").click(function () {
			$cloned_entry_row = $("#new_entry_row").clone();
			$cloned_entry_row.removeAttr('id');

			var nrOfRows = $("#entries_table tbody tr").size();
			if (nrOfRows % 2 === 0) {
				$cloned_entry_row.addClass('even');
			} else {
				$cloned_entry_row.addClass('odd');
			}
			$("#entries_table tbody").append($cloned_entry_row);

			return false;
		});

		$(".delete_row").live('click', function () {
			$(this).closest('tr').remove();
			var nrOfRows = 0;
			$("#entries_table tbody tr").each(function(){
				$(this).removeClass('odd');
				$(this).removeClass('even');

				if (nrOfRows++ % 2 === 0) {
					$(this).addClass('even');
				} else {
					$(this).addClass('odd');
				}
			});
			return false;
		});
	</script>

<?php endif;?>
