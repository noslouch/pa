<?php 
$this->load->view('errors'); 
?>
<div class="clear_left shun"></div>
<?php 
if(count($comments) > 0)
{
?>
	<div style="clear:left"></div>
	
    <?=form_open($query_base.'edit_orders_ajax_filter', array('id' => 'order_form'))?>
	<div id="filterMenu">
		<fieldset>
			<legend><?=lang('comments')?> <?=$total_comments; ?></legend>


			<div class="group">
				<?=form_dropdown('channel_id', $comment_channels, FALSE, 'id="channel_id"')?> 
				<?=form_dropdown('date_range', $date_select_options, $date_selected, 'id="date_range"').NBS.NBS?>	
				<?=form_dropdown('status', $status_select, FALSE, 'id="status"')?> 				
				<?=form_dropdown('export_format', $export_format, FALSE, 'id="export_format"')?> 
				
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
		lang('comment'),
		lang('entry_title'),
		lang('name'),
		lang('date'),
		lang('channel_title'),
		lang('status')
	);

	foreach($comments as $comment)
	{		
		$status = (isset($status_select[$comment['status']]) ? $status_select[$comment['status']] : $comment['status']);
		$this->table->add_row(
								'<a href="?D=cp&C=addons_modules&M=show_module_cp&module=comment&method=edit_comment_form&comment_id='.$comment['comment_id'].'">'.word_limiter($comment['comment'], 10).'</a>',
								'<a href="javascript:;" rel="'.$comment['title'].'" class="keyword_filter_value">'.$comment['title'].'</a>',
								'<a href="javascript:;" rel="'.$comment['name'].'" class="keyword_filter_value">'.$comment['name'].'</a>',
								m62_convert_timestamp($comment['comment_date']),
								'<a href="javascript:;" rel="'.$comment['channel_id'].'" class="channel_filter_id">'.$comment['channel_title'].'</a>',
								'<a href="javascript:;" rel="'.$comment['status'].'" class="status_filter_id">'.$status.'</a>'
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