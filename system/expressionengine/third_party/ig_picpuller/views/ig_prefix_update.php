<h3>Update the prefix for this application's returned values for <em><?=$site_label;?></em>.</h3>

<p>Pic Puller for Instagram's returned data attempts to mirror the data returned directly from Instagram. This can cause a problem in some cases because in some cases those names will conflict with the names of other pieces of your ExpressionEngine application. The default prefix is <em><strong>ig_</strong></em>.</p>

<p>To allow you the flexibility to work around these potential conflicts, you can add or change the prefix of tags returned by your Pic Puller application.</p>

<p>For example, if you are using the default prefix, you would have <strong>{ig_status}</strong> returned in your Pic Puller loop instead of <strong>{status}</strong>.</p>


<?php

if ($ig_picpuller_prefix === ''){
  $ig_picpuller_prefix = '<em>No prefix set</em>';
}
?>
<table border="1" cellspacing="2" cellpadding="5" width='100%'>
	<tr ><th colspan="2">Enter the prefix to use for all of this app's returned values.</th></tr>
	<!-- <tr><td>Data</td><td>Data</td></tr> -->



<?=form_open($update_secret_url, '', $form_hidden);?>
<tr><td><label>Instagram Client ID</label></td><td><?=$client_id;?></td></tr>
<?php $data = array(
              'name'        => 'ig_picpuller_prefix',
              'id'          => 'ig_picpuller_prefix',
              'value'       => '',
              'maxlength'   => '128',
              'size'        => '30',
              'style'       => 'width:98%',
            );
echo "<tr><td>".form_label('Prefix for returned Pic Puller tags ', 'ig_picpuller_prefix')."</td><td>".form_input($data)."</tr>";
//echo form_input($data);
?>

<?php echo "<tr><td colspan='2'>".form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))."</td></tr>"; ?>

<?=form_close();?>
</table>

<br>

<p>Note: Your current <em>prefix</em> is set to: <em><strong><?=$ig_picpuller_prefix;?></strong></em>. To keep this unchanged, <strong><a href="<?=$cancel_url;?>">CANCEL</a></strong> and return to the <em><?=$ig_info_name;?></em> menu.</p>
