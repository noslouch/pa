<div class="dmenu">
    <ul>
        <li class="<?=(($section == 'tags')) ? ' current': ''?>"><a class="tags" href="<?=$base_url?>"><?=lang('tagger:tags')?></a></li>
        <li class="<?=(($section == 'groups')) ? ' current': ''?>"><a class="groups" href="<?=$base_url?>&method=groups"><?=lang('tagger:groups')?></a></li>
        <li class="<?=(($section == 'import')) ? ' current': ''?>"><a class="import" href="<?=$base_url?>&method=import"><?=lang('tagger:import')?></a></li>
        <li class="<?=(($section == 'settings')) ? ' current': ''?>"><a class="settings" href="<?=$base_url?>&method=settings"><?=lang('tagger:settings')?></a></li>
        <li><a rel="external" href="<?=$this->cp->masked_url('http://www.devdemon.com/tagger/docs/')?>"><?=lang('tagger:docs')?></a></li>
    </ul>
</div>
