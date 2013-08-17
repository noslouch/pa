<table class="mainTable">
	<thead>
		<tr><th colspan="2"><?=lang('ci:watermark:settings')?></th></tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:watermark:type')?></td>
			<td>
				<?php if (isset($type) == FALSE) $type = 'none';?>
				<input type="radio" value="none" class="ci_watermark_type" name="ci_watermark[type]" <?php if ($type == 'none') echo 'checked';?> /> &nbsp;<?=lang('ci:watermark:type:none')?> &nbsp;&nbsp;&nbsp;
				<input type="radio" value="text" class="ci_watermark_type" name="ci_watermark[type]" <?php if ($type == 'text') echo 'checked';?> /> &nbsp;<?=lang('ci:watermark:type:text')?> &nbsp;&nbsp;&nbsp;
				<input type="radio" value="image" class="ci_watermark_type" name="ci_watermark[type]" <?php if ($type == 'image') echo 'checked';?> /> &nbsp;<?=lang('ci:watermark:type:image')?>
			</td>
		</tr>
		<tr class="ci_watermark_general">
			<td><?=lang('ci:watermark:padding')?></td>
			<td>
				<input type="text" name="ci_watermark[padding]" value="<?php if (isset($padding) == FALSE) echo 0; else echo $padding;?>"  /> <br />
				<?=lang('ci:watermark:padding:exp')?>
			</td>
		</tr>
		<tr class="ci_watermark_general">
			<td><?=lang('ci:watermark:horalign')?></td>
			<td>
				<?php $temp = array('left' => lang('ci:watermark:left'), 'center' => lang('ci:watermark:center'), 'right' => lang('ci:watermark:right'));?>
				<?=form_dropdown('ci_watermark[horizontal_alignment]', $temp, ((isset($horizontal_alignment) == FALSE) ? 'center' : $horizontal_alignment) );?>
				<?=lang('ci:watermark:horalign:exp')?>
			</td>
		</tr>
		<tr class="ci_watermark_general">
			<td><?=lang('ci:watermark:vrtalign')?></td>
			<td>
				<?php $temp = array('top' => lang('ci:watermark:top'), 'middle' => lang('ci:watermark:middle'), 'bottom' => lang('ci:watermark:bottom'));?>
				<?=form_dropdown('ci_watermark[vertical_alignment]', $temp, ((isset($vertical_alignment) == FALSE) ? 'bottom' : $vertical_alignment) );?>
				<?=lang('ci:watermark:vrtalign:exp')?>
			</td>
		</tr>
		<tr class="ci_watermark_general">
			<td><?=lang('ci:watermark:horoffset')?></td>
			<td>
				<input type="text" name="ci_watermark[horizontal_offset]" value="<?php if (isset($horizontal_offset) == FALSE) echo 0; else echo $horizontal_offset;?>"  /> <br />
				<?=lang('ci:watermark:horoffset:exp')?>
			</td>
		</tr>
		<tr class="ci_watermark_general">
			<td><?=lang('ci:watermark:vrtoffset')?></td>
			<td>
				<input type="text" name="ci_watermark[vertical_offset]" value="<?php if (isset($vertical_offset) == FALSE) echo 0; else echo $vertical_offset;?>"  /> <br />
				<?=lang('ci:watermark:vrtoffset:exp')?>
			</td>
		</tr>
	</tbody>
</table>


<table class="mainTable ci_watermark_text">
	<thead>
		<tr><th colspan="2"><?=lang('ci:watermark:text_pref')?></th></tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:watermark:text')?></td>
			<td>
				<input type="text" name="ci_watermark[text]" value="<?php if (isset($text) == FALSE) echo 'DevDemon.Com (Channel Images)'; else echo $text;?>"  /> <br />
				<?=lang('ci:watermark:text:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:font_path')?></td>
			<td>
				<input type="text" name="ci_watermark[font_path]" value="<?php if (isset($font_path) == FALSE) echo ''; else echo $font_path;?>"  /> <br />
				<?=lang('ci:watermark:font_path:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:font_size')?></td>
			<td>
				<input type="text" name="ci_watermark[font_size]" value="<?php if (isset($font_size) == FALSE) echo '16'; else echo $font_size;?>"  /> <br />
				<?=lang('ci:watermark:font_size:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:font_color')?></td>
			<td>
				<input type="text" name="ci_watermark[font_color]" value="<?php if (isset($font_color) == FALSE) echo 'ffffff'; else echo $font_color;?>"  /> <br />
				<?=lang('ci:watermark:font_color:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:shadow_color')?></td>
			<td>
				<input type="text" name="ci_watermark[shadow_color]" value="<?php if (isset($shadow_color) == FALSE) echo ''; else echo $shadow_color;?>"  /> <br />
				<?=lang('ci:watermark:shadow_color:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:shadow_distance')?></td>
			<td>
				<input type="text" name="ci_watermark[shadow_distance]" value="<?php if (isset($shadow_distance) == FALSE) echo 3; else echo $shadow_distance;?>"  /> <br />
				<?=lang('ci:watermark:shadow_distance:exp')?>
			</td>
		</tr>
	</tbody>
</table>


<table class="mainTable ci_watermark_image">
	<thead>
		<tr><th colspan="2"><?=lang('ci:watermark:overlay_pref')?></th></tr>
	</thead>
	<tbody>
		<tr>
			<td><?=lang('ci:watermark:overlay_path')?></td>
			<td>
				<input type="text" name="ci_watermark[overlay_path]" value="<?php if (isset($overlay_path) == FALSE) echo ''; else echo $overlay_path;?>"  /> <br />
				<?=lang('ci:watermark:overlay_path:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:opacity')?></td>
			<td>
				<input type="text" name="ci_watermark[opacity]" value="<?php if (isset($opacity) == FALSE) echo 50; else echo $opacity;?>"  /> <br />
				<?=lang('ci:watermark:opacity:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:x_trans')?></td>
			<td>
				<input type="text" name="ci_watermark[x_transp]" value="<?php if (isset($x_transp) == FALSE) echo 4; else echo $x_transp;?>"  /> <br />
				<?=lang('ci:watermark:x_trans:exp')?>
			</td>
		</tr>
		<tr>
			<td><?=lang('ci:watermark:y_trans')?></td>
			<td>
				<input type="text" name="ci_watermark[y_transp]" value="<?php if (isset($y_transp) == FALSE) echo 4; else echo $y_transp;?>"  /> <br />
				<?=lang('ci:watermark:y_trans:exp')?>
			</td>
		</tr>
	</tbody>
</table>

<a href="#" class="CITestWaterMark ci_watermark_general"><?=lang('ci:watermark:test_wm')?></a>