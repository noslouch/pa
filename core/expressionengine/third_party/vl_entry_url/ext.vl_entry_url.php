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
 * VL Entry URL Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Trevor Davis
 * @link		http://viget.com
 */

class Vl_entry_url_ext {

	public $settings 		= array();
	public $description		= 'Display the current entry url on the publish form.';
	public $docs_url		= '';
	public $name			= 'VL Entry URL';
	public $settings_exist	= 'y';
	public $version			= '1.3';

	private $EE;

	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->_fetch_channels();
		$this->settings = $settings;
	}

	// ----------------------------------------------------------------------

	/**
	 * Settings Form
	 *
	 * @param   Array   Settings
	 * @return  void
	 */
	function settings_form($current)
	{
	    $this->EE->load->helper('form');
	    $this->EE->load->library('table');

	    $vars = array();

		if (isset($current)) {
			$vars['settings'] = $current;
		}

		foreach ($this->channels AS $key => $val) {
			$vars['channels'][$key] = $val;
		}

		// print_r($vars);

	    return $this->EE->load->view('index', $vars, TRUE);
	}

	// ----------------------------------------------------------------------

	/**
	 * Get all channels
	 *
	 * @return void
	 */
	private function _fetch_channels()
	{
		$query = $this->EE->db->select('channel_id, channel_title, channel_url')
								->from('channels')
								->where('site_id', $this->EE->config->item('site_id'))
								->order_by('channel_title')
								->get();

		$this->channels = $query->result_array();
		$query->free_result();
	}

	// ----------------------------------------------------------------------

	/**
	 * Save Settings
	 *
	 * This function provides a little extra processing and validation
	 * than the generic settings form.
	 *
	 * @return void
	 */
	function save_settings()
	{
		if (empty($_POST)) {
			show_error($this->EE->lang->line('unauthorized_access'));
		}

		$data = array();

		foreach ($this->channels AS $key => $val) {
			$channel_id = $val['channel_id'];

			$data[$channel_id] = array(
				'url' => $this->EE->input->post('channel_' . $channel_id . '_url'),
			);
		}

		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('settings' => serialize($data)));

		$this->EE->session->set_flashdata(
			'message_success',
			$this->EE->lang->line('preferences_updated')
		);
	}

	// ----------------------------------------------------------------------

	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();

		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'dummy',
			'hook'		=> 'entry_submission_absolute_end',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);
	}

	// ----------------------------------------------------------------------

	/**
	 * Don't do anything, just want the extension for saving settings.
	 *
	 * @param
	 * @return void
	 */
	public function dummy()
	{

	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}

	// ----------------------------------------------------------------------
}

/* End of file ext.vl_entry_url.php */
/* Location: /system/expressionengine/third_party/vl_entry_url/ext.vl_entry_url.php */