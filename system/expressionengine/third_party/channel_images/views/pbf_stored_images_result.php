<?php foreach($images as $img):?>
	<?php $ID = $img->image_id;
	$img->image_id = 0;?>

	<div class="Image">
		<a href='#' class='ImgUrl' title="<?=$img->title?>" rel="<?=$img->filename?>" id="<?=$ID?>" data="<?=$img->entry_id?>"><img src='<?=$img->small_img_url?>' width='<?=$this->config->item('ci_image_preview_size')?>'/></a>
		<div class="hidden"><?php echo base64_encode($this->view('pbf_field_single_image', $img, TRUE)); ?></div>
	</div>
<?php endforeach;?>

<?php if (empty($images) == TRUE):?> <p><?=lang('ci:no_images_found')?> <?php endif;?>

