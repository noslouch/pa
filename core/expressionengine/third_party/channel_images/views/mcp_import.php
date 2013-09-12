<?php foreach($matrix as $matrix_field):?>

<?=form_open($base_url_short.AMP.'method=import_images')?>

<?=form_hidden('matrix[field_id]', $matrix_field['field_id']);?>
<?=form_hidden('matrix[channel_id]', $matrix_field['channel_id']);?>

<table class="mainTable ImportMatrixImages">
	<thead>
		<tr>
			<th colspan="3"><?=$matrix_field['field_label']?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong><?=lang('ci:transfer_field')?></strong></td>
			<td>
				<select name="matrix[ci_field]">
				<?php foreach($matrix_field['ci_fields'] as $ci):?>
					<option value="<?=$ci->field_id?>"><?=$ci->field_label?></option>
				<?php endforeach;?>
				</select>
			</td>
		</tr>
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
				<?php foreach($matrix_field['cols'] as $col):?>
					<tr>
						<td><?=$col->col_label?></td>
						<td>
							<select name="matrix[fieldmap][<?=$col->col_id?>]">
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
		<tr>
			<td><?=lang('ci:import_entries')?></td>
			<td class="CI_IMAGES">
				<?php foreach($matrix_field['entries'] as $row):?>
					<div class="Image Queued" rel="<?=$row->entry_id?>"><?=$row->entry_id?></div>
				<?php endforeach;?>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3"><input class="submit" type="submit" value="Import"/></td>
		</tr>
	</tfoot>
</table>
<?=form_close()?>

<?php endforeach;?>