<?php
	// default values
	if (! isset($cols))    $cols = array('date', 'size');
	if (! isset($orderby)) $orderby = FALSE;
	if (! isset($sort))    $sort = 'asc';

	if (!in_array('name', $cols))
	{
		array_unshift($cols, 'name');
	}
?>

<div class="assets-listview">
	<table cellspacing="0" cellpadding="0" border="0">
		<thead>
			<tr>
				<?php foreach ($cols as $col): ?>
					<th class="assets-lv-<?php echo $col ?><?php if ($orderby == $col): ?> assets-lv-sorting assets-lv-<?php echo $sort ?><?php endif ?>" data-orderby="<?php echo $col ?>"><?php echo lang($col) ?></th>
				<?php endforeach ?>
			</tr>
		</thead>
		<tbody>
			<?php echo $this->load->view('listview/files') ?>
		</tbody>
	</table>
</div>
