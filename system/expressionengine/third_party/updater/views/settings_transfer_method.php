<div class="utable">
<h2><?=lang('u:'.$settings['file_transfer_method'])?></h2>
<table class="file_transfer_methods">
	<thead>
		<tr class="heading">
			<th></th>
			<?php foreach($dirs as $dir => $status):?>
			<th><?=lang('u:dir_'.$dir)?></th>
			<?php endforeach;?>
		</tr>
	</thead>
	<tbody>

		<?php if ($settings['file_transfer_method'] != 'local'): ?>
		<tr>
			<td><?=lang('u:connect')?></td>
			<?php foreach($dirs as $dir => $status):?>
			<td>
				<?php if ($connect == FALSE):?><span class="label label-important"><?=lang('u:failed')?></span>
				<?php else:?><span class="label label-success"><?=lang('u:passed')?></span><?php endif;?>
			</td>
			<?php endforeach;?>
		</tr>
		<?php endif;?>

		<?php foreach($actions as $action):?>
		<tr>
			<td><?=lang('u:'.$action)?></td>
			<?php foreach($dirs as $dir => $status):?>
			<td>
				<?php if ($status[$action] == FALSE):?><span class="label label-important"><?=lang('u:failed')?></span>
				<?php else:?><span class="label label-success"><?=lang('u:passed')?></span><?php endif;?>
			</td>
			<?php endforeach;?>
		</tr>
		<?php endforeach;?>

	</tbody>
</table>
</div>
