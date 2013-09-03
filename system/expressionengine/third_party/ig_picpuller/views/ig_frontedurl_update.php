<h3>Update Instagram Client Front-end URL for <em><?=$site_label;?></em></h3>

<p>Enter in the Front-end URL for your application below.</p>

<p>This will be shown to all users attempting to authorize this app through the control panel when control panel authorization isn't possible due to mismatched domains for the control panel and the Instagram application.</p>
<?php

if ($frontend_auth_url === '' ){
	$frontend_auth_url = '<em>No value set</em>';
}
?>
<table border="1" cellspacing="2" cellpadding="5" width='100%'>
	<tr ><th colspan="2">Enter the Front-end authorization link to display for this app.</th></tr>
	<!-- <tr><td>Data</td><td>Data</td></tr> -->

<?=form_open($update_frontend_url, '', $form_hidden);?>
<tr><td><label>Instagram Client ID</label></td><td><?=$client_id;?></td></tr>
<?php $data = array(
              'name'        => 'frontend_auth_url',
              'id'          => 'frontend_auth_url',
              'value'       => '',
              'maxlength'   => '256',
              'size'        => '30',
              'style'       => 'width:98%',
            );
echo "<tr><td>".form_label('Front-end Authorization URL ', 'frontend_auth_url')."</td><td>".form_input($data)."</tr>";
//echo form_input($data);
?>

<?php echo "<tr><td colspan='2'>".form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))."</td></tr>"; ?>

<?=form_close();?>
</table>

<br>

<p>Note: Your current front-end authorization URL is <em><strong><?=$frontend_auth_url;?></strong></em>. To keep this unchanged, <strong><a href="<?=$cancel_url;?>">CANCEL</a></strong> and return to the <em><?=$ig_info_name;?></em> menu.</p>
