<?php echo $this->view('mcp/_menu'); ?>
<div class="ci-body">

<?php foreach($fields as $field):?>

<?=form_open($base_url_short.AMP.'method=import_images')?>

<?=form_hidden('field[type]', $field['type']);?>
<?=form_hidden('field[field_id]', $field['field_id']);?>
<?=form_hidden('field[channel_id]', $field['channel_id']);?>

<table class="mainTable ImportMatrixImages">
	<thead>
		<tr>
			<th colspan="3"><?=$field['field_label']?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong><?=lang('ci:transfer_field')?></strong></td>
			<td>
				<select name="field[ci_field]">
				<?php foreach($field['ci_fields'] as $ci):?>
					<option value="<?=$ci->field_id?>"><?=$ci->field_label?></option>
				<?php endforeach;?>
				</select>
			</td>
		</tr>
<?php if ($field['type'] == 'matrix'):?>
		<tr>
			<td><strong><?=lang('ci:column_mapping')?></strong></td>
			<td>
				<table class="mainTable">
				<thead>
					<tr>
						<th colspan="3"><?=lang('ci:column_mapping')?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($field['cols'] as $col):?>
					<tr>
						<td><?=$col->col_label?></td>
						<td>
							<select name="field[fieldmap][<?=$col->col_id?>]">
								<option value=""><?=lang('ci:dont_transfer')?></option>
								<option value="image"><?=lang('ci:image')?></option>
								<option value="title"><?=lang('ci:title')?></option>
								<option value="description"><?=lang('ci:desc')?></option>
								<option value="category"><?=lang('ci:category')?></option>
								<option value="cifield_1"><?=lang('ci:cifield_1')?></option>
								<option value="cifield_2"><?=lang('ci:cifield_2')?></option>
								<option value="cifield_3"><?=lang('ci:cifield_3')?></option>
								<option value="cifield_4"><?=lang('ci:cifield_4')?></option>
								<option value="cifield_5"><?=lang('ci:cifield_5')?></option>
							</select>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
				</table>
			</td>
		</tr>
<?php endif;?>
		<tr>
			<td style="width:300px"><?=lang('ci:import_entries')?></td>
			<td class="CI_IMAGES">
				<?php foreach($field['entries'] as $row):?>
					<div class="Image Queued label" rel="<?=$row->entry_id?>" style="float:left;margin:0 5px 5px 0;"><?=$row->entry_id?></div>
				<?php endforeach;?>
				<br clear="all">
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td><button class="submit">Import</button></td>
			<td class="errormsg"></td>
		</tr>
	</tfoot>
</table>
<?=form_close()?>

<?php endforeach;?>

</div> <!-- </ci-body> -->
<?php echo $this->view('mcp/_footer'); ?>
