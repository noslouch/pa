<h3>About all <?=$moduleShortTitle;?> Applications</h3>

<?php 
if (count($client_ids) > 0) {
?>

<style type="text/css">
	.highlight {
		background-color: #D91350;
		color: white;
	}
</style>

<p>Below are all of the Instagram applications that live within your ExpressionEngine site. If you have MSM installed, you may see multiple applications listed below.</p>
<p>You can only edit the Pic Puller Instagram application of the current active site in the ExpressionEngine control panel. You may do that from <a href="<?=$app_info_link;?>"><em><?=$ig_info_name;?></em></a>.</p>

<table border="1" cellspacing="2" cellpadding="5" width='100%'>
	<tr><th>EE site name</th><th>Instagram Client ID</th><th>Instagram Client Secret</th><th>Prefix for all tags</th></tr>
	<?php
	for ($i = 0; $i < count($client_ids); $i++) {
		// catch instances where there is no prefix set and tell user in an appropriate way.
		if ($ig_picpuller_prefixs[$i] === ''){
			$ig_picpuller_prefixs[$i] = '<em>No prefix set.</em>';
		}

	?>
	<tr ><td><?php if ($site_ids[$i] === $current_site_id) { echo"<strong>";} ?><?=$site_labels[$i];?><?php if ($site_ids[$i] === $current_site_id) { echo"</strong>";} ?><td><?=$client_ids[$i];?></td><td><?=$client_secrets[$i];?></td><td><?=$ig_picpuller_prefixs[$i];?></td></tr>
	<?php } ?>
</table>
<br>

<?php
}
else
{
// There is no app defined, so tell the user that.
?>
<p>There is currently no Instagram app defined within <?=$moduleShortTitle;?>.</p>	
<?php	
}


?>
