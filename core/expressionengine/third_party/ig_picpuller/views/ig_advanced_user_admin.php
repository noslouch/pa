<h3><?=$ig_advanced_menu;?> ► <?=$ig_adv_user_auth;?> for <em><?=$site_label;?></em></h3>

<?php 
	if ( $app_id != '' ) {
?>

<p>This method of authorization is not the recommended proceedure for user authorization your Instagram application with Pic Puller. It's here as a fall back method for servers that fail in oAuth process. This method is only available to SuperAdmin level users. It is incompabilite with Pic Puller's front-end authorization process.</p>

<p>It <em>does not</em> solve the issue that caused the normal authorization process to fail.</p>

<h4>Instructions:</h4>

<p>1. In a new window, paste in the following URL:</p>

<p><input type="text" value="<?=$alt_url;?>" class="oauthcode" style='width: 100%;'></p>
	
<p>2. If the alternate process is working, you will be presented with 2 pieces of information from Instagram, a <strong>USER ID</strong> and an <strong>oAuth code</strong>. You will need to cut and paste each piece of information into the appropriate fields below and <em>submit</em> them to save them in the database for <em><?=$site_label;?></em>.</p>

<table border="1" cellspacing="2" cellpadding="5" width='75%'>
	<tr ><th colspan="2">Enter new authorization information for this user.</th></tr>
	<!-- <tr><td>Data</td><td>Data</td></tr> -->

<?=form_open($update_user_info_url, '', $form_hidden);?>
<?php $data = array(
			'name'        => 'ig_user_id',
			'id'          => 'ig_user_id',
			'value'       =>  $ig_user_id,
			'maxlength'   => '256',
			'size'        => '30',
			'style'       => 'width:98%',
			);
echo "<tr><td>".form_label('Instagram User ID', 'ig_user_oauth')."</td><td>".form_input($data)."</tr>";
?>



<?php $data = array(
			'name'        => 'ig_user_oauth',
			'id'          => 'ig_user_oauth',
			'value'       => $ig_user_oauth,
			'maxlength'   => '256',
			'size'        => '30',
			'style'       => 'width:98%',
			);
echo "<tr><td>".form_label('Instagram oAuth', 'ig_user_oauth')."</td><td>".form_input($data)."</tr>";
//echo form_input($data);
?>

<?php echo "<tr><td colspan='2'>".form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))."</td></tr>"; ?>

<?=form_close();?>
</table>

<br>

<?php 
	if ($ig_user_oauth == '') {
		$ig_user_oauth = '<em>(no value set)</em>';
	};
?>

<p>Note: Currently the oAuth for this logged in user is set to <em><strong><?=$ig_user_oauth;?></strong></em>. To keep this unchanged, <strong><a href="<?=$cancel_url;?>">CANCEL</a></strong> and return to the <em><?=$ig_info_name;?></em> menu.</p>

<?php 

} else {
?>
<p><strong>NOTE: </strong>You must <a href="/<?=$setup_link;?>">define an App</a> before authorizing a user.</p>

<?php
}
?>