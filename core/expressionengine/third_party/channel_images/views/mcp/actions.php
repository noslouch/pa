<?php echo $this->view('mcp/_menu'); ?>
<div class="ci-body">

<div class="utable">

<h2><?=lang('ci:image_list')?></h2>
<table>
    <tr class="image-filters">
        <td class="filter filter-channel" style="width:300px">
            <?=form_multiselect('channels[]', $channels, '', ' class="select2" placeholder="Limit By: Channels"  style="width:300px"')?>
        </td>
        <td class="filter filter-fields" style="width:300px">
            <?=form_multiselect('fields[]', $fields, '', ' class="select2" placeholder="Limit By: Fields"  style="width:300px"')?>
        </td>

        <td class="filter filter-fields" style="width:150px">
            <?=form_input('offset', '', ' placeholder="Override Start Position"')?>
        </td>
        <td class="filter filter-fields" style="width:230px">
            <?=form_input('entry_id', '', ' placeholder="Limit By: Entry IDs (comma sep.)"')?>
        </td>
        <td class="total-images" colspan="50">
            <?=lang('ci:total_images')?>: <strong class="total_count"></strong>
        </td>
    </tr>
</table>

</div>

<div class="utable">
<h2>
    <?=lang('ci:action')?>:

    <span class="action-toggler">
        <input type="radio" name="action" value="regen" checked> Regenerate Images &nbsp;&nbsp;
        <input type="radio" name="action" value="transfer"> Transfer Images
    </span>
</h2>

<table class="actions action-regen">
    <thead>
        <tr>
            <th>Group</th>
            <th>Field</th>
            <th>Sizes</th>
        </tr>
    </thead>
    <tbody class="image_sizes">

    </tbody>
</table>

<table class="actions action-transfer">
    <tbody>
        <tr>
            <td style="width:200px"><label><?=lang('ci:transfer_to')?></label></td>
            <td class="transfer-toggle">
                <input type="radio" name="transfer[to]" value="s3" checked> Amazon S3<br>
                <input type="radio" name="transfer[to]" value="cloudfiles"> Rackspace CloudFiles
            </td>
        </tr>
    </tbody>
    <tbody class="options option-s3">
        <tr>
            <td><label>AWS KEY</label></td>
            <td><input type="text" value="" name="s3[key]"></td>
        </tr>
        <tr>
            <td><label>AWS SECRET KEY</label></td>
            <td><input type="text" value="" name="s3[secret_key]"></td>
        </tr>
        <tr>
            <td><label>Bucket</label></td>
            <td><input type="text" value="" name="s3[bucket]"></td>
        </tr>
        <tr>
            <td><label>ACL</label></td>
            <td>
                <select name="s3[acl]">
                    <option value="private">Owner-only read</option>
                    <option selected="selected" value="public-read">Public READ</option>
                    <option value="authenticated-read">Public Authenticated Read</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label>Storage Redundancy</label></td>
            <td>
                <select name="s3[storage]">
                    <option selected="selected" value="standard">Standard storage redundancy</option>
                    <option value="reduced">Reduced storage redundancy</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label>Subdirectory (optional)</label></td>
            <td><input type="text" value="" name="s3[directory]"></td>
        </tr>
    </tbody>

    <tbody class="options option-cloudfiles">

        <tr>
            <td><label>Username</label></td>
            <td><input type="text" value="" name="cloudfiles[username]"></td>
        </tr>
        <tr>
            <td><label>API Key</label></td>
            <td><input type="text" value="" name="cloudfiles[api]"></td>
        </tr>
        <tr>
            <td><label>Container</label></td>
            <td><input type="text" value="" name="cloudfiles[container]"></td>
        </tr>
        <tr>
            <td><label>Region</label></td>
            <td>
                <select name="cloudfiles[region]">
                    <option selected="selected" value="us">United States</option>
                    <option value="uk">United Kingdom (London)</option>
                </select>
            </td>
        </tr>
    </tbody>
</table>

</div>


<div class="utable progress_holder">
<h2>
    <?=lang('ci:progress')?>&nbsp;&nbsp;&nbsp;
    <button class="submit start_actions"><?=lang('ci:start')?></button>&nbsp;&nbsp;
    <small><?=lang('ci:start_tip')?></small>
</h2>

<table>
    <tbody class="current-actions">

    </tbody>

    <tbody>
        <tr>
            <td class="total-progress" colspan="20">
                <div class="progress">
                    <div class="progress-text">
                        <span class="total_done">0</span> of <span class="total_count"></span>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>




</div> <!-- </ci-body> -->
<?php echo $this->view('mcp/_footer'); ?>
