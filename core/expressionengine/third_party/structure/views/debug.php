<?php if ($duplicate_entries > 0):?>
<h2>You currently have <span style="color:#E7174B"><?=$duplicate_entries?></span> extraneous entries in the exp_structure table.</h2>
<br />
<?=form_open($action_url, $attributes)?>

	<?=form_submit(array('name' => 'submit', 'value' => 'Fix It!', 'class' => 'submit'))?>

<?=form_close()?>
<br />
<?php endif ?>

<?php if ($duplicate_lefts > 0 || $duplicate_rights > 0): ?>
<h2>You <?php if ($duplicate_entries > 0):?>also <?php endif ?>have Duplicate Left and/or Right values.</h2>

<h3>Duplicate Entries</h3>
<?php print_r($duplicate_entries) ?>

<h3>Duplicate Lefts</h3>
<?php print_r($duplicate_lefts) ?>

<h3>Duplicate Duplicate Rights</h3>
<?php print_r($duplicate_rights) ?>

<p>Head over to <a href="http://structure.tenderapp.com">Structure Support</a> and we'll help sort you out.</p>
<?php endif ?>

<?php if ($duplicate_lefts == 0 && $duplicate_rights == 0 && $duplicate_entries == 0): ?>
	<p>No duplicate entries or node positions. Good!</p>
<?php endif?>

<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=listing_site_id_fix')?>

	<?=form_submit(array('name' => 'submit', 'value' => 'Update MSM Listing Site IDs', 'class' => 'submit'))?>

<?=form_close()?>