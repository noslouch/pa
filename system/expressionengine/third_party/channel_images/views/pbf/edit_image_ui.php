<div class="ci_eiw">

    <div class="eiw_left">
        <ul class="sizes">
            <li class="label"><label><?=lang('ci:sizes')?></label></li>
            <li class="current"><a href="#" data-name="ORIGINAL">ORIGINAL</a></li></li>
            <?php foreach($sizes as $size_name => $dimensions):?>
            <li><a href="#" data-name="<?=$size_name?>" data-width="<?=$dimensions['width']?>" data-height="<?=$dimensions['height']?>"><?=$size_name?></a></li></li>
            <?php endforeach;?>
        </ul>

        <ul class="actions">
            <li class="label"><label><?=lang('ci:actions')?></label></li>
            <li><a href="#" data-action="crop"><?=lang('ci:crop')?></a></li></li>
            <li><a href="#" data-action="rotate-left" class="rotate"><?=lang('ci:rotate_left')?></a></li>
            <li><a href="#" data-action="rotate-right" class="rotate"><?=lang('ci:rotate_right')?></a></li>
            <li><a href="#" data-action="flip-hor"><?=lang('ci:flip_hor')?></a></li>
            <li><a href="#" data-action="flip-ver"><?=lang('ci:flip_ver')?></a></li>
        </ul>

        <div class="ci_eiw_bottombar bottombar">
            <span class="crop_holder" style="display:none">
                <a class="submit apply_crop"><?=lang('ci:apply_crop')?></a>
                <a class="submit cancel_crop"><?=lang('ci:cancel_crop')?></a>
                <br clear="all"><br>
                X:&nbsp;&nbsp;&nbsp;<input type="text" class="jcrop_x">&nbsp;&nbsp;&nbsp;&nbsp;Y: <input type="text" class="jcrop_y"> <br>
                X2: <input type="text" class="jcrop_x2">&nbsp;&nbsp;Y2: <input type="text" class="jcrop_y2">
                <br clear="all"><br>
                <a class="submit set_sel"><?=lang('ci:set_crop_sel')?></a>
            </span>
            <span class="save_image_holder">
                <span class="regen_sizes"><input type="checkbox" name="regen_sizes" value="yes"> <?=lang('ci:regen_sizes')?><br><br></span>
                <a class="submit save_image"><?=lang('ci:save_img')?></a>
                <a class="submit cancel_image"><?=lang('ci:cancel')?></a>
            </span>
            <p class="loading">loading....</p>
        </div>

        <br><br>
    </div>

    <div class="imgholder" style="text-align:center;">
        <img src="<?=$img_url?>" data-alturl="<?=$img_url_alt?>" id="jcrop_target" data-realwidth="<?=$img_info[0]?>" data-realheight="<?=$img_info[1]?>" style="max-width:100%;">
        <p><?=lang('ci:image_scaled_note')?></p>
    </div>

<br clear="all">
</div>






