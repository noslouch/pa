<?php echo form_open($base.AMP.'method=config_save', '', array('config_id' => $config['config_id'])) ?>

<?php

$this->table->set_template($cp_table_template);

$this->table->set_heading(array(
	'colspan' => '2',
	'data' => lang('wygwam_config_settings')
));

// -------------------------------------------
//  Configuration Name
// -------------------------------------------

$this->table->add_row(
	array(
		'style' => 'width: 40%;',
		'data' => lang('wygwam_config_name', 'config_name')
	),
	array(
		'style' => 'padding-right: 13px',
		'data' => form_input('config_name', $config['config_name'], 'id="config_name"')
	)
);

// -------------------------------------------
//  Toolbar
// -------------------------------------------

$tb_helper_vars = array(
	'tb_groups'          => Wygwam_helper::tb_groups(),
	'tb_combos'          => Wygwam_helper::tb_combos(),
	'tb_label_overrides' => Wygwam_helper::tb_label_overrides()
);

$selections_vars = array(
	'vars'            => $tb_helper_vars,
	'id'              => 'selections',
	'groups'          => Wygwam_helper::create_toolbar($config['settings']['toolbar'], TRUE),
	'selected_groups' => array(),
	'selections_pane' => TRUE
);

$options_vars = array(
	'vars'            => $tb_helper_vars,
	'id'              => 'options',
	'groups'          => Wygwam_helper::tb_groups(),
	'selected_groups' => $config['settings']['toolbar'],
	'selections_pane' => FALSE
);

$tb_html = form_hidden('settings[toolbar]', 'n')
         . '<div id="tb-label">'
         .   lang('wygwam_toolbar', 'toolbar')
         .   '<table align="middle"><tr>'
         .     '<td id="tbhelp1">'.lang('wygwam_toolbar_help1').'</td>'
         .     '<td id="tbhelp2">'.lang('wygwam_toolbar_help2').'</td>'
         .   '</tr></table>'
         . '</div>'
         . $this->load->view('config_edit_toolbar', $selections_vars, TRUE)
         . $this->load->view('config_edit_toolbar', $options_vars, TRUE);

$this->table->add_row(array('id' => 'toolbar', 'colspan' => '2', 'data' => $tb_html));

// -------------------------------------------
//  Height
// -------------------------------------------

$this->table->add_row(
	lang('wygwam_height', 'height'),
	form_input('settings[height]', $config['settings']['height'], 'id="height" style="width: 3em;"')
	    . form_hidden('settings[resize_enabled]', 'n') . NBS.NBS.NBS
		. '<label>'.form_checkbox('settings[resize_enabled]', 'y', ($config['settings']['resize_enabled'] == 'y')).NBS.lang('wygwam_resizable').'</label>'
);

// -------------------------------------------
//  CSS File
// -------------------------------------------

$this->table->add_row(
	lang('wygwam_css_file', 'css') . '<br />' . lang('wygwam_css_desc'),
	array(
		'style' => 'padding-right: 13px',
		'data' =>  '<p>'.form_input(array('id'=>'css', 'name'=>'settings[contentsCss]', 'value'=>implode($config['settings']['contentsCss']))).'</p>' .
		           '<label>'.form_checkbox('settings[parse_css]', 'y', ($config['settings']['parse_css'] == 'y')).NBS.lang('wygwam_parse_css').'</label>' . NBS .
		           ' '.lang('wygwam_parse_css_alt')
	)
);

// -------------------------------------------
//  Upload Directory
// -------------------------------------------

$this->table->add_row(
	lang('wygwam_upload_dir', 'upload_dir'),
	$upload_dir
);

// -------------------------------------------
//  Allowed Content
// -------------------------------------------

$this->table->add_row(
	lang('wygwam_restrict_html', 'restrict_html') . '<br />' . lang('wygwam_restrict_html_desc'),
	form_dropdown('settings[restrict_html]', array('n' => lang('no'), 'y' => lang('yes')), $config['settings']['restrict_html'], 'id="restrict_html"')
);

?>

<div id="settings">
	<?php echo $this->table->generate(); ?>
</div>


<?php

// -------------------------------------------
//  Advanced Settings
// -------------------------------------------

$this->table->clear();

$this->table->set_heading(array(
	'colspan' => '3',
	'style' => 'width: 40%;',
	'data' => lang('wygwam_advanced')
));

?>

<div id="advanced">
	<?php echo $this->table->generate() ?>
</div>


<p><?php echo form_submit(array('name' => 'submit', 'value' => lang(($config['config_id'] ? 'update' : 'submit')), 'class' => 'submit')) ?></p>

<?php echo form_close() ?>
