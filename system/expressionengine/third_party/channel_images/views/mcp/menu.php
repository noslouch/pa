<div class="dmenu">
	<ul>
		<li class="<?=(($section == 'regen')) ? ' current': ''?>"><a class="regen" href="<?=$base_url?>&method=regenerate_sizes"><?=lang('ci:regenerate_sizes')?></a></li>
		<li class="<?=(($section == 'legacy')) ? ' current': ''?>"><a class="legacy" href="<?=$base_url?>&method=legacy_settings"><?=lang('ci:legacy_settings')?></a></li>
		<li class="<?=(($section == 'import')) ? ' current': ''?>"><a class="import" href="<?=$base_url?>&method=import"><?=lang('ci:import')?> (Matrix)</a></li>
		<li class="<?=(($section == 'docs')) ? ' current': ''?>"><a class="docs" href="http://www.devdemon.com/docs/channel_images/"><?=lang('ci:docs')?></a></li>
	</ul>
</div>
