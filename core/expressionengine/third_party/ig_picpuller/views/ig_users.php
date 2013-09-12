<h3>Authorized Users of the Instagram Application for <em><?=$site_label;?></em></h3>
<style type="text/css">
	.highlight {
		background-color: green;
	}

</style>

<?php

if (count($member_ids) > 0) {
	if (count($member_ids) ==  1 ) {
		echo "<p>There is one user in total. </p>";
	} else {
		echo "<p>There are ".count($member_ids)." users in total. </p>";
	}
?>
<table border="1" cellspacing="2" cellpadding="5" width='100%'>
	<tr><th>Expression Engine Member ID</th><th>Expression Engine Screen Name</th><th>Instagram oAuth</th></tr>
	<?php
	for ($i = 0; $i < count($member_ids); $i++)
		echo "<tr><td>$member_ids[$i]</td><td>$screen_names[$i]</td><td>$oauths[$i]</td></tr>"
	?>
<?php
	} 
	else 
	{
		
		if ($appexists) {
			echo "<p>There are no authorized users for $moduleTitle.</p>";
		} 
		else 
		{
				echo "<p>There is no Instagram application in the database for <em>$site_label</em>.</p>";
		}
	}
?>
</table>