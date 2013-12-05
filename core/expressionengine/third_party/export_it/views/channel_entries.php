<?php 
$this->load->view('errors'); 
?>
<div class="clear_left shun"></div>
	<div style="clear:left"></div>
	
    <?=form_open($query_base.'edit_orders_ajax_filter', array('id' => 'order_form'))?>
	<div id="filterMenu">
		<fieldset>
			<legend><?=lang('channel_entries')?> <?=$total_entries; ?></legend>


			<div class="group">
				<?=form_dropdown('channel_id', $channel_options, $channel_id, 'id="channel_id"')?> 
				<?=form_dropdown('date_range', $date_select_options, $date_selected, 'id="date_range"').NBS.NBS?>	
				<?=form_dropdown('status', $status_options, FALSE, 'id="status"')?>
				<?=form_dropdown('category', $category_options, FALSE, 'id="category"')?> 	 				
				<?=form_dropdown('export_format', $export_format, FALSE, 'id="export_format"')?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	
				
				<?=form_label(lang('complete_select'), 'complete_select')?>&nbsp;
				<?=form_checkbox('complete_select', '1', FALSE, 'id="complete_select"')?> 				 
				
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

	echo form_open($query_base.'void'); 
	
	$this->table->set_template($cp_pad_table_template);
	$this->table->set_heading(
		lang('ID'),
		lang('entry_title'),
		lang('channel_title'),
		lang('date'),
		lang('status')
	);

	if(count($entries) >= '1')
	{
		foreach($entries as $entry)
		{		
			$this->table->add_row(
									'<a href="?D=cp&C=addons_modules&M=show_module_cp&module=comment&method=edit_comment_form&comment_id='.$entry['entry_id'].'">'.$entry['entry_id'].'</a>',
									'<a href="javascript:;" rel="'.$entry['title'].'" class="keyword_filter_value">'.$entry['title'].'</a>',
									'<a href="javascript:;" rel="'.$entry['channel_id'].'" class="channel_filter_id">'.$entry['channel_title'].'</a>',
									m62_convert_timestamp($entry['entry_date']),
									'<a href="javascript:;" rel="'.$entry['status'].'" class="status_filter_id">'.$entry['status'].'</a>'
									//'<span style="color:#'.m62_status_color($entry['status'], $order_channel_statuses).'">'.lang($entry['status']).'</span>'
			);
		}
	}
	else
	{
		$cell = array('data' => lang('no_matching_channel_entries'), 'colspan' => 5);
		$this->table->add_row($cell);
	}
	
	echo $this->table->generate();
	
?>
<div class="tableFooter">

	<span class="js_hide"><?php echo $pagination?></span>	
	<span class="pagination" id="filter_pagination"></span>
</div>	

<?php echo form_close()?>
