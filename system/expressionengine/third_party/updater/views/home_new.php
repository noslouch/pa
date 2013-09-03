<?=$this->view('_topmenu'); ?>
<!--[if IE]> <div id="updater" class="updater-ie"> <![endif]-->
<!--[if !IE]><!--> <div id="updater"><!--<![endif]-->


    <div class="dropregion">

    </div>

    <div class="addonlist">
        <div class="top">
            <div class="sectionfilter">
                <button class="active filter" data-filter="filter-section_all"><span><?=lang('u:show_all')?></span></button>
                <button class="filter" data-filter="filter-section_module"><span><?=lang('u:modules')?></span></button>
                <button class="filter" data-filter="filter-section_fieldtype"><span><?=lang('u:fieldtypes')?></span></button>
                <button class="filter" data-filter="filter-section_extension"><span><?=lang('u:extensions')?></span></button>
                <button class="filter" data-filter="filter-section_plugin"><span><?=lang('u:plugins')?></span></button>
                <button class="filter" data-filter="filter-section_accessory"><span><?=lang('u:accessories')?></span></button>
                <button class="filter" data-filter="filter-section_rte_tool"><span><?=lang('u:rte_tools')?></span></button>
            </div>
            <div class="sortby">
                <strong><?=lang('u:sort_by')?></strong>
                <button class="sort" data-sort="install_status"><span><?=lang('u:install_status')?></span></button>
                <button class="active sort" data-sort="label"><span><?=lang('u:addon_name')?></span></button>
                <button class="sort" data-sort="updated"><span><?=lang('u:last_updated')?></span></button>
            </div>
        </div>

        <div class="addons">
            <?php foreach($addons as $section => $addonlist):?>
                <?php foreach($addonlist as $package => $addon):?>
                <div class="addon <?=implode(' ', $addon['classes'])?> ">
                    <div class="inner">
                        <span class="icon"><img src="<?=$addon['icon']?>" width="32px" height="32px"></span>
                        <span class="version"><?=$addon['version']?></span>
                        <span class="label"><?=$addon['label']?></span>
                        <?php if ($addon['installed'] == true):?>
                        <span class="status installed"><?=lang('u:installed')?></span>
                        <?php else:?>
                        <span class="status notinstalled"><?=lang('u:not_installed')?></span>
                        <?php endif;?>
                        <span class="addonoptions" data-toggle="dropdown">&lt; <?=lang('u:actions')?> &gt;</span>

                        <div class="actions">

                            <?php if ($addon['installed'] == false):?>
                            <span class="install"><?=lang('u:install_addon')?></span>
                            <?php else:?>
                            <span class="uninstall"><?=lang('u:uninstall_addon')?></span>
                            <?php endif;?>

                            <span class="deladdon"><?=lang('u:delete_addon')?></span>

                            <div class="settings">
                                <strong><?=lang('u:settings')?>:</strong>
                                <span class="addon_st">MOD</span>
                                <span class="addon_st">FT</span>
                                <span class="addon_st">EXT</span>
                                <span class="addon_st">ACC</span>
                            </div>

                        </div>
                    </div>
                </div>
                <?php endforeach;?>
            <?php endforeach;?>
            <br clear="all">
        </div>

        <div class="bottom">
            <div class="misc">
                <button class="active filter" data-filter="filter-thirdparty"><span><?=lang('u:hide_native')?></span></button>
                <button class="filter" data-filter="filter-installed"><span><?=lang('u:hide_notinstalled')?></span></button>
            </div>
        </div>
    </div>





</div> <!-- #updater -->
