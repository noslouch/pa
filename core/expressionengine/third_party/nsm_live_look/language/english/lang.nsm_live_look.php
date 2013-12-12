<?php

/**
 * Language file for NSM Live Look
 *
 * @package			NsmLiveLook
 * @version			1.2.4
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2013 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */

$lang = array(

	'nsm_live_look'							=> 'NSM Live Look',
	"nsm_live_look_title" 					=> "NSM Live Look",
	'nsm_live_look_module_name'				=> 'NSM Live Look',
	'nsm_live_look_module_description'		=> 'Live preview on the publish page',
	
	'nsm_live_look_save_extension_settings'		=> 'Save extension settings',

	'nsm_live_look_channel_preferences_title'	=> 'Channel preferences',
	'nsm_live_look_channel_preferences_info'	=> "<p>Each channel can have one or more entry preview URLs. This URL is used to display the live page preview inside the publish tab <code>&lt;iframe&gt;</code>. Each preview URL is made up of the following attributes:</p>
												<ul class='tag-list'>
													<li>{url_title}</li>
													<li>{entry_id}</li>
													<li>{channel_id}</li>
													<li>{title}</li>
													<li>{author_id}</li>
													<li>{status}</li>
													<li>{entry_date_day}</li>
													<li>{entry_date_month}</li>
													<li>{entry_date_year}</li>
													<li>{dst_enabled}</li>
													<li>{comment_total}</li>
													<li>{username}</li>
													<li>{email}</li>
													<li>{screen_name}</li>
												</ul>
												<p>'Use page URL?' will override the 'Preview URL' if an entry is a 'page'.",	
	
	'nsm_live_look_ext_ch_prefs_preview_url'	=> 'Preview URL',
	'nsm_live_look_ext_ch_prefs_height'			=> 'Default Height (px)',
	'nsm_live_look_ext_ch_prefs_use_page_url'	=> 'Use Page URL?',
	
	'nsm_live_look_tab_shrink_preview'						=> 'Shrink Preview',
	'nsm_live_look_tab_enlarge_preview'						=> 'Enlarge Preview',
	
	'nsm_live_look_alert.error.no_channels'					=> 'No channels exists for this site',
	'nsm_live_look_alert.error.entry_unsaved'					=> 'Entry must be saved before you can preview it',
	'nsm_live_look_alert.error.no_preview_urls'				=> 'No preview URLS exist for this channel',
	'nsm_live_look_alert.success.extension_settings_saved' 	=> 'Extension settings have been saved.',
	
	'nsm_live_look.error.no_morphine' => '<p class="alert error"><strong>NSM Morphine Theme could not be found</strong>. Please ensure that you have downloaded the <a href="http://ee-garage.com/nsm-morphine#download">latest version</a> and have <a href="http://ee-garage.com/nsm-morphine/user-guide#toc-installation_activation">activated the accessory</a>.</p>',
	
	// END
	''=>''
    
);
