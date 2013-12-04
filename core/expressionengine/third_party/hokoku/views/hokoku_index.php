<?php if( ! empty($format)) : ?>
	<p><?=lang('profile_export_message')?><strong><?=$format?></strong></p>
	<a href="<?=$return_to_zenbu_url?>"><?=lang('return_to_zenbu')?></a>
<?php endif;?>

<?=form_open($action_url, 'id="exportform"')?>
	<?=$rules?>
	<span class="<?= ! empty($format) ? 'invisible' : ''?>"><strong><?=lang('file_format');?></strong> &nbsp;&nbsp;<?=$export_formats;?></span>
	<div class="clear"></div>
	
	<?php if( ! empty($saved_searches) ) : ?>
		<fieldset class="right <?= ! empty($format) ? 'invisible' : ''?>">
			<legend><h2><?=lang('saved_searches')?></h2></legend>
			<ul>
				<?php foreach($saved_searches as $key => $link) :?>
				<li>
					<?=$link['rule_url']?>
				</li>
				<?php endforeach;?>
			</ul>
		</fieldset>
	<?php endif; ?>


	<div id="exportOptions" class="left <?= ! empty($format) ? 'invisible' : ''?>">
		<?php foreach($export_options as $ext => $opt) :?>
		<span class="options-<?=$ext?> <?=$format == $ext || (empty($format) && $ext == 'csv') ? '' : 'invisible'?>">
			<table class="mainTable" width="100%" cellspacing="0">
			<tr>
				<th colspan="2">
					<?=lang('options')?>
				</th>
			</tr>
			<?php foreach($opt as $label => $input) :?>
			<tr>
				<td><?=$label?></td>
				<td><?=$input?></td>
			</tr>
			<?php endforeach;?>
			</table>
		</span>
		<?php endforeach;?>
	</div>
	
	<div class="clear"></div>

	<div id="resultArea-inner" class="<?= ! empty($format) ? 'invisible' : ''?>">
	<?php if(isset($entry)):?>
	<br />
	<button type="button" class="exampleData" id="exampleData"><?=lang('show_data_sample')?></button>
	<button type="button" class="invisible exampleData" id="exampleData"><?=lang('hide_data_sample')?></button>
		<table class="mainTable resultTable exportTable" width="100%" cellspacing="0">
		<thead>
		<tr>
			<th></th>
			
				<?php foreach($field_order as $order => $col_name) :?>
							
					<?php if(substr($col_name, 0, 6) != 'field_') :?>
						<?php /* -- standard stuff -- */ ?>
						
						<?php if(isset(${$col_name}) && ${$col_name} == "y") :?>
							<?php $col_name = str_replace('show_', '', $col_name); ?>
							<?php $col_name = ($col_name == 'view') ? 'live_look' : $col_name; ?>
							<?php $col_name = ($col_name == 'sticky') ? 'is_sticky' : $col_name; ?>
							<?php $col_name = ($col_name == 'id') ? 'id' : $col_name; ?>
								<?php if($col_name == "mx_cloner" && ! in_array('Mx_cloner_ext', $extensions)):?>
								<?php else:?>
									<th id="<?=$col_name?>">
										<div class="center">
										<?=lang($col_name)?>
										</div>
									</th>
								<?php endif;?>
						<?php endif;?>
					
					<?php else :?>
					
						<?php /* -- fields -- */ ?>
						<?php $field_id = str_replace('field_', '', $col_name);?>
						<?php if(isset($field[$field_id])):?>
						
								<th id="field_id_<?=$field_id?>">
									<?=$field[$field_id]?>
								</th>
							
						<?php endif;?>
						
					<?php endif;?>
				
				<?php endforeach;?>

		</tr>
		<tr class="">
			<td class="nowrap optionRow"><strong><?=lang('plain_text')?></strong></td>
			
				<?php foreach($field_order as $order => $col_name) :?>
					<?php if(substr($col_name, 0, 6) != 'field_') :?>
						<?php /* -- standard stuff -- */ ?>

						<?php $data = str_replace('show_', '', $col_name);?>
						<?php if(isset(${$col_name}) && ${$col_name} == "y") :?>
							<?php if($col_name == "show_mx_cloner" && ! in_array('Mx_cloner_ext', $extensions)):?>
							<?php else:?>
								<td class="optionRow clickable center">
									<?=form_checkbox('plain_text[' . $col_name . ']', 'y', TRUE)?>
								</td>
							<?php endif;?>
						<?php endif;?>
				
					<?php else :?>

						<?php /* -- fields -- */ ?>
						<?php $field_id = str_replace('field_', '', $col_name);?>
						<?php if(isset($field[$field_id])):?>

							<td class="optionRow clickable center">
								<?=form_checkbox('plain_text[' . $col_name . ']', 'y', TRUE)?>
							</td>

						<?php endif;?>

					<?php endif;?>
				<?php endforeach;?>

		</tr>
		</thead>
		

		
		<tbody id="exampleData" style="display: none;">
			<?php $c = 1;?>		
			<?php foreach($entry as $id => $entry_info) :?>
				<?php /*if($c <= 5): */?>
				<?php $row_class = ($c % 2 == 0) ? 'odd' : 'even';?>
				<tr class="entryRow <?=$row_class?>">
					<td class="selectable">
						&nbsp;
					</td>
					<?php foreach($field_order as $order => $col_name) :?>
						<?php if(substr($col_name, 0, 6) != 'field_') :?>
							<?php /* -- standard stuff -- */ ?>
							<?php $data = str_replace('show_', '', $col_name);?>
							<?php if(isset(${$col_name}) && ${$col_name} == "y") :?>
								<?php if($col_name == "show_mx_cloner" && ! in_array('Mx_cloner_ext', $extensions)):?>
								<?php else:?>
									<td class="clickable"><?= ! empty($entry_info[$data]) ? $entry_info[$data] : '&nbsp;'?></td>
								<?php endif;?>
							<?php endif;?>
					
						<?php else :?>
					
							<?php /* -- fields -- */ ?>
							<?php $field_id = str_replace('field_', '', $col_name);?>
							<?php if(isset($entry_info['fields'][$field_id])):?>
							
									<td<?=($field_text_direction[$field_id] == 'rtl') ? ' style="direction: rtl; text-align: right" dir="rtl"' : ''?> class="clickable"><?=$entry_info['fields'][$field_id]?></td>
								
							<?php endif;?>
						<?php endif;?>
					<?php endforeach;?>
				</tr>
				<?php /*endif;*/ ?>
			<?php $c++;?>
			<?php endforeach;?>
		</tbody>
		</table>
		
	<?php else:?>
		<p><?=lang('no_results')?></p>
	<?php endif;?>
	</div>


