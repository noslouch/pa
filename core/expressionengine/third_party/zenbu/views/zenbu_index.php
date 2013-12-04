<?php $form_attr = array('id' => 'filterMenu');?>
<?=form_open($action_url, $form_attr)?>
	
	<div id="filterMenu">
	
		
	
		<fieldset class="invisible" style="display: none"><legend><strong>Reserve</strong></legend>
		<div class="reserve">
			<?php foreach($rules_first_dropdown as $channel_id => $dropdown) :?>
				<?php if(isset($dropdown['dropdown_labels'])) :?>
					<?php 
					//	----------------------------------------
					// 	First dropdown manual creation
					//	----------------------------------------
					?>
					<select name="rule[][field]" class="first-input first_rule_<?=$channel_id?>" disabled="disabled">
						<?php foreach($dropdown['dropdown_labels'] as $val => $label) :?>
							<?php if(is_array($label)) :?>
								<optgroup label="<?=$val?>">
								<?php foreach($label as $optval => $optlabel) :?>
									<?php $selected = $optval == $rule_cond['field'] ? 'selected="selected"' : '';?>
									<?php $data_type = isset($option_type[$optval]) ? 'data-type="'.$option_type[$optval].'"' : ''; ?>
									<option value="<?=$optval?>" <?=$data_type?> <?=$selected?> ><?=$optlabel?></option>
								<?php endforeach ?>
								</optgroup>
							<?php else :?>
									<?php $selected = $val == $rule_cond['field'] ? 'selected="selected"' : '';?>
									<?php $data_type = isset($option_type[$val]) ? 'data-type="'.$option_type[$val].'"' : ''; ?>
									<option value="<?=$val?>" <?=$data_type?> <?=$selected?> ><?=$label?></option>
							<?php endif ?>
						<?php endforeach ?>
					</select>

				<?php endif;?>
			<?php endforeach;?>
		<br />	
			<?=form_dropdown('rule[][cond]', $second_dropdown_type1['labels'], '', 'class="second-input-type1" disabled="disabled"')?>
			<?=form_dropdown('rule[][cond]', $second_dropdown_type2['labels'], '', 'class="second-input-type2" disabled="disabled"')?>
			<?=form_dropdown('rule[][cond]', $second_dropdown_type3['labels'], '', 'class="second-input-type3" disabled="disabled"')?>
		<br />
			<?=form_dropdown('rule[][val]', $channels['dropdown_labels'], $this->input->get_post('channel_id'), 'class="channel_id" disabled="disabled"');?>
		<br />
			<?php foreach($categories as $channel_id => $dropdown) :?>
				<?php if(isset($dropdown['dropdown_labels'])) :?>
					<?=form_dropdown('rule[][val]', $dropdown['dropdown_labels'], $this->input->get_post('cat_id'), 'class="cat_id cat_'.$channel_id.'" disabled="disabled"');?>
				<?php endif;?>
			<?php endforeach;?>
		<br />
			<?php foreach($status as $channel_id => $dropdown) :?>
				<?php if(isset($dropdown['dropdown_labels'])) :?>
					<?=form_dropdown('rule[][val]', $dropdown['dropdown_labels'], $this->input->get_post('status'), 'class="status status_'.$channel_id.'" disabled="disabled"');?>
				<?php endif;?>
			<?php endforeach;?>
		<br />
			<?php foreach($authors as $channel_id => $dropdown) :?>
				<?php if(isset($dropdown['dropdown_labels'])) :?>
					<?=form_dropdown('rule[][val]', $dropdown['dropdown_labels'], $this->input->get_post('author'), 'class="author author_'.$channel_id.'" disabled="disabled"');?>
				<?php endif;?>
			<?php endforeach;?>
		<br />
			<?php foreach($custom_fields as $channel_id => $dropdown) :?>
				<?php if(isset($dropdown['dropdown_labels'])) :?>
					<?=form_dropdown('rule[][val]', $dropdown['dropdown_labels'], $this->input->get_post('field'), 'class="field field_'.$channel_id.'" disabled="disabled" ');?>
				<?php endif;?>
			<?php endforeach;?>
		<br />
			<?=form_dropdown('rule[][val]', $sticky['dropdown_labels'], $this->input->get_post('sticky'), 'class="sticky" disabled="disabled"');?>
		<br />
			<?=form_dropdown('rule[][val]', $entry_date['dropdown_labels'], $this->input->get_post('entry_date'), 'class="date" disabled="disabled"');?> 
			<span class="date invisible">  <input type="text" class="dateRange date_from" name="rule[][date_from]" size="8" disabled="disabled" /> - <input type="text" class="dateRange date_to" name="rule[][date_to]" size="8" disabled="disabled" /></span>
		<br />
			<?=form_input('rule[][val]', '', 'class="title"')?>
			<?=form_input('rule[][val]', '', 'class="fieldinput"')?>
		<br />
			<?php foreach($orderby_dropdown as $channel_id => $dropdown) :?>
				<?php if(isset($dropdown['dropdown_labels'])) :?>
					<?=form_dropdown('orderby', $dropdown['dropdown_labels'], $this->input->get_post('orderby'), 'class="orderby_'.$channel_id.'" disabled="disabled"');?>
				<?php endif;?>
			<?php endforeach;?>
		</div>
		</fieldset>
		
		
		<div class="right <?php if(empty($saved_searches)):?>invisible<?php endif ?>" id="savedsearches" style="height: 100px">
			<fieldset>
				<legend><strong><?=lang('saved_searches_list')?></strong>&nbsp;&nbsp;<a href="<?=$action_url_manage_searches?>" title="<?=lang('edit')?>"><i class="icon-edit icon-large"></i></a></legend>
				<div>
				<?=$saved_searches?>
				</div>
			</fieldset>
		</div>
		
		
		<div id="rulefields">
			<?php if(isset($rules) && ! empty($rules)) :?>
				<?php foreach($rules as $rule_number => $rule_cond) :?>
					<?php 
					/**
					* ===============================
					* Page refresh rendering of rules
					* ===============================
					*/
					?>
					<?php if($rule_number == "0") :?>

						<?php $current_channel_id = $rule_cond['val'];?>
						<?=form_fieldset()?>
						<legend><strong><?=lang('search_entries')?></strong></legend>
							<div id="channel_rule">
								<span class="first">
									<?=lang('channel')?>
									<?=form_hidden('rule[0][field]', 'channel_id')?>
								</span>
								<span class="second">
									<?=lang('is')?>
									<?=form_hidden('rule[0][cond]', 'is')?>
								</span>
								<span class="third">
									<?=form_dropdown('rule[0][val]', $channels['dropdown_labels'], $rule_cond['val'], 'class="channel"');?>
								</span>
							</div>
							
							<div class="clear"></div>
						<?=form_fieldset_close()?>
						<?=form_fieldset()?>	

					<?php elseif ( isset($rule_cond['field']) ):?>

						<?php if($rule_number == "1") :?>
							<table class="rule">
						<?php endif;?>
						<tr class="rule last">

							<td class="first">
								<?php if( ! isset($rules_first_dropdown['ch_id_'.$current_channel_id]['dropdown_labels'])) :?>
									<?php $rules_first_dd = $rules_first_dropdown['ch_id_0']['dropdown_labels']; ?>
								<?php else :?>
									<?php $rules_first_dd = $rules_first_dropdown['ch_id_'.$current_channel_id]['dropdown_labels']; ?>
								<?php endif;?>
								<?php 
								//	----------------------------------------
								// 	First dropdown manual creation
								//	----------------------------------------
								?>
								<select name="<?='rule['.$rule_number.'][field]'?>" class="first-input" style="min-width: 10%">
									<?php foreach($rules_first_dd as $val => $label) :?>
										<?php if(is_array($label)) :?>
											<optgroup label="<?=$val?>">
											<?php foreach($label as $optval => $optlabel) :?>
												<?php $selected = $optval == $rule_cond['field'] ? 'selected="selected"' : '';?>
												<?php $data_type = isset($option_type[$optval]) ? 'data-type="'.$option_type[$optval].'"' : ''; ?>
												<option value="<?=$optval?>" <?=$data_type?> <?=$selected?> ><?=$optlabel?></option>
											<?php endforeach ?>
											</optgroup>
										<?php else :?>
												<?php $selected = $val == $rule_cond['field'] ? 'selected="selected"' : '';?>
												<?php $data_type = isset($option_type[$val]) ? 'data-type="'.$option_type[$val].'"' : ''; ?>
												<option value="<?=$val?>" <?=$data_type?> <?=$selected?> ><?=$label?></option>
										<?php endif ?>
									<?php endforeach ?>
								</select>

							</td>
							<td class="second">
								<?php 
								
								if(isset($rule_cond['field']))
								{
									$rc = $rule_cond['field'];

									$ot = isset($option_type[$rc]) ? $option_type[$rc] : '';
									
									switch($ot)
									{
										case 'is_isnot': case 'date':
											echo form_dropdown('rule['.$rule_number.'][cond]', $second_dropdown_type1['labels'], $rule_cond['cond'], 'class="second-input-type1"');
										break;
										case 'contains_doesnotcontain':
											echo form_dropdown('rule['.$rule_number.'][cond]', $second_dropdown_type2['labels'], $rule_cond['cond'], 'class="second-input-type2"');
										break;
										case 'standard':
											echo form_dropdown('rule['.$rule_number.'][cond]', $second_dropdown_type3['labels'], $rule_cond['cond'], 'class="second-input-type3"');
										break;
									}
									
								} else {
									echo form_dropdown('rule['.$rule_number.'][cond]', $second_dropdown_type1['labels'], '', 'class="second-input-type1"');
								}
								?>
							</td>
							<td class="third">
								<?php
								if(isset($rule_cond['field']))
								{
									switch ($rule_cond['field'])
									{
										case "cat_id":
											echo form_dropdown('rule['.$rule_number.'][val]', $categories['ch_id_'.$current_channel_id]['dropdown_labels'], $rule_cond['val'], 'class="cat_id cat_'.$current_channel_id.'"');
										break;
										case "status":
											echo form_dropdown('rule['.$rule_number.'][val]', $status['ch_id_'.$current_channel_id]['dropdown_labels'], $rule_cond['val'], 'class="cat_id cat_'.$current_channel_id.'"');
										break;
										case "author":
											echo form_dropdown('rule['.$rule_number.'][val]', $authors['ch_id_'.$current_channel_id]['dropdown_labels'], $rule_cond['val'], 'class="cat_id cat_'.$current_channel_id.'"');
										break;
										case "sticky":
											echo form_dropdown('rule['.$rule_number.'][val]', $sticky['dropdown_labels'], $rule_cond['val'], 'class="sticky"');
										break;
										case "date": case 'expiration_date': case 'edit_date': case $ot == 'date':
											// For saved values of '+' in older versions, convert the '+' to an entity.
											$sel		= strncmp($rule_cond['val'], '+', 1) == 0 ? '&#43;'.substr($rule_cond['val'], 1) : $rule_cond['val'];
											$hiderange	= $sel == 'range' ? '' : 'invisible';
											$disabledrange = $sel == 'range' ? '' : 'disabled="disabled"';
											$date_from	= isset($rule_cond['date_from']) ? $rule_cond['date_from'] : '';
											$date_to	= isset($rule_cond['date_to']) ? $rule_cond['date_to'] : '';
											echo form_dropdown('rule['.$rule_number.'][val]', $entry_date['dropdown_labels'], $sel, 'class="date"');
											echo '<span class="date '. $hiderange . '">  <input type="text" class="dateRange date_from" name="rule['.$rule_number.'][date_from]" size="8" value="' . $date_from . '" ' . $disabledrange . ' /> - <input type="text" class="dateRange date_to" name="rule['.$rule_number.'][date_to]" size="8" value="' . $date_to . '" ' . $disabledrange . ' /></span>';
										break;
										default:
											$invisible = ($rule_cond['cond'] == "isempty" || $rule_cond['cond'] == "isnotempty") ? 'invisible' : '';
											echo form_input('rule['.$rule_number.'][val]', $rule_cond['val'], 'class="field field_'.$channel_id.' '.$invisible.'" ');
										break;
									}
								}
								?>
							</td>
							
							<td class="controls nowrap">
								<button class="addrule" type="button" title="<?=lang('add_filter_rule')?>"><i class="icon-plus-sign icon-2x"></i></button>
								<?php if(count($rules) > 2) :?>
								<button class="removerule" type="button" title="<?=lang('remove_filter_rule')?>"><i class="icon-minus-sign icon-2x"></i></button>
								<?php endif;?>
							</td>
						</tr>
					<?php else : ?>
						<?php
						/**
						* =======================================================================
						* Load default/initial fields when $rules array
						* is in irregular format (occurs when eg. too rapid sequential reloading)
						* =======================================================================
						*/				
						?>
						<?php if($rule_number == 1) :?>
						<p class="notice rapidloaderror"><?=lang('rapid_loading_error')?></p>
						<table class="rule last">
							<tr class="rule">
								<td class="first">
									<?=form_dropdown('rule['.$rule_number.'][field]', $rules_first_dropdown['ch_id_0']['dropdown_labels'], '', 'class="first-input" style="min-width: 10%"')?>
								</td>
								<td class="second">
									<?=form_dropdown('rule['.$rule_number.'][cond]', $second_dropdown_type1['labels'], '', 'class="second-input"')?>
								</td>
								<td class="third">
									<?=form_dropdown('rule['.$rule_number.'][val]', $categories['ch_id_0']['dropdown_labels'], '', 'class="cat_id cat_0"');?>
								</td>
								
								<td class="controls">
								<button class="addrule" type="button" title="<?=lang('add_filter_rule')?>">+</button>
								</td>
							</tr>
						</table>
						<?php endif;?>
					<?php endif;?>
				<?php endforeach;?>
					</table>
				<?=form_fieldset_close()?>
				<?=form_fieldset()?>
					<div class="limit">
						<?=form_dropdown('limit', $limit['dropdown_labels'], ($this->input->get_post('limit') == "") ? $limit_val : $this->input->get_post('limit'), 'class=""')?>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<?=lang('orderby')?>&nbsp;
						<span class="orderby">
							<?=form_dropdown('orderby', $orderby_dropdown['ch_id_'.$current_channel_id]['dropdown_labels'], ($this->input->get_post('orderby') == "") ? $orderby_val : $this->input->get_post('orderby'), 'class="orderby orderby_ch_'.$current_channel_id.'"');?>
						</span>
						<?=form_dropdown('sort', $sort['dropdown_labels'], ($this->input->get_post('sort') == "") ? $sort_val : $this->input->get_post('sort'), 'class="sort"');?>

					</div>
				<?=form_fieldset_close()?>
			<?php 
			/**
			* ================================
			* Initial fields when loading page
			* ================================
			*/
			?>
			<?php else:?>
			
			Initial fields when loading page
			<?=form_fieldset()?>
			<legend><strong><?=lang('search_entries')?></strong></legend>
				<div id="channel_rule">
					<span class="first">
						<?=lang('channel')?>
						<?=form_hidden('rule[0][field]', 'channel_id')?>
					</span>
					<span class="second">
						<?=lang('is')?>
						<?=form_hidden('rule[0][cond]', 'is')?>
					</span>
					<span class="third">
						<?=form_dropdown('rule[0][val]', $channels['dropdown_labels'], '', 'class="channel"');?>
					</span>
				</div>
			<?=form_fieldset_close()?>
			
			<?=form_fieldset()?>
				<table class="rule last">
					<tr class="rule">
						<td class="first">
							<?=form_dropdown('rule[1][field]', $rules_first_dropdown['ch_id_0']['dropdown_labels'], '', 'class="first-input" style="min-width: 10%"')?>
						</td>
						<td class="second">
							<?=form_dropdown('rule[1][cond]', $second_dropdown_type1['labels'], '', 'class="second-input"')?>
						</td>
						<td class="third">
							<?=form_dropdown('rule[1][val]', $categories['ch_id_0']['dropdown_labels'], '', 'class="cat_id cat_0"');?>
						</td>
						
						<td class="controls">
						<button class="addrule" type="button" title="<?=lang('add_filter_rule')?>">+</button>
						</td>
					</tr>
				</table>
			<?=form_fieldset_close()?>
			
			<?=form_fieldset()?>
				<div class="limit">
					<?=form_dropdown('limit', $limit['dropdown_labels'], ($this->input->get_post('limit') == "") ? 25 : $this->input->get_post('limit'), 'class=""')?>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?=lang('orderby')?>&nbsp;
					<span class="orderby">
						<?=form_dropdown('orderby', $orderby_dropdown['ch_id_0']['dropdown_labels'], $this->input->get_post('orderby'), 'class="orderby orderby_ch_0"');?>
					</span>
					<?=form_dropdown('sort', $sort['dropdown_labels'], $this->input->get_post('sort'));?>
				</div>
			<?=form_fieldset_close()?>
			<br />
			<?php endif;?>
		</div>
		
		<?php /*<input type="text" name="perpage" value="<?=$showing_to?>" />*/ ?>
		
		<button type="submit" class="submit left zenbusearch invisible">
			<span><?=lang('search')?></span>
			<span class="onsubmit invisible"><?=lang('searching')?></span>
		</button><span class="loader left invisible"><i class="icon-spinner icon-spin icon-large"></i></span>

	</div>
	<br />
