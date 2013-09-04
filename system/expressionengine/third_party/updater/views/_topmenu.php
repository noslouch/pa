<!--[if IE]> <div id="updater" class="updater-ie"> <![endif]-->
<!--[if !IE]><!--> <div id="updater"><!--<![endif]-->

<div id="umenu">
	<ul>
		<li class="<?=(($section == 'home')) ? ' current': ''?>"><a class="home" href="<?=$base_url?>&method=home"><?=lang('u:dashboard')?></a></li>
		<li class="<?=(($section == 'settings')) ? ' current': ''?>"><a class="settings" href="<?=$base_url?>&method=settings"><?=lang('settings')?></a></li>
        <li class="upd_version">v<?=UPDATER_VERSION?></li>
	</ul>
</div>

<div id="test_ajax_error" class="alert alert-error alert-block" style="margin:0px;padding:10px;display:none">
    <h4><?=lang('u:warning')?></h4>
    <?=lang('error:test_ajax_failed')?>
    <div class="error">
        <div class="inner"></div>

        <a href="#" class="js-show_error"><strong>Show Response</strong></a>
        <textarea style="display:none"></textarea>
    </div>
</div>

<!-- Yeah, people tend to forget the themes folder -->
<style type="text/css">
#updater_missing_themes {
    font-size:30px;
    font-weight:bold;
    padding:20px;
    text-align:center;
    background:red;
    color:#fff;
}
</style>
<h2 id="updater_missing_themes">Theme files are missing! The Add-on will not work.<br>Please fix the issue before using!</h2>


<div class="updater-body">
