<?php $this->load->view('errors'); ?>
<?php 

$tmpl = array (
	'table_open'          => '<table class="mainTable" border="0" cellspacing="0" cellpadding="0">',

	'row_start'           => '<tr class="even">',
	'row_end'             => '</tr>',
	'cell_start'          => '<td style="width:50%;">',
	'cell_end'            => '</td>',

	'row_alt_start'       => '<tr class="odd">',
	'row_alt_end'         => '</tr>',
	'cell_alt_start'      => '<td>',
	'cell_alt_end'        => '</td>',

	'table_close'         => '</table>'
);

$this->table->set_template($tmpl); 
$this->table->set_empty("&nbsp;");
?>
<div class="clear_left shun"></div>

<?php echo form_open($query_base.'settings', array('id'=>'my_accordion'))?>
<input type="hidden" value="yes" name="go_settings" />
<h3  class="accordion"><?=lang('configure_cp_export')?></h3>
<div>
	<?php 
	$this->table->set_heading(lang('settings'),' ');
	$this->table->add_row('<label for="members_list_limit">'.lang('members_list_limit').'</label><div class="subtext">'.lang('members_list_limit_instructions').'</div>', form_input('members_list_limit', $settings['members_list_limit'], 'id="members_list_limit"'. $settings_disable));
	$this->table->add_row('<label for="channel_entries_list_limit">'.lang('channel_entries_list_limit').'</label><div class="subtext">'.lang('channel_entries_list_limit_instructions').'</div>', form_input('channel_entries_list_limit', $settings['channel_entries_list_limit'], 'id="channel_entries_list_limit"'. $settings_disable));
	$this->table->add_row('<label for="comments_list_limit">'.lang('comments_list_limit').'</label><div class="subtext">'.lang('comments_list_limit_instructions').'</div>', form_input('comments_list_limit', $settings['comments_list_limit'], 'id="comments_list_limit"'. $settings_disable));
	$this->table->add_row('<label for="mailing_list_limit">'.lang('mailing_list_limit').'</label><div class="subtext">'.lang('mailing_list_limit_instructions').'</div>', form_input('mailing_list_limit', $settings['mailing_list_limit'], 'id="mailing_list_limit"'. $settings_disable));
	
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<h3  class="accordion"><?=lang('configure_api')?></h3>
<div>
	<?php 
	$this->table->set_heading(lang('settings'),' ');
	$this->table->add_row('<label for="enable_api">'.lang('enable_api').'</label><div class="subtext">'.lang('enable_api_instructions').'</div>', form_checkbox('enable_api', '1', $settings['enable_api'], 'id="enable_api"'. $settings_disable));
	$this->table->add_row('<label for="api_key">'.lang('api_key').'</label><div class="subtext">'.lang('api_key_instructions').'</div>', form_input('api_key', $settings['api_key'], 'id="api_key"'. $settings_disable));
	$this->table->add_row('<label for="api_url">'.lang('api_url').'</label><div class="subtext">'.lang('api_url_instructions').'</div>', $api_url);
	
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<h3  class="accordion"><?=lang('global_config')?></h3>
<div>
	<?php 
	$this->table->set_heading(lang('settings'),' ');
	$this->table->add_row('<label for="export_it_date_format">'.lang('export_it_date_format').'</label><div class="subtext">'.lang('export_it_date_format_instructions').'</div>', form_input('export_it_date_format', $settings['export_it_date_format'], 'id="export_it_date_format"'. $settings_disable));
	
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<h3  class="accordion"><?=lang('license_number')?></h3>
<div>
	<?php 
	$this->table->set_heading(lang('settings'),' ');
	$this->table->add_row('<label for="license_number">'.lang('license_number').'</label>', form_input('license_number', $settings['license_number'], 'id="license_number"'. $settings_disable));	
	
	echo $this->table->generate();
	$this->table->clear();	
	?>
</div>

<br />
<div class="tableFooter">
	<div class="tableSubmit">
		<?php echo form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'));?>
	</div>
</div>	
<?php echo form_close()?>