<?=form_close()?>


<div class="right">
	<span class="rule_label invisible">
		<label for="save_search_name"><?=lang('give_rule_label')?>&nbsp;</label><?=form_input('save_search_name', '', 'id="save_search_name"')?><button class="savesearch submit"><?=lang('save')?></button>
		<a href="javascript:;" class="cancelsavesearch"><?=lang('cancel');?></a>
	</span>
	<button type="button" class="savesearchInit left submit"><?=lang('save_this_search')?></button>
	
	<?=$extra_options_right_save?>
</div>
<!-- Result table: -->

<div id="resultArea">


<?php if(isset($total_results) && $total_results > 0):?>
	<?=lang('showing')?> <?=($total_results > 1) ? lang('results') : lang('result')?> <?=$showing?> <?=lang('to')?> <?=$showing_to?> <?=lang('out_of')?> <?=$total_results?> <?=($total_results > 1) ? lang('results') : lang('result')?>
<?php else:?>
	<?=lang('no_results')?>
<?php endif;?>

<div class="clear"></div>

<?=$pagination?>
<?=form_open($action_url_entries)?>
<div id="resultArea-inner">
<table class="mainTable resultTable sortable" width="100%" cellspacing="0" cellpadding="0" border="0">
<thead>
<tr class="">
	<?php if(isset($entry)):?>
		<th width="1%" id="entry_checkbox"><?=form_checkbox('', '', '', 'class="selectAll" id="checkcolumn"')?></th>
		
		<?php foreach($field_order as $order => $col_name) :?>
			
			<?php if(substr($col_name, 0, 6) != 'field_') :?>
				
				<?php if(isset(${$col_name}) && ${$col_name} == "y") :?>
					<?php $col_name = str_replace('show_', '', $col_name); ?>
					<?php $col_name = ($col_name == 'view') ? 'live_look' : $col_name; ?>
					<?php $col_name = ($col_name == 'sticky') ? 'is_sticky' : $col_name; ?>
					<?php $col_name = ($col_name == 'id' && $col_name != $orderby_val) ? '#' : $col_name; ?>
					<?php $col_name = ($col_name == 'categories') ? 'category' : $col_name; ?>
						
							<th id="<?=$col_name?>" class="<?=($orderby_val == $col_name && $sort_val == "desc") ? 'headerSortUp' : '' ;?><?=($orderby_val == $col_name && $sort_val == "asc") ? 'headerSortDown' : '' ;?>"><?=lang($col_name)?></th>
						
				<?php endif;?>
			
			<?php else :?>
			
				
				<?php $field_id = str_replace('field_', '', $col_name);?>
				<?php if(isset($field[$field_id])):?>
				
						<th id="field_id_<?=$field_id?>"><?=$field[$field_id]?></th>
					
				<?php endif;?>
				
			<?php endif;?>
		
		<?php endforeach;?>
		
		
	<?php endif;?>
	