<br />
<input type="hidden" name="limit" value="<?=$perpage?>" />
<input type="hidden" name="perpage" value="0" />
<input type="hidden" name="total_results" value="<?=$total_results?>" />
<button type="submit" class="submit export"><?=lang('export')?></button>

<?=form_close()?>
<?php if( empty($format)) : ?>
	<br />
	<a href="<?=$return_to_zenbu_url?>"><?=lang('return_to_zenbu_main_page')?></a>
<?php endif ?>

<br />

<span class="loader left invisible"></span>
<span id="progress"></span>
<span id="exportcomplete"></span>

<style>

span#progress
{
	font-weight: bold;
	font-size: 1.2em;
}

.loader
{
	background: transparent url(<?=$themes_url?>/cp_themes/default/images/indicator.gif) top left no-repeat;
	height: 16px;
	width: 16px;
	margin: 0px 10px 0 0px;
}



/**
*	EXPORT OPTIONS
*/
table.exportOptions
{
	
}

table.exportOptions td
{
	padding: 0px 20px 3px 0px;
}

/**
* 	EXPORT TABLE
*/
table.mainTable tr td.optionRow
{
	background-color:#fffcbf;
	/*background-image:-webkit-gradient(linear, 0% 0%, 0% 100%, from(rgba(255, 255, 255, .5)), to(rgba(255, 255, 255, 0)));
	background-image:-moz-linear-gradient(top, rgba(255, 255, 255, .5), rgba(255, 255, 255, 0));*/
	font-size:11px;
	text-shadow:0 1px 0 #fff;
}

table.mainTable tr.selected.odd td
{
	background: #F4F6F6;
}

table.mainTable tr.selected.even td
{
	background: #EBF0F2;
}

/**
* 	EXPORT PAGE
*/
ul.exportLinks li
{
	display: block;
	float: left;
	margin: 10px 5px 0 0
}
</style>