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
				<?=form_dropdown('channel_images[keep_original]', array('yes' => lang('ci:yes'), 'no' => lang('ci:no')), $keep_original)?>
				<small><?=lang('ci:keep_original_exp')?></small>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:upload_location')?></td>
			<td><?=form_dropdown('channel_images[upload_location]', $upload_locations, $upload_location, ' class="ci_upload_type" ')?></td>
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
			<td><?=form_dropdown('channel_images[locations][local][location]', $local['locations'], $locations['local']['location'] ); ?></td>
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
			<td><?=form_input('channel_images[locations][s3][key]', $locations['s3']['key'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:secret_key')?> <small><?=lang('ci:s3:secret_key_exp')?></small></td>
			<td><?=form_input('channel_images[locations][s3][secret_key]', $locations['s3']['secret_key'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:bucket')?> <small><?=lang('ci:s3:bucket_exp')?></small></td>
			<td><?=form_input('channel_images[locations][s3][bucket]', $locations['s3']['bucket'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:region')?></td>
			<td><?=form_dropdown('channel_images[locations][s3][region]', $s3['regions'], $locations['s3']['region']); ?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:acl')?> <small><?=lang('ci:s3:acl_exp')?></small></td>
			<td><?=form_dropdown('channel_images[locations][s3][acl]', $s3['acl'], $locations['s3']['acl']); ?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:storage')?></td>
			<td><?=form_dropdown('channel_images[locations][s3][storage]', $s3['storage'], $locations['s3']['storage']); ?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:directory')?></td>
			<td><?=form_input('channel_images[locations][s3][directory]', $locations['s3']['directory'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:s3:cloudfrontd')?></td>
			<td><?=form_input('channel_images[locations][s3][cloudfront_domain]', $locations['s3']['cloudfront_domain'])?></td>
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
			<td><?=form_input('channel_images[locations][cloudfiles][username]', $locations['cloudfiles']['username'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:api')?></td>
			<td><?=form_input('channel_images[locations][cloudfiles][api]', $locations['cloudfiles']['api'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:container')?></td>
			<td><?=form_input('channel_images[locations][cloudfiles][container]', $locations['cloudfiles']['container'])?></td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:region')?></td>
			<td><?=form_dropdown('channel_images[locations][cloudfiles][region]', $cloudfiles['regions'], $locations['cloudfiles']['region']); ?></td>
		</tr>
		<tr>
			<td><?=lang('ci:cloudfiles:cdn_uri')?></td>
			<td><?=form_input('channel_images[locations][cloudfiles][cdn_uri]', $locations['cloudfiles']['cdn_uri'])?></td>
		</tr>
	</tbody>
</table>