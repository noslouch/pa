<table class="mainTable matrixTable" width="" cellspacing="0" cellpadding="0" border="0" style="">

	<tr>
		<?php foreach($headers as $row => $title) :?>
		<th>
			<?=$title?>
		</th>
		<?php endforeach;?>
	</tr>
	<?php foreach($rows as $row => $array) :?>
		<tr>
			
			<?php foreach($array as $key => $data) :?>
				<td><?=$data?></td>
			<?php endforeach;?>
			
		</tr>
	<?php endforeach; ?>

</table>