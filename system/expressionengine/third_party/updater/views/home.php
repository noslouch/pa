<?=$this->view('_topmenu')?>
<div id="updater-home">

<?php if ($this->session->userdata['group_id'] != 1):?>
<p style="padding:20px 20px 0"><strong><?=lang('u:only_super_admins')?></strong></p>
<?php else:?>

<?php if ( isset($settings_done) === TRUE && $settings_done === FALSE):?>
<div class="alert alert-block" style="margin:0px;padding:10px;">
    <h4><?=lang('u:warning')?></h4>
    <?=lang('error:no_settings')?>
</div>
<?php endif;?>

<div class="utable" style="width:50%;float:left">
<h2>
	<?=lang('u:ee_and_addons')?>
	<button class="submit submit-right js-test_settings"><?=lang('u:test_settings')?></button>
</h2>
<table>
	<tbody>
		<tr>
			<td style="width:150px"><label><?=lang('u:current_ver')?></label></td>
			<td style="width:150px">v<?=APP_VER?> - Build: <?=APP_BUILD?></td>
			<td rowspan="99">
				<div class="dropregion">
					<div class="inner">
						<p class="drop"><?=lang('u:drag_drop')?></p>
						<p class="or"><?=lang('u:or')?></p>
						<p class="upload" id="updater_upload">
							<?=lang('u:select_files')?>
							<em id="update_upload_placeholder"></em>
						</p>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<td><label><?=lang('u:upload_max_size')?></label></td>
			<td>
				<?=$upload_max_filesize?>
				(POST: <?=$post_max_size?>)
			</td>
		</tr>
		<tr>
			<td><label><?=lang('u:backup_db')?></label></td>
			<td class="backup_db">
				<input type="radio" name="backup_db" value="yes" class="js-yes"> <?=lang('yes')?>&nbsp;&nbsp;
				<input type="radio" name="backup_db" value="no" checked> <?=lang('no')?>
			</td>
		</tr>
		<tr>
			<td><label><?=lang('u:backup_files')?></label></td>
			<td class="backup_files">
				<input type="radio" name="backup_files" value="yes" class="js-yes"> <?=lang('yes')?>&nbsp;&nbsp;
				<input type="radio" name="backup_files" value="no" checked> <?=lang('no')?>
			</td>
		</tr>
	</tbody>
</table>

</div>


<div class="utable" style="width:49%;float:right">
<h2><?=lang('u:upload_status')?></h2>
<table>
	<tbody id="upload_queue">
		<tr>
			<td colspan="99" class="js-no_files">
				<?=lang('u:no_files_up')?>
			</td>
		</tr>
	</tbody>
</table>

</div>

<br clear="all">

<div class="utable">
<h2><?=lang('u:actions')?></h2>
<table>
	<tbody id="actions_queue">
		<tr>
			<td colspan="99" class="js-no_actions">
				<?=lang('u:actions_none')?>
			</td>
		</tr>
	</tbody>
</table>
<h2>
	<button class="submit start_action"><?=lang('u:actions_start')?></button>

	<a href="#" class="queries_exec"><span class="total"></span> <?=lang('u:queries_executed')?></a>
</h2>
</div>

<br clear="all">

<?php endif;?>
</div> <!--#updater-home -->
<?=$this->view('_footer')?>
