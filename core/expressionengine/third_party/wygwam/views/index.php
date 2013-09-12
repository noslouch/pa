<?php

echo form_open($base.AMP.'method=save_settings');

$this->table->set_template($cp_table_template);
$this->table->set_heading(array(array('style' => 'width: 50%', 'data' => lang('preference')), lang('setting')));


$this->table->add_row(
	lang('wygwam_license_key', 'license_key'),
	form_input('license_key', $license_key, 'id="license_key" style="width: 98%"')
);


$file_browser_options = array(
	'ee'       => 'EE File Manager',
	'ckfinder' => 'CKFinder'
);

// is Assets installed?
if (Wygwam_helper::is_assets_installed())
{
	$file_browser_options['assets'] = 'Assets';
}

$this->table->add_row(
	lang('wygwam_file_browser', 'file_browser') . '<br />'.lang('wygwam_file_browser_desc'),
	form_dropdown('file_browser', $file_browser_options, $file_browser, 'id="file_browser"')
);


echo $this->table->generate();

echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));
echo form_close();

?>
