<table class="mainTable matrixTable" width="" cellspacing="0" cellpadding="0" border="0" style="">

	<tr>
		<?php foreach($headers['field_id_'.$field_id] as $header) :?>
			<th><?=$header['label']?></th>
		<?php endforeach ?>
	</tr>

	<?php if(isset($table_data['entry_id_'.$entry_id]['field_id_'.$field_id])) : ?>
		<?php foreach($table_data['entry_id_'.$entry_id]['field_id_'.$field_id] as $row) :?>
			<tr>
				<?php foreach($row as $col) : ?>
				<td><?=$col?></td>
				<?php endforeach ?>
			</tr>
		<?php endforeach ?>
	<?php endif ?>
</table>