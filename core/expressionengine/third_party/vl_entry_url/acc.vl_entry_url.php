<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * VL Entry URL Accessory
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Accessory
 * @author		Trevor Davis
 * @link		http://viget.com
 */

class Vl_entry_url_acc {

	public $name			= 'VL Entry URL';
	public $id				= 'vl_entry_url';
	public $version			= '1.3';
	public $description		= 'Display the current entry url on the publish form.';
	public $sections		= array();

	private $EE;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	// ----------------------------------------------------------------

	/**
	 * Set Sections
	 */
	public function set_sections()
	{
		$theme_folder_url = defined('URL_THIRD_THEMES') ? URL_THIRD_THEMES : $this->EE->config->slash_item('theme_folder_url').'third_party/';

		//Are we on the entry form?
		if ($this->EE->input->get('C') === 'content_publish' && $this->EE->input->get('M') === 'entry_form') {
			$channel_id = $this->EE->input->get('channel_id');
			$site_url = $this->EE->config->item('site_url');

			if ($channel_id) {
				//Get the extension settings
				$query = $this->EE->db->select('settings')
								->from('extensions')
								->where('class', 'Vl_entry_url_ext')
								->get();

				if ($query->num_rows() > 0) {
					$result = $query->row();
					$settings = unserialize($result->settings);

					if(array_key_exists($channel_id, $settings)) {
						$channel_pattern = $settings[$channel_id]['url'];

						//If we have a pattern defined for this channel, append the JS and CSS
						if ($channel_pattern) {
							$this->EE->cp->add_to_head($this->_js($channel_pattern, $site_url));
							$this->EE->cp->add_to_head('<script src="'. $theme_folder_url . $this->id . '/scripts/' . $this->id .'.js"></script>');
							$this->EE->cp->add_to_head($this->_css());

							// Get the Structure tree
							if (class_exists('Structure')) {
								$sql = new Sql_structure();
								$pages = $sql->get_site_pages();
								$uris = json_encode($pages['uris']);

								$this->EE->cp->add_to_head('<script>' . $this->id . '.structureTree = '. $uris . ';</script>');
							}
						}
					}
				}
			}
		}

		//Remove the tab
		$this->sections[] = '<script type="text/javascript">$("#accessoryTabs a.' . $this->id . '").parent().remove();</script>';
	}

	// ----------------------------------------------------------------

	/**
	 * Create a JS object to access in the addon JS
	 */
	private function _js($channel_pattern = '', $site_url = '')
	{
		return '<script>
					var ' . $this->id . ' = {
						pattern: "' . $channel_pattern . '",
						base: "' . $site_url . '"
					};
				</script>';
	}

	// ----------------------------------------------------------------

	/**
	 * Style the input
	 */
	private function _css()
	{
		return '<style type="text/css">
					#entry-url {
						background: none;
						border: none;
						color: #27343c;
						float: right;
						font-size: 15px;
						line-height: 19px;
						margin-right: 50px;
						padding: 7px 0;
						position: relative;
						text-align: right;
						text-shadow: 0 1px 0 rgba(255, 255, 255, 0.3);
						width: 65%;
						z-index: 2;
					}
				</style>';
	}

	// ----------------------------------------------------------------

}

/* End of file acc.vl_entry_url.php */
/* Location: /system/expressionengine/third_party/vl_entry_url/acc.vl_entry_url.php */