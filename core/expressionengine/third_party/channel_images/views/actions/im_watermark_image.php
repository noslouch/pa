<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
    <thead>
        <tr>
            <th colspan="2">
                <h4>
                    <?=lang('ci:watermark:position_settings')?>
                </h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?=lang('ci:watermark:horoffset')?> </td>
            <td>
                <?=form_input($action_field_name.'[horizontal_offset]', $horizontal_offset, 'style="border:1px solid #ccc; width:80%;"')?>
                <small><?=lang('ci:watermark:horoffset:exp')?></small>
            </td>
        </tr>
        <tr>
            <td><?=lang('ci:watermark:vrtoffset')?> </td>
            <td>
                <?=form_input($action_field_name.'[vertical_offset]', $vertical_offset, 'style="border:1px solid #ccc; width:80%;"')?>
            </td>
        </tr>
        <tr>
            <td>Opacity</td>
            <td>
                <?=form_input($action_field_name.'[opacity]', $opacity, 'style="border:1px solid #ccc; width:80%;"')?><br>
                <small>The level of transparency: 1.0 is fully opaque and 0.0 is fully transparent.</small>
            </td>
        </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable">
    <thead>
        <tr>
            <th colspan="2">
                <h4>
                    <?=lang('ci:watermark:overlay_pref')?>
                </h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?=lang('ci:watermark:overlay_path')?> </td>
            <td>
                <?=form_input($action_field_name.'[overlay_path]', $overlay_path, 'style="border:1px solid #ccc; width:80%;"')?>
            </td>
        </tr>
    </tbody>
</table>
