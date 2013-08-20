<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_images/config'.EXT;

/**
 * Channel Images Module Extension File
 *
 * @package			DevDemon_ChannelRatings
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Channel_images_ext
{
	public $version			= CHANNEL_IMAGES_VERSION;
	public $name			= 'Channel Images Extension';
	public $description		= 'Supports the Channel Images Module in various functions.';
	public $docs_url		= 'http://www.devdemon.com';
	public $settings_exist	= FALSE;
	public $settings		= array();
	public $hooks			= array('wygwam_config');

	// ********************************************************************************* //

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
	}

	// ********************************************************************************* //

	/**
	 * This hook will enable you to override your Wygwam fields’ CKEditor config settings right on page load, taking your Wygwam customizations to a whole new level.
	 *
	 * @param array $config The array of config settings that are about to be JSON-ified and sent to CKEditor during field initialization.
	 * @param array $settings The full array of your field’s settings, as they were before being translated into the $config array.
	 * @access public
	 * @see http://pixelandtonic.com/wygwam/docs/wygwam_config
	 * @return array
	 */
	public function wygwam_config($config, $settings)
	{
		// Check if we're not the only one using this hook
		if($this->EE->extensions->last_call !== FALSE)
		{
			$config = $this->EE->extensions->last_call;
		}

		// Check just to be sure!
		if (isset($config['extraPlugins']) != FALSE)
		{
			$config['extraPlugins'] .= ',channelimages';
			$config['toolbar'][] = array('ChannelImages');
		}

		return $config;
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine when the user activates the extension.
	 *
	 * @access		public
	 * @return		void
	 **/
	public function activate_extension()
	{
		foreach ($this->hooks as $hook)
		{
			 $data = array(	'class'		=>	__CLASS__,
			 				'method'	=>	$hook,
							'hook'      =>	$hook,
							'settings'	=>	serialize($this->settings),
							'priority'	=>	100,
							'version'	=>	$this->version,
							'enabled'	=>	'y'
      			);

			// insert in database
			$this->EE->db->insert('exp_extensions', $data);
		}
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine when the user disables the extension.
	 *
	 * @access		public
	 * @return		void
	 **/
	public function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('exp_extensions');
	}

	// ********************************************************************************* //

	/**
	 * Called by ExpressionEngine updates the extension
	 *
	 * @access public
	 * @return void
	 **/
	public function update_extension($current=FALSE)
	{
		if($current == $this->version) return false;

		// Update the extension
		$this->EE->db
			->where('class', __CLASS__)
			->update('extensions', array('version' => $this->version));

	}

	// ********************************************************************************* //

} // END CLASS

/* End of file ext.channel_images.php */
/* Location: ./system/expressionengine/third_party/channel_images/ext.channel_images.php */