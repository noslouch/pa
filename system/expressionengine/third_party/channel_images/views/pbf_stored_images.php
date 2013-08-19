<div id="CI_StoredImages">
	<div class="topbar">
		<h3><?=lang('ci:search_images')?></h3>
		<a href="javascript:$.fancybox.close()" class="close"><?=lang('close')?></a>
	</div>
	<div class="search">
		<?=lang('ci:find_entry')?> <input type="text" class="text"> <button><?=lang('ci:get_images')?></button>
		<input type="hidden" class="entry_id" value="">
	</div>
	<p class="loadingimages"><?=lang('ci:loading_images')?></p>
	<div class="result clearfix"></div>
</div>