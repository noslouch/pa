<!--[if IE]> <div id="cimcp" class="ci-ie"> <![endif]-->
<!--[if !IE]><!--> <div id="cimcp"><!--<![endif]-->

<div id="umenu">
    <ul>
        <li class="<?=(($section == 'actions')) ? ' current': ''?>"><a class="actions" href="<?=$base_url?>&method=batch_actions"><?=lang('ci:batch_actions')?></a></li>
        <li class="<?=(($section == 'legacy')) ? ' current': ''?>"><a class="legacy" href="<?=$base_url?>&method=legacy_settings"><?=lang('ci:legacy_settings')?></a></li>
        <li class="<?=(($section == 'import')) ? ' current': ''?>"><a class="import" href="<?=$base_url?>&method=import"><?=lang('ci:import')?> (Matrix / File)</a></li>
        <li class="<?=(($section == 'docs')) ? ' current': ''?>"><a class="docs" href="http://www.devdemon.com/docs/channel_images/"><?=lang('ci:docs')?></a></li>
    </ul>
</div>

