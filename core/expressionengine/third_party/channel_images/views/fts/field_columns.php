<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable" style="width:80%">
    <thead>
        <tr>
            <th colspan="99">
                <h4>
                    <?=lang('ci:field_columns')?>
                    <small><?=lang('ci:field_columns_exp')?></small>
                </h4>
            </th>
        </tr>
        <tr>
            <th>Field</th>
            <th>Label</th>
            <th>Default Content</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($columns as $name => $val):?>
        <tr>
            <td><?=lang('ci:'.$name)?></td>
            <td>
                <input type="text" name="channel_images[columns][<?=$name?>]'" <?php if (isset($override['columns'][$name]) === true):?>disabled value="<?=$override['columns'][$name]?>" <?php else:?> value="<?=$val?>"  <?php endif;?> style="border:1px solid #ccc; width:150px;">
            </td>
            <td>
            <?php if (!in_array($name, array('row_num', 'id', 'image', 'filename'))):?>
                <input type="text" name="channel_images[columns_default][<?=$name?>]'" <?php if (isset($override['columns_default'][$name]) === true):?>disabled value="<?=$override['columns_default'][$name]?>" <?php else:?> value="<?=$columns_default[$name]?>"  <?php endif;?> style="border:1px solid #ccc; width:150px;">
            <?php endif;?>
            </td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>