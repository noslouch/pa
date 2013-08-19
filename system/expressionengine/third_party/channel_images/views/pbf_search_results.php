<div class="ImagesResultTabs">

	<a href="#" class="ClearImageSearch ClearImageSearch_Top"><?=lang('ci:clear_search')?></a>

	<ul>
	<?php foreach($images as $channel_id => $row):?>
		<li><a href="#CITAB_<?=$field_id?>_<?=$channel_id?>"><?=$channels[$channel_id]?> (<?=count($row)?>)</a></li>
	<?php endforeach;?>
	</ul>

	<?php foreach($images as $channel_id => $row):?>

	<div id="CITAB_<?=$field_id?>_<?=$channel_id?>">
		<table cellspacing="0" cellpadding="0" class="CITable"> <thead> <tr>
			<th width="30">&nbsp;</th>
			<th><span><?=lang('ci:image')?></span></th>
			<th><span><?=lang('ci:title')?></span></th>
			<th><span><?=lang('ci:desc')?></span></th>
			<th><span><?=lang('ci:category')?></span></th>
			<th><span><?=lang('ci:filename')?></span></th>
			</tr> </thead> <tbody>

			<?php foreach($row as $image):?>
				<?php $image->image_id_hidden = $image->image_id;
				$image->image_id = 0;?>
				<tr>
					<td>
						<a href="#" class="AddImage">&nbsp;</a>
						<span class='imagetd hidden'><?php echo base64_encode($this->view('pbf_field_single_image', $image, TRUE)); ?></span>
						<span class='imageinfo hidden'><?=$this->channel_images_helper->generate_json($image);?></span>
					</td>
					<td><a href='<?=$image->big_img_url?>' class='ImgUrl' rel='CISearchResult' title="<?=$image->title?>"><img src='<?=$image->small_img_url?>' width='<?=$this->config->item('ci_image_preview_size')?>'/></a></td>
					<td><?=$image->title?></td>
					<td><?=$image->description?></td>
					<td><?=$image->category?></td>
					<td><?=$image->filename?></td>
				</tr>
			<?php endforeach;?>


		</tbody></table>
	</div>

	<?php endforeach;?>

	<a href="#" class="ClearImageSearch"><?=lang('ci:clear_search')?></a>
</div>