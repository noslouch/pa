<?php echo $this->view('menu'); ?>
<div id="ubody">

<?php if ($this->session->userdata['group_id'] != 1):?>
<p style="padding:20px 20px 0"><strong><?=lang('only_super_admins')?></strong></p>
<?php else:?>

<div id="leftsection">
	<div class="utable" id="process_form">
	<?=form_open($action_url, array('enctype' => 'multipart/form-data', 'method'=>'POST'));?>

	<?php if ($disable_btn):?>
		<h2><?=lang('info_req')?></h2>
		<table>
			<tbody>
				<tr>
					<td><label><?=lang('php_zip')?></label></td>
					<td>
					<?php if ($zip_extension):?>
						<strong class="label label-success"><?=lang('passed')?></strong>
					<?php else:?>
						<strong class="label label-important"><?=lang('not_passed')?></strong>
					<?php endif;?>
					</td>
					<td>
						<?php if ($zip_extension):?>v<?=$zip_extension_version?><?php endif;?>
					</td>
				</tr>
				<tr>
					<td><label><?=lang('post_max_size')?></label></td>
					<td>
					<?php if ($post_5mb):?>
						<strong class="label label-success"><?=lang('passed')?></strong>
					<?php else:?>
						<strong class="label label-important"><?=lang('not_passed')?></strong>
					<?php endif;?>
					</td>
					<td><?=$post_max_size?></td>
				</tr>
				<tr>
					<td><label><?=lang('upload_max_size')?></label></td>
					<td>
					<?php if ($upload_5mb):?>
						<strong class="label label-success"><?=lang('passed')?></strong>
					<?php else:?>
						<strong class="label label-important"><?=lang('not_passed')?></strong>
					<?php endif;?>
					</td>
					<td><?=$upload_max_filesize?></td>
				</tr>
				<tr>
					<td><label><?=lang('settings_saved')?></label></td>
					<td>
					<?php if ($settings_done):?>
						<strong class="label label-success"><?=lang('passed')?></strong>
					<?php else:?>
						<strong class="label label-important"><?=lang('not_passed')?></strong>
					<?php endif;?>
					</td>
					<td></td>
				</tr>
			</tbody>
		</table>
	<?php endif;?>


		<h2>
			<?=lang('update_ee')?>
			<span class="submit" id="test_settings" style="font-size:12px;float:right;cursor:pointer;"><?=lang('test_settings')?></span>
		</h2>
		<table>
			<tbody>
				<tr>
					<td><label><?=lang('current_ee_version')?></label></td>
					<td colspan="2">
						v<?=APP_VER?> - Build: <?=APP_BUILD?>
					</td>
				</tr>
				<tr>
					<td><label><?=lang('backup_db')?></label></td>
					<td class="backup_db">
						<input type="radio" name="backup_db" value="yes" checked> <?=lang('yes')?>&nbsp;&nbsp;
						<input type="radio" name="backup_db" value="no"> <?=lang('no')?>
					</td>
				</tr>
				<tr>
					<td><label><?=lang('backup_files')?></label></td>
					<td class="backup_files">
						<input type="radio" name="backup_files" value="yes" checked> <?=lang('yes')?>&nbsp;&nbsp;
						<input type="radio" name="backup_files" value="no"> <?=lang('no')?>
					</td>
				</tr>
				<tr>
					<td><label><?=lang('ee_file')?></label></td>
					<td>
						<input type="file" name="file">
					</td>
				</tr>
				<tr>
					<td><label><?=lang('ee_file_url')?></label></td>
					<td>
						<input type="text" name="file_url">
					</td>
				</tr>
			</tbody>
		</table>

		<h2><input type="submit" class="submit" id="process_ee" value="<?=lang('start_update')?>" <?php if ($disable_btn):?>disabled="disabled" style="background:#DDE2E5"<?php endif;?> ></h2>
		<input type="hidden" name="key" value="<?=$this->localize->now?>" class='temp_key'>
	<?=form_close();?>
	</div>
</div> <!-- #leftsection -->

