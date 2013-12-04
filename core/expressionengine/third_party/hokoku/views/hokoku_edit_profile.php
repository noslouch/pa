<?php $this->EE =& get_instance(); ?>
<?=form_open($action_url, 'id="exportform"')?>
	
	<?=$update_profile?>
	<label for="profile_label"><?=lang('profile_name')?></label> <input type="text" class="profile_label" id="profile_label" name="profile_label" value="<?=$profile_label?>" placeholder="<?=lang('profile_name')?>" />
	<div class="clear"></div>
	<br />
	<span class=""><strong><?=lang('file_format');?></strong> &nbsp;&nbsp;<?=$export_formats;?></span>
	<br />
	<br />
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


	<div id="exportOptions" class="left">
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

	<?php if(isset($member_groups)) : ?>
		<h3><?=lang('copy_to_member_groups')?></h3>
		<table class="mainTable" width="100%" cellspacing="0">
			<tr>
				<th><?=form_checkbox('')?></th>
				<th><?=lang('copy_profile_to')?></th>
			</tr>
			<?php foreach($member_groups as $m_id => $m_label) :?>
			<tr>
				<td width="1%"><?=form_checkbox('copy_profile[]', $m_id, $checked[$m_id])?></td>
				<td><?=$m_label?></td>
			</tr>
			<?php endforeach;?>
			</table>
	<?php endif ?>
	
	<button type="submit" class="submit savepreset" name="preset" value="save"><span class=""><?=lang('save_preset')?></span><span class="invisible onsubmit"><?=lang('saving')?> <i class="icon-spinner icon-spin icon-large"></i></button>

	&nbsp;&nbsp;<a href="<?=$return_to_manage_profiles?>"><?=lang('return_to_manage_profiles')?></a>
	

<?=form_close();?>

<style>

.invisible
{
	display: none;
}

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

textarea, input[type="text"] {
	width: auto;
}

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