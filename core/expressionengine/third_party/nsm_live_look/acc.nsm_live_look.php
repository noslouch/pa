<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NSM Live Look Accessory
 *
 * @package			NsmLiveLook
 * @version			1.2.4
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @copyright 		All rights reserved <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 * @link			http://ee-garage.com/live-look
 * @see				http://expressionengine.com/docs/development/accessories.html
 */

class Nsm_live_look_acc 
{
	var $id;
	var $version		= '1.2.4';
	var $name			= 'NSM Live Look';
	var $description	= 'NSM Live Look preview accessory';
	var $sections		= array();

	public function set_sections()
	{
		$EE =& get_instance();

		$this->addon_id = substr(strtolower(__CLASS__),0,-4);
		$this->id = $this->addon_id;
		$this->name = "Live Look";

		$entry_id = $EE->input->get('entry_id');
		$channel_id = $EE->input->get('channel_id');

		$output = '';

		if(
			$entry_id && $channel_id
			&& ($EE->input->get('C') == 'content_publish' && $EE->input->get('M') == 'view_entry'))
		{
			$EE->lang->loadfile($this->addon_id);
			$settings = $this->_getExtensionSettings();

			// check if there is an urls for this channel
			if(isset($settings['channels']) && array_key_exists($channel_id, $settings['channels'])) {
				$EE->load->library('api');
				$EE->api->instantiate('channel_fields');
				$EE->api_channel_fields->include_handler('nsm_live_look');
				$EE->api_channel_fields->setup_handler('nsm_live_look');
				$output = $EE->api_channel_fields->apply('display_field', array('data' => false));

				//$EE->nsm_live_look_helper->addCSS('admin.css');
				//$EE->nsm_live_look_helper->addJS('admin.js');

				$output .= '<script type="text/javascript" charset="utf-8">
								$("#nsm_live_look .cf").prependTo(".pageContents");
							</script>';
			}

		}

		$output .= '<script type="text/javascript" charset="utf-8">$("#accessoryTabs a.nsm_live_look").parent().remove();</script>';

		$this->sections[''] = $output;
	}


	/**
	 * Get the extension settings by creating a new extension class
	 * 
	 * @access private
	 * @return array the extension settings
	 */
	private function _getExtensionSettings()
	{
		if(!class_exists('Nsm_live_look_ext'))
			require(PATH_THIRD."{$this->addon_id}/ext.{$this->addon_id}.php");
		$ext = new Nsm_live_look_ext();
		$settings = $ext->settings;
		return $settings;
	}
}
