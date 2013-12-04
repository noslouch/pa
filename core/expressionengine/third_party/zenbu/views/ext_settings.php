<?=form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=zenbu');?>

<table class="mainTable" cellpadding="0" cellspacing="0" border="0">
<tr>
	<th><?=lang('option')?></th>
	<th></th>
</tr>
<tr>
	<td width="20%">
		<?=lang('license')?>&nbsp;
	</td>
	<td>
		<?=$settings['license']?>
	</td>
</tr>
</table>
<p>
	<?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))?>
</p>
<?=form_close()?>