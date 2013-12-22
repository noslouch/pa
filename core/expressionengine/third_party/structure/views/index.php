<?php
$ul_open = FALSE;
$last_page_depth = 0;
$level_lock_reorder = is_numeric(substr($permissions['reorder'], -1)) ? (int)substr($permissions['reorder'], -1) : $permissions['reorder'];
$level_lock_delete = is_numeric(substr($permissions['delete'], -1)) ? (int)substr($permissions['delete'], -1) : $permissions['delete'];
?>

<div class="padder">

<div id="structure-ui">

	<div id="tree-header">
		<?php if ($cp_asset_data OR count($tabs) > 1): ?>

			<ul id="tree-switcher">
				<?php foreach($tabs as $id => $name) : ?>
					<li<?php if(array_search($id, array_keys($tabs)) == 0) : ?> class="here"<?php endif; ?>><a href="#" rel="<?php echo $id; ?>"><?php echo $name; ?></a></li>
				<?php endforeach; ?>
				<?php if ($cp_asset_data): ?>
					<li><a href="#" rel="assets"><?=lang('assets')?></a></li>
				<?php endif; ?>
			</ul>

		<?php endif; ?>


		<ul id="tree-controls">

			<?php if (isset($permissions['view_global_add_page']) && $permissions['view_global_add_page'] == TRUE):?>
				<li <?php if (count($page_choices) > 1 && $page_count > 0):?>class="tree-add"<?php endif; ?>><a href="<?=$add_page_url?>" class="pop" title="pop"><?=lang('ui_add_page')?></a></li>
			<?php endif; ?>
			<li class="tree-expand"><a href="#"><?=lang('ui_expand_all')?></a></li>
			<li class="tree-collapse"><a href="#"><?=lang('ui_collapse_all')?></a></li>
		</ul>

	</div> <!-- close #tree-header -->

	<?php foreach($tabs as $id => $name) : ?>
	<?php $i = 1; #reset for each item in the switcher ?>
	<ul id="<?php echo $id; ?>" class="page-ui page-list<?php if(array_search($id, array_keys($tabs)) > 0) : ?> hide-alt<?php endif; ?>">
	<?php foreach ($data[$id] as $eid => $page):?>

		<?php
		$edit_url = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$page['channel_id'].AMP.'entry_id='.$page['entry_id'].AMP.'parent_id='.$page['parent_id'];
		$add_url  = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$page['channel_id'].AMP.'parent_id='.$eid.AMP.'template_id='.$site_pages['templates'][$eid];
		$classes = array('page-item', 'status-'.str_replace(" ", "-", strtolower($page['status'])), 'channel-'.str_replace(" ", "-", strtolower($page['channel_id'])));
		
		if ($page['entry_id'] == $homepage) {
			$classes[] = 'home';
			$classes[] = 'ui-nestedSortable-no-nesting';
		} 

		$li_open = '<li id="page-'. $page['entry_id'] . '" class="'.implode(' ', $classes).'">';

		// Start a sub nav
		if ($page['depth'] > $last_page_depth)
		{
			$markup = "<ul class='page-list";

			if ($member_settings['nav_state'] && in_array($page['parent_id'], $member_settings['nav_state']))
				$markup .= " state-collapsed";

			$markup .= "'>".$li_open."\n";

			$ul_open = TRUE;
		}
		elseif ($i == 1)
		{
			$markup = $li_open."\n";
		}
		elseif ($page['depth'] < $last_page_depth)
		{
			$back_to = $last_page_depth - $page['depth'];
			$markup  = "\n</li>";
			$markup .= str_repeat("\n</ul>\n</li>\n", $back_to);
			$markup .= $li_open."\n";
			$ul_open = false;
		}
		else
		{
			$markup = "\n</li>\n".$li_open."\n";
		}

		?>

		<?=$markup;?>
		<div class="item-wrapper">
			<div class="item-inner">
				<span class="page-expand-collapse ec-none"><a href="#">+/-</a></span> <!-- new toggle -->
				<span class="page-handle<?php if($permissions['reorder'] == 'none') echo " page-handle-disabled";?><?php if (isset($permissions['reorder']) && ($permissions['reorder'] == 'all' || (is_numeric($level_lock_reorder)) && ($page['depth'] + 1) > $level_lock_reorder)) echo " drag-handle"?>">
					<a href="#">Move</a>
				</span>

				<span class="page-title">
					<?php if (array_key_exists($page['channel_id'], $assigned_channels)): ?>
						<a href="<?=$edit_url;?>"><?=$page['title'];?></a>
					<?php else: ?>
						<span class="page-title-disabled"><?=$page['title'];?></span>
					<?php endif; ?>
					<?php if ($page['hidden'] == 'y'):?><span>(hidden)</span><?php endif; ?>
				</span>

				<!-- If Listing Exists -->
				<?php if ($page['listing_cid'] && array_key_exists($page['listing_cid'], $assigned_channels)): ?>
					<span class="page-listing"><a href="<?=BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$page['listing_cid']; ?>"><?=lang('add')?></a> or <a href="<?= BASE . "&amp;C=content_edit&amp;channel_id={$page['listing_cid']}" ?>"><?=lang('edit')?></a></span>
				<?php endif; ?>

				<div class="page-controls">
					<?php if ($permissions['view_add_page'] && $settings['show_picker'] == 'y'): ?>
						<?php if (count($page_choices) > 1 && $page_count > 0):?>
							<span class="control-add"><a href="#" class="pop" data-parent_id="<?=$eid?>"><?=lang('ui_add_child_page')?></a></span>
						<?php else: ?>
							<span><a href="<?=$add_page_url?>&parent_id=<?=$eid?>"><?=lang('ui_add_child_page')?></a></span>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ($permissions['view_add_page'] && $settings['show_picker'] == 'n'): ?>
						<span class="control-add"><a href="#" data-parent_id="<?=$eid?>"><?=lang('ui_add_child_page')?></a></span>
					<?php endif; ?>

					<?php if(isset($permissions['view_view_page']) && $permissions['view_view_page'] == 'y'):?>
						<span class="control-view"><a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=link'.AMP.'entry_id='.$page['entry_id'];?>"><?=lang('view_page')?></a></span>
					<?php endif;?>

					<?php if (isset($permissions['delete']) && ($permissions['delete'] == 'all' || (is_numeric($level_lock_delete)) && ($page['depth'] + 1) > $level_lock_delete)):?>
						<span class="control-del"><a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=delete'.AMP.'toggle='.$page['entry_id'];?>"><?=lang('delete')?></a></span>
					<?php endif;?>

					<input type="hidden" class="structurePid" value="<?=$page['parent_id']; ?>" />
					<input type="hidden" class="structureEid" value="<?=$eid; ?>" />
				</div> <!-- close .page-controls -->
			</div>
		</div> <!-- close .item-wrapper -->

		<?php
		$last_page_depth = $page['depth']; $i++;
		endforeach;

		// Close out the end
		$html  = "\n</li>";
		$html .= str_repeat("</ul>\n</li>\n", $last_page_depth);
		$ul_open = FALSE;

		?>

		<?=$html?>

		</ul>
	<?php endforeach; ?>

	<?php if ($cp_asset_data): ?>

	<ul id="assets" class="hide-alt">

	<?php foreach ($cp_asset_data as $title => $row): ?>
		<?php if ($row['split_assets'] == 'y'): ?>
			<li><span class="listing-title"><a href="<?=BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$row['channel_id'].AMP.'entry_id='.$row['entry_id']?>"><?=$row['title']?></a></span></li>
		<?php else: ?>
			<li>
				<span class="listing-title"><?=$row['title'];?></span>
				<span class="page-listing"><a href="<?=BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$row['channel_id']?>"><?=lang('add')?></a> or <a href="<?=BASE.AMP.'C=content_edit'.AMP.'channel_id='.$row['channel_id']?>"><?=lang('edit')?></a>
				</span>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>

	</ul> <!-- close #assets -->

	<?php endif; ?>

	<div class="clear"></div>
</div>
</div>
