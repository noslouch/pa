<?php if ( ! defined('SERVER_WIZ')) exit('No direct script access allowed');?>

		<p>The information on this page will tell you if your web host has everything 
		you need to run ExpressionEngine. If all the "required" features are marked with a "Yes" 
		then you can run ExpressionEngine. In addition, we recommend that all the 
		"suggested" features be present as well in order to use ExpressionEngine to 
		its full potential.</p>


		<table border="0" cellspacing="0" cellpadding="0" style="width:100%;" class="tableBorder">
		<thead>
			<tr>
				<th>Requirement</th>
				<th>Importance</th>
				<th>Supported</th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 0; foreach ($requirements as $required): ?>
			<tr class="<?php echo ($i++ % 2) ? 'even' : '';?>">
				<td><strong><?php echo $required['item'];?></strong></td>
				<td><span class="<?php echo $required['severity'];?>"><?php echo $required['severity'];?></span></td>
				<td class="supported_<?php echo $required['supported'];?>"><?php echo ($required['supported'] == 'y') ? 'Yes' : 'No';?></td>
			</tr>
		<?php endforeach;?>
		</tbody>
		</table>

<?php if (count($errors) > 0):?>
<h3 class="important">Critical Errors</h3>
<div class="important">To complete the wizard check, the following error(s) must be addressed:</div>
<ul>
	<li><?php echo implode("</li>\n\t<li>", $errors);?></li>
</ul>
<?php else:?>
<h3>Congratulations!  Your server is ready to use ExpressionEngine!</h3>
<?php endif;?>