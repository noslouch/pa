<h3>About the Instagram Application for <em><?=$site_label;?></em></h3>

<?php 
if (isset($client_id) ) {

if ($frontend_auth_url === '' ){
	$frontend_auth_url = '<em>No value set for this app.</em>';
}

if ($ig_picpuller_prefix === ''){
	$ig_picpuller_prefix = '<em>No prefix set.</em>';
}


?>

<table border="1" cellspacing="2" cellpadding="5" width='100%'>
	<tr><th>Instagram Client ID</th><th>Instagram Client Secret</th><th>Front-end Authorization URL</th><th>Prefix for all tags</th></tr>
	<tr><td><?=$client_id;?></td><td><?=$client_secret;?>&nbsp;&nbsp;(<a href="<?=$edit_secret;?>" title='Edit Secret'>edit</a>)</td><td><?=$frontend_auth_url;?>&nbsp;&nbsp;(<a href="<?=$edit_frontend_url;?>" title='Edit Front-end URL'>edit</a>)</td><td><?=$ig_picpuller_prefix;?>&nbsp;&nbsp;(<a href="<?=$edit_prefix;?>" title='Edit Prefix URL'>edit</a>)</td></tr>
</table>
<br>
<p><a href="<?=$delete_method;?>" class='submit'>Remove this application for <em><?=$site_label;?></em> and all it's users.</a></p>

<?php
}
else
{
// There is no app defined, so tell the user that.
?>
<p>There is currently no Instagram app defined within <?=$moduleShortTitle;?> for <em><?=$site_label;?>.</p>	
<?php	
}


?>