</tr>
</thead>

<tbody>
	<?php if(isset($entry)):?>
		<?php $c = 1;?>
		<?php foreach($entry as $id => $entry_info) :?>
		<?php $row_class = ($c % 2 == 0) ? 'odd' : 'even';?>
		<tr class="entryRow <?=$row_class?>">
			<td class="selectable"><?=form_checkbox('toggle[]', $id, '', 'class="checkcolumn"')?></td>
			
			<?php foreach($field_order as $order => $col_name) :?>
				<?php if(substr($col_name, 0, 6) != 'field_') :?>
					<?php /* -- standard stuff -- */ ?>
					<?php $data = str_replace('show_', '', $col_name);?>
					<?php if(isset(${$col_name}) && ${$col_name} == "y") :?>
						<td><?= ! empty($entry_info[$data]) ? $entry_info[$data] : '&nbsp;'?></td>
					<?php endif;?>
			
				<?php else :?>
			
					<?php /* -- fields -- */ ?>
					<?php $field_id = str_replace('field_', '', $col_name);?>
					<?php if(isset($entry_info['fields'][$field_id])):?>
					
							<td<?=($field_text_direction[$field_id] == 'rtl') ? ' style="direction: rtl; text-align: right" dir="rtl"' : ''?>><?=$entry_info['fields'][$field_id]?></td>
						
					<?php endif;?>
				<?php endif;?>
			<?php endforeach;?>
			
			
			
		</tr>
		<?php $c++;?>
		<?php endforeach;?>
	<?php endif;?>
