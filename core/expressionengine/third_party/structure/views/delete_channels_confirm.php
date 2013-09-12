<?=form_open($action_url, $attributes)?>

<input type="hidden" name="channel_ids" value="<?=$to_be_deleted?>">
<p class="notice">Are you sure you want to permanently remove these channels from Structure?</p>
<ul>
<?php foreach ($channel_titles as $channel): ?>
  <li><strong><?=$channel['channel_title']; ?></strong></li>
<?php endforeach; ?>
</ul>
<br />
<p>Setting these channels to "Unmanaged" will <strong>remove every page created with them</strong> from the Structure nav tree. This will also remove any child pages under them. Make sure this is what you want to do before proceeding.</p>

<?=form_submit(array('name' => 'submit', 'value' => "Yes I Know What I'm Doing, Delete Them!", 'class' => 'submit'))?>
<a href="<?=$base_url.AMP.'module=structure';?>" style="margin-left:10px;">Cancel</a>

<?=form_close()?>