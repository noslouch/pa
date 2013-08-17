<?php if (isset($settings['channels']) == TRUE):?>

	<?php foreach ($settings['channels'] as $channel_id => $data):?>
	<?php if (isset($channels[$channel_id]) == FALSE) continue;?>

	<h3><?=$channels[$channel_id]?></h3>
	<p><strong><?=lang('ci:location_path')?>: </strong> <input type="text" class="text" value="<?=$data['location_path']?>" style="width:50%"></p>
	<p><strong><?=lang('ci:location_url')?>: </strong> <input type="text" class="text" value="<?=$data['location_url']?>" style="width:50%"></p>
	<p><strong><?=lang('ci:categories')?>: </strong> <input type="text" class="text" value="<?=implode(',', $data['categories'])?>" style="width:50%"></p>
	<p>
		<strong><?=lang('ci:image_sizes')?>: </strong>
		<table class='mainTable' style="width:50%">
			<thead>
				<tr>
					<th><?=lang('ci:name')?></th>
					<th><?=lang('ci:width_px')?></th>
					<th><?=lang('ci:height_px')?></th>
					<th><?=lang('ci:quality')?></th>
					<th><?=lang('ci:greyscale')?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($data['sizes'] as $name => $size):?>
					<tr>
						<td> <input type="text" value="<?=$name?>"> </td>
						<td> <input type="text" value="<?=$size['w']?>"> </td>
						<td> <input type="text" value="<?=$size['h']?>"> </td>
						<td> <input type="text" value="<?=$size['q']?>"> </td>
						<td> <?php if (isset($size['g']) == TRUE AND $size['g'] == 'y') echo lang('ci:yes'); else echo lang('ci:no'); ?> </td>
					</tr>
				<?php endforeach;?>
			</tbody>
		</table>
	</p>

	<br /><br />
	<?php endforeach;?>


<?php else:?>

<h2><?=lang('ci:no_legacy')?></h2>

<?php endif;?>
