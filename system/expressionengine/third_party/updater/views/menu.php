<!--[if IE]> <div id="cimcp" class="ci-ie"> <![endif]-->
<!--[if !IE]><!--> <div id="cimcp"><!--<![endif]-->

<div id="umenu">
	<ul>
		<li class="<?=(($section == 'addons')) ? ' current': ''?>"><a class="addons" href="<?=$base_url?>&method=addons"><?=lang('addons')?></a></li>
		<li class="<?=(($section == 'ee_install')) ? ' current': ''?>"><a class="ee_install" href="<?=$base_url?>&method=ee_install"><?=lang('ee_install')?></a></li>
		<li class="<?=(($section == 'settings')) ? ' current': ''?>"><a class="settings" href="<?=$base_url?>&method=settings"><?=lang('settings')?></a></li>
	</ul>
</div>

<script type= "text/javascript">
Updater.queries = [];
Updater.key = '<?=$this->localize->now?>';
Updater.action_url = '<?=$action_url?>';
Updater.action_url_cp = '<?=$action_url_cp?>';

Updater.States = {};
Updater.States.waiting = '<span class="label"><?=lang('waiting')?></span>';
Updater.States.working = '<span class="label label-info"><?=lang('working')?></span>';
Updater.States.passed = '<span class="label label-success"><?=lang('passed')?></span>';
Updater.States.done = '<span class="label label-success"><?=lang('done')?></span>';
Updater.States.not_passed = '<span class="label label-important"><?=lang('not_passed')?></span>';
Updater.States.skipped = '<span class="label"><?=lang('skipped')?></span>';
Updater.States.failed = '<span class="label label-important"><?=lang('failed')?></span>';
Updater.States.forced = '<span class="label label-warning"><?=lang('forced')?></span>';
Updater.ee_loading = '<span class="loading"><?=lang('update_ee_loading')?></span>';
Updater.show_error_html = '<span class="label label-inverse show_error"><?=lang('show_error')?></span>';
Updater.retry_lang = '<span class="label retry"><?=lang('retry')?></span>';
</script>

<?php if ( isset($settings_done) === TRUE && $settings_done === FALSE):?>
<div class="alert alert-block" style="margin:0px;padding:10px;"><h4><?=lang('warning')?></h4> <?=lang('error:no_settings')?></div>
<?php endif;?>


<div id="test_ajax_error" class="alert alert-error alert-block" style="margin:0px;padding:10px;display:none">
	<h4><?=lang('warning')?></h4>
	<?=str_replace('{url}', $action_url, lang('error:test_ajax_failed'));?>
</div>
