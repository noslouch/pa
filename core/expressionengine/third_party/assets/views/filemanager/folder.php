<li class="assets-fm-folder"<?= $depth == 1 ? 'data-source_id="' . $folder->id . '"' : ''?>>
	<a data-id="<?php echo $folder->id ?>" style="padding-left: <?php echo (20 + (18 * $depth)) ?>px"><span class="assets-fm-label"><?php echo $folder->name ?></a></a>
	<?php if ( ! empty($folder->children)): ?>
		<ul>
			<?php
				foreach ($folder->children as $child_folder)
				{
					$vars['folder'] = $child_folder;
					$vars['depth']  = $depth + 1;
					$this->load->view('filemanager/folder', $vars);
				}
			?>
		</ul>
	<?php endif ?>
</li>
