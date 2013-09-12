<?php echo $this->view('mcp_header'); ?>

<div class="dbody" style="padding:20px;">

<?=form_open($base_url_short.AMP.'method=update_settings')?>
<table class="mainTable">
	<thead>
		<tr>
			<th width="40%"><?=lang('tagger:question')?></th>
			<th width="60%"><?=lang('tagger:answer')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><strong><?=lang('tagger:urlsafe_seperator')?></strong></td>
			<td><?=form_dropdown('urlsafe_seperator', array('plus'=>lang('tagger:plus'), 'space'=>lang('tagger:space'), 'dash'=>lang('tagger:dash'), 'underscore'=>lang('tagger:underscore') ), $urlsafe_seperator);?></td>
		</tr>
		<tr>
			<td><strong><?=lang('tagger:lowercase_tags')?></strong></td>
			<td><?=form_dropdown('lowercase_tags', array('yes'=>lang('tagger:yes'), 'no'=>lang('tagger:no')), $lowercase_tags);?></td>
		</tr>
	</tbody>
</table>

<input name="submit" class="submit" type="submit" value="Save"/>

<?=form_close()?>

</div> <!-- dbody -->
