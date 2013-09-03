<h3>Update Instagram Client Secret for <em><?=$site_label;?></em></h3>

<p>Enter in the new Client Secret for your application below.</p>

<table border="1" cellspacing="2" cellpadding="5" width='75%'>
	<tr ><th colspan="2">Enter the <em>new</em> client secret from your <a href="http://instagram.com/developer/clients/manage/" target="instagram">Instagram Application Management screen</a>.</th></tr>

<?=form_open($update_secret_url, '', $form_hidden);?>
<tr><td><label>Instagram Client ID</label></td><td><?=$client_id;?></td></tr>
<?php $data = array(
              'name'        => 'ig_client_secret',
              'id'          => 'ig_client_secret',
              'value'       => '',
              'maxlength'   => '64',
              'size'        => '30',
              'style'       => 'width:98%',
            );
echo "<tr><td>".form_label('Instagram Client Secret ', 'ig_client_secret')."</td><td>".form_input($data)."</tr>";
//echo form_input($data);
?>

<?php echo "<tr><td colspan='2'>".form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))."</td></tr>"; ?>

<?=form_close();?>
</table>

<br>

<p>Note: Your old secret was <em><strong><?=$client_secret;?></strong></em>. To keep this unchanged, <strong><a href="<?=$cancel_url;?>">CANCEL</a></strong> and return to the <em><?=$ig_info_name;?></em> menu.</p>
