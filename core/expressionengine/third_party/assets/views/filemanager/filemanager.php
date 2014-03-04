<div class="assets-fm">
	<div class="assets-fm-sidebar">
		<div class="assets-fm-upload"></div>

		<div class="assets-fm-folders">
			<ul>
				<?php
					if (empty($filedirs))
					{
						$filedirs = array();
					}

					$tree = $lib->get_folder_tree($filedirs);

					// Sort the top-level folders by name
					$folder_names = array();

					foreach ($tree as $folder)
					{
						$folder_names[] = $folder->name;
					}

					array_multisort($folder_names, $tree);

					foreach ($tree as $folder)
					{
						$vars['folder'] = $folder;
						$vars['depth']  = 1;
						$this->load->view('filemanager/folder', $vars);
					}
				?>
				<li class="assets-fm-folder" data-source_id="recent"><a data-id="recent" style="padding-left: 20px;" data-no_uploads="1" data-no_menu="1"><span class="assets-fm-label"><?php echo lang('recent_uploads') ?></span></a></li>

			</ul>
		</div>
	</div>

	<div class="assets-fm-main">
		<div class="assets-fm-toolbar">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td class="assets-fm-search"><input type="text" placeholder="<?php echo lang('search_assets') ?>" /></td>
					<td>
						<ul class="assets-fm-view">
							<li class="assets-btn assets-fm-thumbs" title="<?php echo lang('view_files_as_thumbnails') ?>" data-view="thumbs"></li>
							<li class="assets-btn assets-fm-bigthumbs" title="<?php echo lang('view_files_as_big_thumbnails') ?>" data-view="bigthumbs"></li>
							<li class="assets-btn assets-fm-list" title="<?php echo lang('view_files_in_list') ?>" data-view="list"></li>
						</ul>
					</td>
					<td><div class="assets-btn assets-fm-refresh" title="<?php echo lang('refresh') ?>"></div></td>
				</tr>
			</table>
			<div class="assets-fm-searchoptions">
				<label><input type="checkbox" class="assets-fm-searchmode"> <?php echo lang('search_nested_folders') ?></label>
			</div>
		</div>

		<div class="assets-fm-files"></div>
	</div>

	<div class="assets-fm-uploadprogress">
		<div class="assets-fm-progressbar">
			<div class="assets-fm-pb-bar"></div>
		</div>
	</div>
</div>
<?php
	if (isset($footer) && $footer)
	{
		?>
		<div class="assets-buttons">
			<span class="assets-btn assets-cancel"><?php echo lang('cancel') ?></span> <span class="assets-btn assets-disabled assets-add"><?php echo lang('add_files') ?></span>
		</div>
	<?php
	}
