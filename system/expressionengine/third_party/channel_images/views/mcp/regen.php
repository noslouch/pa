<?php echo $this->view('mcp/menu'); ?>

<div class="btitle" id="actionbar">
	<h2><?=lang('ci:regenerate_sizes')?></h2>
	<a href="#" class="abtn start" id="start_regen"><span><?=lang('ci:start_resize')?></span></a>
</div>


<div id="regen_fields">
	<span><strong><?=lang('ci:ci_fields')?></strong></span>
	<?php foreach($fields as $field_id => $field_name):?>
	<span class="label label-info" data-field="<?=$field_id?>"><?=$field_id?>: <?=$field_name?></span>
	<?php endforeach;?>
</div>
<br clear="all">

<div id="regen_images" style="padding:20px">
	<table cellpadding="0" cellspacing="0" border="0" class="dtable">
		<thead>
			<tr>
				<th>Image ID</th>
				<th><?=lang('ci:filename')?></th>
				<th><?=lang('ci:title')?></th>
				<th>Entry ID</th>
				<th><?=lang('ci:status')?></th>
			</tr>
		</thead>
		<tbody>
			<tr><td colspan="9"><?=lang('ci:select_regen_field')?></td></tr>
		</tbody>
	</table>
</div>

<script type="text/javascript">
ChannelImages.EntryFormURL = "<?=str_replace('&amp;', '&', $base_cp)?>admin.php?S=0&amp;D=cp&C=content_publish&M=entry_form";
</script>


<br clear="all">

<div id="error_log" style="padding:0 20px 20px;display:none">
	<div class="btitle"><h2><?=lang('error')?></h2></div>
	<div class="body" style="border:1px solid #ccc; padding:20px;"></div>
</div>
