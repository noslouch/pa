<?php 
$this->load->view('errors'); 
?>
<div class="clear_left shun"></div>
<?php 
if(count($members) > 0)
{
?>
	<div style="clear:left"></div>
	
    <?=form_open($query_base.'edit_orders_ajax_filter', array('id' => 'order_form'))?>
	<div id="filterMenu">
		<fieldset>
			<legend><?=lang('members')?> <?=$total_members; ?></legend>


			<div class="group">
				<?=form_dropdown('group_id', $member_groups_dropdown, FALSE, 'id="group_id"')?>
				<?=form_dropdown('date_range', $date_select_options, $date_selected, 'id="date_range"').NBS.NBS?>
				
				<?=form_dropdown('export_format', $export_format, FALSE, 'id="export_format"')?> 
	
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				
				<?=form_label(lang('include_custom_fields'), 'include_custom_fields')?>&nbsp;
				<?=form_checkbox('include_custom_fields', '1', '1', 'id="include_custom_fields"')?> 
	
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
				
				<?=form_label(lang('complete_select'), 'complete_select')?>&nbsp;
				<?=form_checkbox('complete_select', '1', FALSE, 'id="complete_select"')?> 
	
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;				
				<input type="hidden" value="" name="perpage" id="f_perpage" />
				<input type="hidden" value="<?php echo $default_start_date; ?>" id="default_start_date" />
				
			</div>
			<div id="custom_date_picker" style="display: none; margin: 0 auto 25px auto;width: 500px; height: 245px; padding: 5px 15px 5px 15px; border: 1px solid black;  background: #FFF;">
				<div id="cal1" style="width:250px; float:left; text-align:center;">
					<p style="text-align:left; margin-bottom:5px"><?=lang('start_date', 'custom_date_start')?>:&nbsp; <input type="text" name="custom_date_start" id="custom_date_start" value="yyyy-mm-dd" size="12" /></p>
					<span id="custom_date_start_span"></span>
				</div>
				<div id="cal2" style="width:250px; float:left; text-align:center;">
					<p style="text-align:left; margin-bottom:5px"><?=lang('end_date', 'custom_date_end')?>:&nbsp; <input type="text" name="custom_date_end" id="custom_date_end" value="yyyy-mm-dd" size="12" /></p>
					<span id="custom_date_end_span"></span>          
				</div>
			</div>		
			
			
									
			<p>
				<?=form_label(lang('keywords').NBS, 'order_keywords', array('class' => 'field js_hide'))?>
				<?=form_input(array('id'=>'member_keywords', 'name'=>'member_keywords', 'class'=>'field', 'placeholder' => lang('keywords'), 'value'=>$member_keywords))?>
				&nbsp;&nbsp;
				<?=form_submit('submit', lang('search'), 'id="filter_order_submit" class="submit"')?>
				&nbsp;&nbsp;
				<?=form_submit('submit', lang('export'), 'id="export_submit" class="submit"')?>				 
			</p>
		</fieldset>
	</div>
    <?=form_close()?>	
<?php 

	echo form_open($query_base.'delete_order_confirm'); 
	
	$this->table->set_template($cp_pad_table_template);
	$this->table->set_heading(
		lang('id'),
		lang('username'),
		lang('screen_name'),
		lang('email'),
		lang('join_date'),
		lang('member_group')
	);

	foreach($members as $member)
	{
		
		$customer_link = 'customer_view&email=';
		$this->table->add_row(
								'<a href="'.$url_base.'order_view'.AMP.'id='.$member['member_id'].'">'.$member['member_id'].'</a>',
								$member['username'],
								$member['screen_name'],
								'<a href="mailto:'.$member['email'].'">'.$member['email'].'</a>',
								m62_convert_timestamp($member['join_date']),
								'<a href="javascript:;" rel="'.$member['group_id'].'" class="group_filter_id">'.$member['group_title'].'</a>'
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