</tbody>
</table>
</div> <?php /* -- ResultArea-inner -- */ ?>
<?=$pagination?>

<br /><br />

<?php if(isset($total_results) && $total_results > 0):?>

<table class="" border="0">
	<tr>
		<th><?=lang('entries')?></th>
		<th></th>
		<th class="catEditButtons">
			<?php $ch_id = $this->input->get_post('channel_id');?>
			<?php if($hide_categories != 'y' || empty($ch_id)) :?>
			<?=lang('categories')?>
			<?php endif; ?>
		</th>
	</tr>
	<tr>
		<td>
			<button name="action" type="submit" value="edit" class="submit zenbu_action_validate"><?=lang('edit')?></button>
			<?php if($can_delete_self_entries == 'y' || $can_delete_all_entries == 'y') :?>
			<button name="action" type="submit" value="delete" class="submit zenbu_action_validate"><?=lang('delete')?></button>
			<?php endif;?>
		</td>
		
		<td>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
		
		<td class="catEditButtons">
			<?php if($hide_categories != 'y' || empty($ch_id)) :?>
			<button name="action" type="submit" value="add_categories" class="submit zenbu_action_validate"><?=lang('add')?></button>
			<button name="action" type="submit" value="remove_categories" class="submit zenbu_action_validate"><?=lang('remove')?></button>
			<?php endif;?>
		</td>
	</tr>
</table>
<?php endif;?>
&nbsp;&nbsp;&nbsp;

<?=form_close()?>


</div> <?php /* -- END resultArea -- */ ?>

<div style="display: block;" id="fancybox" class="image_overlay">
	<div class="contentWrap"></div>
</div>