<div class="CI_FIELDS">
	<h3><?=lang('ci:ci_fields')?></h3>
	<?php foreach($fields as $field_id => $field_label):?>
	<p>(ID:<?=$field_id?>) <strong><?=$field_label?></strong> - <a href="#" class="ci_grab_images" rel="<?=$field_id?>"><?=lang('ci:grab_images')?></a></p>
	<?php endforeach;?>
</div>

<div class="CI_IMAGES"></div>

<br clear="left">