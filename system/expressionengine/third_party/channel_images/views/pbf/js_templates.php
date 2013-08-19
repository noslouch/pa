<script type="text/javascript">
ChannelImages.LANG = <?=$langjson?>;
</script>

<script id="ChannelImagesSingleField" type="text/x-jquery-tmpl">
{{#table_view}}
<tr class="Image image-table {{#is_cover}}PrimaryImage{{/is_cover}}">
	{{#show_row_num}}<td class="num"></td>{{/show_row_num}}
	{{#show_id}}<td>{{{image_id}}}</td>{{/show_id}}
	{{#show_image}}<td>
		<a href='{{{big_img_url}}}' class='ImgUrl' rel='ChannelImagesGal' title='{{{image_title}}}'>
			<img src="{{{small_img_url}}}" width='<?=$this->config->item('ci_image_preview_size')?>' alt='{{{image_title}}}'>
		</a></td>
	{{/show_image}}
	{{#show_filename}}<td>{{{filename}}}</td>{{/show_filename}}
	{{#show_title}}<td data-field="title"><input type="text" value="{{image_title}}" class="image_title"></td>{{/show_title}}
	{{#show_url_title}}<td data-field="url_title"><textarea>{{{image_url_title}}}</textarea></td>{{/show_url_title}}
	{{#show_desc}}<td data-field="description"><textarea>{{{description}}}</textarea></td>{{/show_desc}}
	{{#show_category}}<td data-field="category">{{{category}}}</td>{{/show_category}}
	{{#show_cifield_1}}<td data-field="cifield_1"><textarea>{{cifield_1}}</textarea></td>{{/show_cifield_1}}
	{{#show_cifield_2}}<td data-field="cifield_2"><textarea>{{cifield_2}}</textarea></td>{{/show_cifield_2}}
	{{#show_cifield_3}}<td data-field="cifield_3"><textarea>{{cifield_3}}</textarea></td>{{/show_cifield_3}}
	{{#show_cifield_4}}<td data-field="cifield_4"><textarea>{{cifield_4}}</textarea></td>{{/show_cifield_4}}
	{{#show_cifield_5}}<td data-field="cifield_5"><textarea>{{cifield_5}}</textarea></td>{{/show_cifield_5}}
	<td>
		{{#show_image_action}}{{^is_linked}}<a href='#' class='gIcon ImageProcessAction' title='<?=lang('ci:actions:process_action')?>' ></a>{{/is_linked}}{{/show_image_action}}
		<a href='javascript:void(0)' class='gIcon ImageMove' title='<?=lang('ci:actions:move')?>'></a>
		{{#show_cover}}<a href='#' class='gIcon {{#is_cover}}StarIcon ImageCover{{/is_cover}} {{^is_cover}}ImageCover{{/is_cover}}' title='<?=lang('ci:actions:cover')?>'></a>{{/show_cover}}
		{{#show_image_edit}}<a href='#' class='gIcon ImageEdit' title='<?=lang('ci:actions:edit')?>'></a>{{/show_image_edit}}
		{{#show_image_replace}}<a href='#' class='gIcon ImageReplace' title='<?=lang('ci:actions:replace')?>'></a>{{/show_image_replace}}
		<a href="#" {{#is_linked}}class="gIcon ImageDel ImageLinked" title="<?=lang('ci:actions:unlink')?>"{{/is_linked}} {{^is_linked}}class="gIcon ImageDel" title="<?=lang('ci:actions:del')?>"{{/is_linked}}></a>
		<textarea name="{{{field_name}}}[images][][data]" class="ImageData hidden">{{{json_data}}}</textarea>
	</td>
</tr>
{{/table_view}}

{{#tile_view}}
<li class="Image image-tile {{#is_cover}}PrimaryImage{{/is_cover}}">
	<a href='{{{big_img_url}}}' class='ImgUrl' rel='ChannelImagesGal' title='{{{image_title}}}'>
		<img src="{{{small_img_url}}}" width='<?=$this->config->item('ci_image_preview_size')?>' alt='{{{image_title}}}'>
	</a>
	<div class="filename">
		<div class="name" data-field="title"><input type="text" value="{{image_title}}" class="image_title"></div>
	</div>
	<div class="actions">
		{{#show_cover}}<span class="abtn btn-star {{#is_cover}}StarIcon ImageCover{{/is_cover}} {{^is_cover}}ImageCover{{/is_cover}}" title='<?=lang('ci:actions:cover')?>'></span>{{/show_cover}}
		<span {{#is_linked}}class="abtn ImageDel ImageLinked" title="<?=lang('ci:actions:unlink')?>"{{/is_linked}} {{^is_linked}}class="abtn btn-delete ImageDel" title="<?=lang('ci:actions:del')?>"{{/is_linked}}></span>
		{{#show_image_edit}}<span class="abtn btn-edit ImageEdit" title='<?=lang('ci:actions:edit')?>'></span>{{/show_image_edit}}
		{{#show_image_replace}}<span class="abtn btn-replace ImageReplace" title='<?=lang('ci:actions:replace')?>'></span>{{/show_image_replace}}
	</div>

	<textarea name="{{{field_name}}}[images][][data]" class="ImageData hidden">{{{json_data}}}</textarea>
</li>
{{/tile_view}}
</script>

