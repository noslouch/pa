<?php
$current_type = '';
	foreach ($source_list as $data)
	{
		echo '<div class="assets-sync-item sync_' . $data->type .'_' . $data->id . '" ><label for="' . $data->type .'_' . $data->id . '">' . $data->name . (isset($data->site_id) && $data->site_id != 1 ? ' [' . $data->site_id . ']' : '' ). '</label> <input type="checkbox" class="indexing" id="' . $data->type .'_' . $data->id .'" /></div>';
	}
?>
<br />
<input type="submit" class="submit assets-index" value="<?php echo lang('update_indexes') ?>" />
<div id="assets-dialog">
	<div id="index-message"></div>
	<div id="index-status-report"></div>
</div>
