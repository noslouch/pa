<div class="ChannelImagesField cfix">

<div class="view_toggle">
    <strong><?=lang('ci:view_mode')?>:</strong>&nbsp;&nbsp;
    <input name="channel_images[view_mode]" <?php if (isset($override['view_mode'])):?>disabled="disabled"<?php endif;?> type="radio" value="tiles" <?php if ($view_mode == 'tiles') echo 'checked'?>> <?=lang('ci:tiles_view')?>&nbsp;&nbsp;&nbsp;
    <input name="channel_images[view_mode]" <?php if (isset($override['view_mode'])):?>disabled="disabled"<?php endif;?> type="radio" value="table" <?php if ($view_mode == 'table') echo 'checked'?>> <?=lang('ci:table_view')?>
</div>

<ul class="ChannelImagesTabs">
	<li><a href="#CIActions"><?=lang('ci:upload_actions')?></a></li>
	<li><a href="#CILocSettings"><?=lang('ci:loc_settings')?></a></li>
	<li><a href="#CIFieldUI"><?=lang('ci:fieldtype_settings')?></a></li>
    <li><a href="#CIColumns"><?=lang('ci:field_columns')?></a></li>
</ul>

<div class="ChannelImagesTabsHolder">
	<div class="CIActions cfix" id="CIActions"><?=$this->load->view('fts/actions', array(), TRUE);?></div>
	<div class="CILocSettings" id="CILocSettings"><?=$this->load->view('fts/locations', array(), TRUE);?></div>
	<div class="CIFieldUI" id="CIFieldUI"><?=$this->load->view('fts/field_settings', array(), TRUE);?></div>
    <div class="CIColumns" id="CIColumns"><?=$this->load->view('fts/field_columns', array(), TRUE);?></div>

    <small class="imagick">
        Imagick
        <?php if ($imagick):?>Installed<?php else:?>
        Not Installed
        <?php endif;?>
    </small>
</div>



</div>
