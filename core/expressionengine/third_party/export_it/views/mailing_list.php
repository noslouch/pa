<?php 
$this->load->view('errors'); 
?>
<div class="clear_left shun"></div>
<?php 
if(count($emails) > 0)
{
?>
	<div style="clear:left"></div>
	
    <?=form_open($query_base.'edit_orders_ajax_filter', array('id' => 'order_form'))?>
	<div id="filterMenu">
		<fieldset>
			<legend><?=lang('emails')?> <?=$total_emails; ?></legend>


			<div class="group">
				<?=form_dropdown('list_id', $mailing_lists, FALSE, 'id="list_id"')?>				
				<?=form_dropdown('export_format', $export_format, FALSE, 'id="export_format"')?> 
				
				<input type="hidden" value="" name="perpage" id="f_perpage" />
				
			</div>	
			
									
			<p>
				<?=form_label(lang('keywords').NBS, 'keywords', array('class' => 'field js_hide'))?>
				<?=form_input(array('id'=>'keywords', 'name'=>'keywords', 'class'=>'field', 'placeholder' => lang('keywords'), 'value'=>$keywords))?>
				&nbsp;&nbsp;
				<?=form_submit('submit', lang('search'), 'id="filter_order_submit" class="submit"')?>
				&nbsp;&nbsp;
				<?=form_submit('submit', lang('export'), 'id="export_submit" class="submit"')?>				 
			</p>
		</fieldset>
	</div>
    <?=form_close()?>	
<?php 

	echo form_open($query_base.'mailinglist_stuff'); 
	
	$this->table->set_template($cp_pad_table_template);
	$this->table->set_heading(
		lang('email'),
		lang('ip_address'),
		lang('mailinglists')
	);

	foreach($emails as $email)
	{	

		$this->table->add_row(
								'<a href="mailto:'.$email['email'].'">'.$email['email'].'</a>',
								$email['ip_address'],
								m62_create_mailinglist_links($email['list_names'], $mailing_lists)
		);
	}
	
	echo $this->table->generate();
	
?>
<div class="tableFooter">

	<span class="js_hide"><?php echo $pagination?></span>	
	<span class="pagination" id="filter_pagination"></span>
</div>	

<?php echo form_close()?>

<?php } else { ?>
<?php echo lang('no_matching_members')?>
<?php } ?>