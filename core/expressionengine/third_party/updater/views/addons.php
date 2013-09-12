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
			<?=lang('install_update')?>
			<span class="submit" id="test_settings" style="font-size:12px;float:right;cursor:pointer;"><?=lang('test_settings')?></span>
		</h2>
		<table>
			<tbody>
				<tr>
					<td><label><?=lang('backup_db')?></label></td>
					<td class="backup_db">
						<input type="radio" name="backup_db" value="yes"> <?=lang('yes')?>&nbsp;&nbsp;
						<input type="radio" name="backup_db" value="no" checked> <?=lang('no')?>
					</td>
				</tr>
				<tr class="odd">
					<td><label><?=lang('backup_files')?></label></td>
					<td class="backup_files">
						<input type="radio" name="backup_files" value="yes"> <?=lang('yes')?>&nbsp;&nbsp;
						<input type="radio" name="backup_files" value="no" checked> <?=lang('no')?>
					</td>
				</tr>
			</tbody>
			<tbody id="uploadsec">
				<tr>
					<td><label><?=lang('addon_file')?></label></td>
					<td style="width:290px">
						<div class="filerow">
							<input type="file" name="file[]">
							<a href="#" class="add_file"></a>
						</div>
					</td>
				</tr>
				<tr>
					<td><label><?=lang('addon_file_url')?></label></td>
					<td style="width:290px">
						<div class="filerow">
							<input type="text" name="file_url[]">
							<a href="#" class="add_file"></a>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<h2><input type="submit" class="submit" id="process_addon" value="<?=lang('start')?>" <?php if ($disable_btn):?>disabled="disabled" style="background:#DDE2E5"<?php endif;?> ></h2>

		<input type="hidden" name="key" value="<?=$this->localize->now?>" class='temp_key'>
	<?=form_close();?>

	</div>

	<div class="utable hidden" id="update_notes">
		<h2><?=lang('update_notes')?></h2>
		<table>
			<tbody>
			</tbody>
		</table>
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
					<tr class="action__addon_zip">
						<td class="action"><label><?=lang('addon_zip')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"></td>
					</tr>
					<tr class="action__addon_info">
						<td class="action"><label><?=lang('addon_info')?></label></td>
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

		<div class="addon_install_process hidden">
			<h2><?=lang('install_update_short')?></h2>
			<table>
				<tbody>
					<tr class="action__move_files">
						<td class="action"><label><?=lang('move_files')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg">
							<span class="loading"><?=lang('copy_files_loading')?></span>
						</td>
					</tr>
					<tr class="action__install_addon">
						<td class="action"><label><?=lang('install_update')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"><span class="loading"><?=lang('installing_addon')?></span></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="post_process hidden">
			<h2>
				<?=lang('post_process')?>
				<a href="#" class="open_sql_queries"><span></span> - <?=lang('queries_executed')?></a>
			</h2>
			<table>
				<tbody>
					<tr class="action__remove_temp_dirs">
						<td class="action"><label><?=lang('cleanup')?></label></td>
						<td class="state"><span class="label"><?=lang('waiting')?></span></td>
						<td class="msg"><span class="loading"><?=lang('cleaning_up')?></span></td>
					</tr>
				</tbody>
			</table>
		</div>

		<h2 class="upgrade_done" style="display:none">
			<?=lang('addon_process_done')?>
		</h2>

	</div>
</div> <!-- #rightsection -->

<br clear="left">


</div> <!-- #ubody -->


<?php endif;?>



<?php echo $this->view('footer'); ?>
