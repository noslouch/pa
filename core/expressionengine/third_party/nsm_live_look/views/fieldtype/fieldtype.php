<?php
/**
 * Custom field
 *
 * @package			NsmLiveLook
 * @version			1.2.4
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2013 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */
?>
<!-- Start LG Live Look Tab -->
<div class="mor cf nsm_live_look <?= ( empty($urls) ? 'nsm_inactive_tab' : '' ) ?>" data-addonid="nsm_live_look">
	<?php if(empty($urls)): ?>
		<div class="alert error"><?php print $this->lang->line('nsm_live_look_alert.error.no_preview_urls'); ?></div>
	<?php elseif($entry_id == FALSE) : ?>
		<div class="alert error"><?php print $this->lang->line('nsm_live_look_alert.error.entry_unsaved'); ?></div>
	<?php else: ?>
		<?php if(count($urls) > 1) : ?>
		<ul class="menu tabs">
			<?php foreach($urls as $count => $url) : ?>
				<li><a href="#url-<?=$count?>" class="active"><?= $url["title"]; ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
		<?php foreach($urls as $count => $url) : ?>
			<div 
				id="url-<?=$count?>"
				class='iframe-wrap tg'
				<?php if(count($urls) > 1) : ?>style="display:none"<?php endif; ?>
			">
				<div class="alert info" style="margin:0">
					Previewing: <a href="<?= $url["url"]; ?>" target="_blank"><?= $url["url"]; ?></a>
					<!--<a href="<?= $settings_url.$url['channel_id'].'_'.$count ?>" class='settings' target='_blank'>Settings</a>-->
					<a href='#' class='icon add enlarge-iframe'><?php print $this->lang->line('nsm_live_look_tab_enlarge_preview') ?></a>
					<a href='#' class='icon delete shrink-iframe'><?php print $this->lang->line('nsm_live_look_tab_shrink_preview') ?></a>
				</div>
				<iframe
					id="nsm_live_look_<?= $url['channel_id'].'_'.$count ?>"
					style="height:<?= ($url['height'] > 0 ? $url['height'] : 300) ?>px"
					src='<?= $url["url"]; ?>'
				></iframe>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
<!-- End LG Live Look Tab -->
