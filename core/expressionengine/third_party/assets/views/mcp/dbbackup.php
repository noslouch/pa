<div id="assets-dbbackup" class="assets-pane">
	<h3><?php echo lang('backup_db') ?></h3>
	<p><?php echo lang('backup_db_desc') ?></p>
	<div class="assets-buttons"><a id="assets-goforth" class="submit" href=""><?php echo lang('update_assets') ?></a></div>
</div>

<script type="text/javascript">
(function($){

	var $btn = $('#assets-goforth'),
		loading = false;

	$btn.click(function(event)
	{
		event.preventDefault();
		if (loading) return;

		var $disabled = $('<span class="disabled-btn"><?php echo lang('updating') ?></span>');
		$btn.replaceWith($disabled);
		loading = true;
		setTimeout(function() {
			window.location.href += '&goforth=y';
		}, 1);
	});

})(jQuery);
</script>
