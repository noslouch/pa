<?php echo form_open($base.AMP.'method=config_delete', '', array('config_id' => $config_id)) ?>

<p class="notice"><?php echo lang('wygwam_delete_config_confirm') ?></p>
<p><?php echo $config_name ?></p>

<p><?php echo form_submit(array('name' => 'submit', 'value' => lang('delete'), 'class' => 'submit')) ?></p>

<?php echo form_close() ?>