<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable" style="width:80%">
	<thead>
		<tr>
			<th style="width:180px"><?=lang('ci:pref')?></th>
			<th><?=lang('ci:value')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:keep_original')?></td>
			<td>
				<input name="channel_images[keep_original]" <?php if (isset($override['keep_original'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($keep_original == 'yes') echo 'checked'?>> <?=lang('ci:yes')?>&nbsp;&nbsp;
				<input name="channel_images[keep_original]" <?php if (isset($override['keep_original'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($keep_original == 'no') echo 'checked'?>> <?=lang('ci:no')?>
				<small><?=lang('ci:keep_original_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:upload_location')?></td>
			<td class="ci_upload_type">
				<input name="channel_images[upload_location]" <?php if (isset($override['upload_location'])):?>disabled="disabled"<?php endif;?> type="radio" value="local" <?php if ($upload_location == 'local') echo 'checked'?> > <?=lang('ci:local')?>&nbsp;&nbsp;
				<input name="channel_images[upload_location]" <?php if (isset($override['upload_location'])):?>disabled="disabled"<?php endif;?> type="radio" value="s3" <?php if ($upload_location == 's3') echo 'checked'?> > <?=lang('ci:s3')?>&nbsp;&nbsp;
				<input name="channel_images[upload_location]" <?php if (isset($override['upload_location'])):?>disabled="disabled"<?php endif;?> type="radio" value="cloudfiles" <?php if ($upload_location == 'cloudfiles') echo 'checked'?> > <?=lang('ci:cloudfiles')?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><a href="#" class="TestLocation"><?=lang('ci:test_location')?></a></td>
		</tr>
	</tbody>
</table>


<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CIUpload_local" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:local')?>
					<small><?=lang('ci:specify_pref_cred')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:upload_location')?></td>
			<td><?=form_dropdown('channel_images[locations][local][location]', $local['locations'], $locations['local']['location'], ((isset($override['locations']['local']['location']) === true) ? 'disabled' : '' )  ); ?>
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CIUpload_s3" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:s3')?>
					<small><?=lang('ci:specify_pref_cred')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:s3:key')?> <small><?=lang('ci:s3:key_exp')?></small></td>
			<td>
				<input type="text" name="channel_images[locations][s3][key]" <?php if (isset($override['locations']['s3']['key']) === true):?>disabled<?php endif;?> value="<?=$locations['s3']['key']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:secret_key')?> <small><?=lang('ci:s3:secret_key_exp')?></small></td>
			<td>
				<input type="text" name="channel_images[locations][s3][secret_key]" <?php if (isset($override['locations']['s3']['secret_key']) === true):?>disabled<?php endif;?> value="<?=$locations['s3']['secret_key']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:bucket')?> <small><?=lang('ci:s3:bucket_exp')?></small></td>
			<td>
				<input type="text" name="channel_images[locations][s3][bucket]" <?php if (isset($override['locations']['s3']['bucket']) === true):?>disabled<?php endif;?> value="<?=$locations['s3']['bucket']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:region')?></td>
			<td>
				<?=form_dropdown('channel_images[locations][s3][region]', $s3['regions'], $locations['s3']['region'], ((isset($override['locations']['s3']['region']) === true) ? 'disabled' : '' )  ); ?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:acl')?> <small><?=lang('ci:s3:acl_exp')?></small></td>
			<td>
				<?=form_dropdown('channel_images[locations][s3][acl]', $s3['acl'], $locations['s3']['acl'], ((isset($override['locations']['s3']['acl']) === true) ? 'disabled' : '' )  ); ?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:storage')?></td>
			<td>
				<?=form_dropdown('channel_images[locations][s3][storage]', $s3['storage'], $locations['s3']['storage'], ((isset($override['locations']['s3']['storage']) === true) ? 'disabled' : '' )  ); ?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:directory')?></td>
			<td>
				<input type="text" name="channel_images[locations][s3][directory]" <?php if (isset($override['locations']['s3']['directory']) === true):?>disabled<?php endif;?> value="<?=$locations['s3']['directory']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:cloudfrontd')?></td>
			<td>
				<input type="text" name="channel_images[locations][s3][cloudfront_domain]" <?php if (isset($override['locations']['s3']['cloudfront_domain']) === true):?>disabled<?php endif;?> value="<?=$locations['s3']['cloudfront_domain']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CIUpload_cloudfiles" style="width:80%">
	<thead>
		<tr>
			<th colspan="2">
				<h4>
					<?=lang('ci:cloudfiles')?>
					<small><?=lang('ci:specify_pref_cred')?></small>
				</h4>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:cloudfiles:username')?></td>
			<td>
				<input type="text" name="channel_images[locations][cloudfiles][username]" <?php if (isset($override['locations']['cloudfiles']['username']) === true):?>disabled<?php endif;?> value="<?=$locations['cloudfiles']['username']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:api')?></td>
			<td>
				<input type="text" name="channel_images[locations][cloudfiles][api]" <?php if (isset($override['locations']['cloudfiles']['api']) === true):?>disabled<?php endif;?> value="<?=$locations['cloudfiles']['api']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:container')?></td>
			<td>
				<input type="text" name="channel_images[locations][cloudfiles][container]" <?php if (isset($override['locations']['cloudfiles']['container']) === true):?>disabled<?php endif;?> value="<?=$locations['cloudfiles']['container']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:region')?></td>
			<td>
				<?=form_dropdown('channel_images[locations][cloudfiles][region]', $cloudfiles['regions'], $locations['cloudfiles']['region'], ((isset($override['locations']['cloudfiles']['region']) === true) ? 'disabled' : '' ) ); ?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:cdn_uri')?></td>
			<td>
				<input type="text" name="channel_images[locations][cloudfiles][cdn_uri]" <?php if (isset($override['locations']['cloudfiles']['cdn_uri']) === true):?>disabled<?php endif;?> value="<?=$locations['cloudfiles']['cdn_uri']?>" style="border:1px solid #ccc; width:150px;">
			</td>
		</tr>
	</tbody>
</table>
