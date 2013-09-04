<?=$this->view('_topmenu')?>
<div id="updater-settings">

<?php if ($this->session->userdata['group_id'] != 1):?>
<p style="padding:20px 20px 0"><strong><?=lang('u:only_super_admin')?></strong></p>
<?php else:?>


<?=form_open($base_url_short.AMP.'method=update_settings', array('enctype' => 'multipart/form-data', 'method'=>'POST'));?>

<div class="utable">
<h2>
	<?=lang('u:transfer_method')?>
	<span class="options js-togglefiletransfer">
		<input name="settings[file_transfer_method]" <?php if (isset($override_settings['file_transfer_method'])):?>disabled="disabled"<?php endif;?> type="radio" value="local" <?php if ($settings['file_transfer_method'] == 'local') echo 'checked'?>> <?=lang('u:local')?>&nbsp;&nbsp;
		<input name="settings[file_transfer_method]" <?php if (isset($override_settings['file_transfer_method'])):?>disabled="disabled"<?php endif;?> type="radio" value="ftp" <?php if ($settings['file_transfer_method'] == 'ftp') echo 'checked'?>> <?=lang('u:ftp')?>&nbsp;&nbsp;
		<input name="settings[file_transfer_method]" <?php if (isset($override_settings['file_transfer_method'])):?>disabled="disabled"<?php endif;?> type="radio" value="sftp" <?php if ($settings['file_transfer_method'] == 'sftp') echo 'checked'?>> <?=lang('u:sftp')?>&nbsp;&nbsp;
	</span>

	<button class="submit submit-right js-test_settings"><?=lang('u:test_settings')?></button>
