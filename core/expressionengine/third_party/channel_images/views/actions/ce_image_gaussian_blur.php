<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
    <thead>
        <tr>
            <th><?=lang('ci:repeat')?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?=form_input($action_field_name.'[repeat]', $repeat, 'style="border:1px solid #ccc; width:80%;"')?>
            </td>

        </tr>
    </tbody>
</table>

<small><?=lang('ce:gaussian_blur:exp')?></small>
