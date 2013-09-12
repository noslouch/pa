<h3>Delete Instagram Application for <em><?=$site_label;?></em></h3>

<?php 
if (isset($client_id) ) {
?>

<p>Are you sure? Deleting the Instagram application from your ExpressionEngine system will remove all the users and their authorization for <em><?=$site_label;?></em>.</p>

<p>This does not uninstall <em><?=$moduleShortTitle?></em> from ExpressionEngine.</p>

<p>You may reinstall your Instagram application, but your users will need to re-authorize your application in the <em><?=$moduleShortTitle?></em> moduel control panel to access their images.</p>

<p>To keep your Instagram application installed, you may <strong><a href="<?=$cancel_url;?>">CANCEL this process</a></strong> and return to the <em><?=$ig_info_name;?></em> menu.</p>

<br>
<p><a href="<?=$delete_method;?>" class='submit'>YES, Remove this application</a></p>



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
