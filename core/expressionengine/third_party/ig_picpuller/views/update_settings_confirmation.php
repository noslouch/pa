<h3>Success!</h3>
<p>Your Instagram application information has now been updated.</p>

<?php

if ($frontend_auth_url === '' ){
	$frontend_auth_url = '<em>No value set</em>';
}

if ($ig_picpuller_prefix === ''){
	$ig_picpuller_prefix = '<em>No prefix set.</em>';
}

?>

<p>Your Client ID:</p>
<p><pre>
	<?=$client_id;?>
</pre></p>
<p>Your Client Secret:</p>
<p><pre>
	<?=$client_secret;?>
</pre></p>
<p>Your Front-end Authorization URL:</p>
<p><pre>
	<?=$frontend_auth_url;?>
</pre></p>
<p>Your prefix for this application's returned values:</p>
<p><pre>
	<?=$ig_picpuller_prefix;?>
</pre></p>
<br>
<p><strong><a href="<?=$cancel_url;?>">Return to the <em><?=$ig_info_name;?></em> menu</a></strong>.</p>