</h2>
<table class="file_transfer_methods">
	<tbody class="js-local">

	</tbody>
	<tbody class="js-ftp">
		<tr>
			<td style="width:200px"><label><?=lang('u:hostname')?></label></td>
			<td><input name="settings[ftp][hostname]" value="<?=$settings['ftp']['hostname']?>" type="text" <?php if (isset($override_settings['ftp']['hostname'])):?>disabled="disabled"<?php endif;?> ></td>
			<td style="width:200px"><label><?=lang('u:port')?></label></td>
			<td><input name="settings[ftp][port]" value="<?=$settings['ftp']['port']?>" type="text" <?php if (isset($override_settings['ftp']['port'])):?>disabled="disabled"<?php endif;?> ></td>
		</tr>
		<tr>
			<td><label><?=lang('u:username')?></label></td>
			<td><input name="settings[ftp][username]" value="<?=$settings['ftp']['username']?>" type="text" <?php if (isset($override_settings['ftp']['username'])):?>disabled="disabled"<?php endif;?> ></td>
			<td><label><?=lang('u:password')?></label></td>
			<td><input name="settings[ftp][password]" value="<?=$settings['ftp']['password']?>" type="password" <?php if (isset($override_settings['ftp']['password'])):?>disabled="disabled"<?php endif;?> ></td>
		</tr>
		<tr>
			<td><label><?=lang('u:passive')?></label></td>
			<td>
				<input name="settings[ftp][passive]" <?php if (isset($override_settings['ftp']['passive'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($settings['ftp']['passive'] == 'yes') echo 'checked'?>> <?=lang('u:yes')?>&nbsp;&nbsp;
				<input name="settings[ftp][passive]" <?php if (isset($override_settings['ftp']['passive'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($settings['ftp']['passive'] == 'no') echo 'checked'?>> <?=lang('u:no')?>

			</td>
			<td><label><?=lang('u:ssl')?></label></td>
			<td>
				<input name="settings[ftp][ssl]" <?php if (isset($override_settings['ftp']['ssl'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($settings['ftp']['ssl'] == 'yes') echo 'checked'?>> <?=lang('u:yes')?>&nbsp;&nbsp;
				<input name="settings[ftp][ssl]" <?php if (isset($override_settings['ftp']['ssl'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($settings['ftp']['ssl'] == 'no') echo 'checked'?>> <?=lang('u:no')?>
			</td>
		</tr>
	</tbody>
	<tbody class="js-sftp">
		<tr>
			<td style="width:200px"><label><?=lang('u:hostname')?></label></td>
			<td><input name="settings[sftp][hostname]" value="<?=$settings['sftp']['hostname']?>" type="text" <?php if (isset($override_settings['sftp']['hostname'])):?>disabled="disabled"<?php endif;?> ></td>
			<td style="width:200px"><label><?=lang('u:port')?></label></td>
			<td><input name="settings[sftp][port]" value="<?=$settings['sftp']['port']?>" type="text" <?php if (isset($override_settings['sftp']['port'])):?>disabled="disabled"<?php endif;?> ></td>
		</tr>
		<tr>
			<td><label><?=lang('u:username')?></label></td>
			<td><input name="settings[sftp][username]" value="<?=$settings['sftp']['username']?>" type="text" <?php if (isset($override_settings['sftp']['username'])):?>disabled="disabled"<?php endif;?> ></td>
			<td><label><?=lang('u:password')?></label></td>
			<td><input name="settings[sftp][password]" value="<?=$settings['sftp']['password']?>" type="password" <?php if (isset($override_settings['sftp']['password'])):?>disabled="disabled"<?php endif;?> ></td>
		</tr>
		<tr>
			<td><label><?=lang('u:auth_method')?></label></td>
			<td>
				<input name="settings[sftp][auth_method]" <?php if (isset($override_settings['sftp']['auth_method'])):?>disabled="disabled"<?php endif;?> type="radio" value="password" <?php if ($settings['sftp']['auth_method'] == 'password') echo 'checked'?>> <?=lang('u:password')?>&nbsp;&nbsp;
				<input name="settings[sftp][auth_method]" <?php if (isset($override_settings['sftp']['auth_method'])):?>disabled="disabled"<?php endif;?> type="radio" value="key" <?php if ($settings['sftp']['auth_method'] == 'key') echo 'checked'?>> <?=lang('u:public_key')?>
			</td>
			<td rowspan="3"><label><?=lang('u:key_contents')?></label></td>
			<td rowspan="3">
				<div>
					<textarea name="settings[sftp][key_contents]" <?php if (isset($override_settings['sftp']['key_contents'])):?>disabled="disabled"<?php endif;?> style="height:100px;"><?=$settings['sftp']['key_contents']?></textarea>
				</div>
			</td>
		</tr>
		<tr>
			<td><label><?=lang('u:key_password')?></label></td>
			<td><input name="settings[sftp][key_password]" value="<?=$settings['sftp']['key_password']?>" type="text" <?php if (isset($override_settings['sftp']['key_password'])):?>disabled="disabled"<?php endif;?> ></td>
		</tr>
		<tr>
			<td><label><?=lang('u:key_path')?></label></td>
			<td><input name="settings[sftp][key_path]" value="<?=$settings['sftp']['key_path']?>" type="text" <?php if (isset($override_settings['sftp']['key_path'])):?>disabled="disabled"<?php endif;?> ></td>
		</tr>

	</tbody>

	<tbody class="js-ftp js-sftp">
		<tr>
			<td><label><?=lang('u:login_check')?></label></td>
			<td colspan="3" class="login login-testing">
				<em class="retest"><?=lang('u:login_retest')?></em>
				<span class="testing"><?=lang('u:login_testing')?></span>
				<span class="failed"><?=lang('u:login_failed')?></span>
				<span class="success"><?=lang('u:login_success')?></span>
			</td>
		</tr>
	</tbody>
</table>
</div>

<div class="utable">
<h2>
	<?=lang('u:path_map')?>
	<small><?=lang('u:path_map_exp')?></small>
</h2>
<table>
	<tbody>
		<tr>
			<td colspan="2" class="subtable">
				<table>
					<tr>
						<td style="width:150px"><label><?=lang('u:dir_root')?></label></td>
						<td class="pathmap map-root">
							<input name="settings[path_map][root]" value="<?=$settings['path_map']['root']?>" type="text" <?php if (isset($override_settings['path_map']['root'])):?>disabled="disabled"<?php endif;?> >
							<span class="browse" data-map="root"><?=lang('u:browse')?></span>
						</td>
						<td style="width:150px"><label><?=lang('u:dir_backup')?></label></td>
						<td class="pathmap map-backup">
							<input name="settings[path_map][backup]" value="<?=$settings['path_map']['backup']?>" type="text" <?php if (isset($override_settings['path_map']['backup'])):?>disabled="disabled"<?php endif;?> >
							<span class="browse" data-map="backup"><?=lang('u:browse')?></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="subtable">
				<table>
					<tr>
						<td style="width:150px"><label><?=lang('u:dir_system')?></label></td>
						<td class="pathmap map-system">
							<input name="settings[path_map][system]" value="<?=$settings['path_map']['system']?>" type="text" <?php if (isset($override_settings['path_map']['system'])):?>disabled="disabled"<?php endif;?> >
							<span class="browse" data-map="system"><?=lang('u:browse')?></span>
						</td>
						<td style="width:150px"><label><?=lang('u:dir_system_third_party')?></label></td>
						<td class="pathmap map-system_third_party">
							<input name="settings[path_map][system_third_party]" value="<?=$settings['path_map']['system_third_party']?>" type="text" <?php if (isset($override_settings['path_map']['system_third_party'])):?>disabled="disabled"<?php endif;?> >
							<span class="browse" data-map="system_third_party"><?=lang('u:browse')?></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="subtable">
				<table>
					<tr>
						<td style="width:150px"><label><?=lang('u:dir_themes')?></label></td>
						<td class="pathmap map-themes">
							<input name="settings[path_map][themes]" value="<?=$settings['path_map']['themes']?>" type="text" <?php if (isset($override_settings['path_map']['themes'])):?>disabled="disabled"<?php endif;?> >
							<span class="browse" data-map="themes"><?=lang('u:browse')?></span>
						</td>
						<td style="width:150px"><label><?=lang('u:dir_themes_third_party')?></label></td>
						<td class="pathmap map-themes_third_party">
							<input name="settings[path_map][themes_third_party]" value="<?=$settings['path_map']['themes_third_party']?>" type="text" <?php if (isset($override_settings['path_map']['themes_third_party'])):?>disabled="disabled"<?php endif;?> >
							<span class="browse" data-map="themes_third_party"><?=lang('u:browse')?></span>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</div>

<div class="utable">
<h2><?=lang('u:menu_link')?></h2>
<table>
	<tbody>
		<tr>
			<td colspan="2" class="subtable">
				<table>
					<tr>
						<td style="width:250px"><label><?=lang('u:link_root')?></label></td>
						<td>
							<input name="settings[menu_link][root]" <?php if (isset($override_settings['menu_link']['root'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($settings['menu_link']['root'] == 'yes') echo 'checked'?>> <?=lang('u:yes')?>&nbsp;&nbsp;
							<input name="settings[menu_link][root]" <?php if (isset($override_settings['menu_link']['root'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($settings['menu_link']['root'] == 'no') echo 'checked'?>> <?=lang('u:no')?>
						</td>
						<td style="width:250px"><label><?=lang('u:link_tools')?></label></td>
						<td>
							<input name="settings[menu_link][tools]" <?php if (isset($override_settings['menu_link']['tools'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($settings['menu_link']['tools'] == 'yes') echo 'checked'?>> <?=lang('u:yes')?>&nbsp;&nbsp;
							<input name="settings[menu_link][tools]" <?php if (isset($override_settings['menu_link']['tools'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($settings['menu_link']['tools'] == 'no') echo 'checked'?>> <?=lang('u:no')?>
						</td>
					</tr>
					<tr>
						<td style="width:250px"><label><?=lang('u:link_admin')?></label></td>
						<td>
							<input name="settings[menu_link][admin]" <?php if (isset($override_settings['menu_link']['admin'])):?>disabled="disabled"<?php endif;?> type="radio" value="yes" <?php if ($settings['menu_link']['admin'] == 'yes') echo 'checked'?>> <?=lang('u:yes')?>&nbsp;&nbsp;
							<input name="settings[menu_link][admin]" <?php if (isset($override_settings['menu_link']['admin'])):?>disabled="disabled"<?php endif;?> type="radio" value="no" <?php if ($settings['menu_link']['admin'] == 'no') echo 'checked'?>> <?=lang('u:no')?>
						</td>
						<td style="width:250px">
							<label><?=lang('u:act_url')?></label><br>
							<small><?=lang('u:act_change')?></small>
						</td>
						<td>
							<input name="settings[action_url][actionGeneralRouter]" value="<?=$settings['action_url']['actionGeneralRouter']?>" type="text" <?php if (isset($override_settings['action_url']['actionGeneralRouter'])):?>disabled="disabled"<?php endif;?> >

						</td>
					</tr>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</div>

<div class="utable" style="width:30%; float:left;">
	<h2><button class="submit"><?=lang('u:update_settings')?></button></h2>
</div>

<div class="utable" style="width:50%; float:right;">
	<h2>
		<?=lang('u:anon_stats')?>

		<small style="font-size:12px">
		<?php if (isset($override_settings['track_stats']) == TRUE):?>
		<input name="settings[track_stats]" disabled="disabled" type="radio" value="yes" <?php if ($override_settings['track_stats'] == 'yes') echo 'checked'?>> <?=lang('u:yes:rec')?>&nbsp;&nbsp;
		<input name="settings[track_stats]" disabled="disabled" type="radio" value="no" <?php if ($override_settings['track_stats'] == 'no') echo 'checked'?>> <?=lang('u:no')?>&nbsp;&nbsp;
		<?php else:?>
		<input name="settings[track_stats]" type="radio" value="yes" <?php if ($settings['track_stats'] == 'yes') echo 'checked'?>> <?=lang('u:yes:rec')?>&nbsp;&nbsp;
		<input name="settings[track_stats]" type="radio" value="no" <?php if ($settings['track_stats'] == 'no') echo 'checked'?>> <?=lang('u:no')?>&nbsp;&nbsp;
		<?php endif;?>
		</small>

		<br>
		<small style="margin:0">
			<?=lang('u:anon_stats:exp')?><br>
			<a href="#" onclick="$('#upd_example_usage_stats').toggle();return false;"><?=lang('u:anon_stats:what')?></a>
		</small>
	</h2>

	<table style="display:none;" id="upd_example_usage_stats">
	<tbody>
		<tr>
			<td>
				<textarea style="height:250px;">
{
	"event": "addon_zip",
	"properties": {
		"addon": "updater",
		"version": "5.0.0",
		"detection_type": "package.json",
		"success": "yes"
		"app_version": "<?=APP_VER?>",
		"app_build": "<?=APP_BUILD?>",
		"updater_version": "2.0.0",
		"php_version": "5.3",
		"server_os": "unix",
	}
}
				</textarea>
			</td>
			<td>
				<textarea style="height:250px;">
{
	"event": "ee_update",
	"properties": {
		"version_from": "2.5.0",
		"version_to": "2.5.5",
		"success": "yes"
		"transfer_method": "local",
		"app_version": "<?=APP_VER?>",
		"app_build": "<?=APP_BUILD?>",
		"updater_version": "2.0.0",
		"php_version": "5.3",
		"server_os": "unix",
	}
}
				</textarea>
			</td>
		</tr>
	</tbody>
</table>
</div>


<br clear="all">



<!-- Modal -->
<div id="updater_folder_browse" class="modal hide fade updater-browse" tabindex="-1">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3><?=lang('u:browse')?></h3>
    </div>
    <div class="modal-body">
    	<div class="status">
    		<span class="loading"><?=lang('u:loading')?></span>
    		<span class="error"><?=lang('u:browse_error')?></span>
    	</div>
        <div class="content"></div>
        <div class="path">
            <input type="text" readonly value="/">
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true"><?=lang('u:close')?></button>
        <button class="btn btn-primary"><?=lang('u:save')?></button>
    </div>
</div>


<?=form_close();?>

<?php endif;?>
</div> <!--#updater-settings -->
<?=$this->view('_footer')?>
