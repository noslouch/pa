<?= lang('intro'); ?>

<?= form_open('C=addons_extensions'.AMP.'M=save_extension_settings'.AMP.'file=vl_entry_url'); ?>

<?php
	$this->table->set_template($cp_pad_table_template);
	$this->table->set_heading(lang('channel_heading'), lang('url_heading'));

	foreach ($channels as $key => $val) {
		$url_val = '';
		$channel_id = $val['channel_id'];

		if (isset($settings[$channel_id])) {
			$url_val = $settings[$channel_id]['url'];
		}

		$this->table->add_row($val['channel_title'], form_input('channel_' . $channel_id . '_url', $url_val));
	}

	echo $this->table->generate();
?>

<p><?= form_submit('submit', lang('submit'), 'class="submit"'); ?></p>

<?php $this->table->clear(); ?>

<?= form_close(); ?>

<?php
/* End of file index.php */
/* Location: ./system/expressionengine/third_party/vl_entry_url/views/index.php */