<div id="rightsection">
	<div class="utable" id="process_log">

		<div class="not_started">
			<h2><?=lang('process_log')?></h2>
			<table>
				<tbody>
					<tr><td colspan="2"><?=lang('process_not_started')?></td></tr>
				</tbody>
			</table>
		</div>

		<div class="pre_process hidden">
			<h2><?=lang('pre_process_log')?></h2>
			<table>
				<tbody class="pre_process">
					<tr class="action__upload_file">
						<td class="action"><label><?=lang('upload_file')?></label></td>
						<td class="state"><span class="label label-info"><?=lang('working')?></span></td>
						<td class="msg"><span class="loading"><?=lang('uploading_file')?></span></td>
					</tr>
					<tr class="action__extract_zip">
						<td class="action"><label><?=lang('extract_zip')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"></td>
					</tr>
					<tr class="action__ee_zip">
						<td class="action"><label><?=lang('ee_zip')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"></td>
					</tr>
					<tr class="action__ee_info">
						<td class="action"><label><?=lang('ee_info')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="backup_process hidden">
			<h2><?=lang('backup_log')?></h2>
			<table>
				<tbody>
					<tr class="action__backup_db">
						<td class="action"><label><?=lang('backup_db')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg">
							<span class="label skipped hidden"><?=lang('skipped')?></span>
						    <div class="progress progress-striped active hidden" style="margin:0 0 5px 0">
								<div class="bar" style="width:0%;"></div>
					    	</div>
					    	<span class="loading"><?=lang('preparing_db')?></span>
					    	<span class="error"></span>
						</td>
					</tr>
					<tr class="action__backup_files">
						<td class="action"><label><?=lang('backup_files')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg">
							<span class="label skipped hidden"><?=lang('skipped')?></span>
						    <div class="progress progress-striped active hidden" style="margin:0 0 5px 0">
								<div class="bar" style="width:0%;"></div>
					    	</div>
					    	<span class="loading"><?=lang('preparing_files')?></span>
					    	<span class="error"></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="update_init hidden">
			<h2><?=lang('update_init')?></h2>
			<table>
				<tbody>
					<tr class="action__site_off">
						<td class="action"><label><?=lang('site_off')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg">
							<span class="loading"><?=lang('site_off_loading')?></span>
						</td>
					</tr>
					<tr class="action__copy_installer">
						<td class="action"><label><?=lang('copy_installer')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"><span class="loading"><?=lang('copy_installer_loading')?></span></td>
					</tr>
					<tr class="action__wait_installer">
						<td class="action"><label><?=lang('wait_installer')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg">
							<span class="loading"><?=lang('wait_installer_loading')?></span>
							<span class="attempts_wrapper hidden"> -- <?=lang('wait_attempts')?></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="update_ee hidden">
			<h2><?=lang('update_ee_log')?></h2>
			<table>
				<tbody></tbody>
			</table>
		</div>

		<div class="update_post hidden">
			<h2>
				<?=lang('update_ee_post')?>
				<a href="#" class="open_sql_queries"><span></span> - <?=lang('queries_executed')?></a>
			</h2>
			<table>
				<tbody>
					<tr class="action__copy_files">
						<td class="action"><label><?=lang('copy_ee_files')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg">
							<span class="label skipped hidden"><?=lang('skipped')?></span>
						    <div class="progress progress-striped active hidden" style="margin:0 0 5px 0">
								<div class="bar" style="width:0%;"></div>
					    	</div>
					    	<span class="loading"><?=lang('copy_files_loading')?></span>
					    	<span class="error"></span>
						</td>
					</tr>
					<tr class="action__update_modules">
						<td class="action"><label><?=lang('update_modules')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"><span class="loading"><?=lang('update_modules_loading')?></span></td>
					</tr>
					<tr class="action__cleanup">
						<td class="action"><label><?=lang('cleanup_installer')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"><span class="loading"><?=lang('cleanup_installer_loading')?></span></td>
					</tr>
				</tbody>
			</table>
		</div>

		<h2 class="upgrade_done" style="display:none">
			<?=lang('update_ee_done')?>
		</h2>

	</div>
</div> <!-- #rightsection -->

<br clear="left">

</div> <!-- #ubody -->

<?php endif;?>


<?php echo $this->view('footer'); ?>
