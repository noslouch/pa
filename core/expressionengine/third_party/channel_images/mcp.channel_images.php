<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images Control Panel Class
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Channel_images_mcp
{
	/**
	 * Views Data
	 * @var array
	 * @access private
	 */
	private $vData = array();

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		// Load Models & Libraries & Helpers
		$this->EE->load->library('image_helper');

		// Some Globals
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_images';
		$this->base_short = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_images';
		$this->base_cp = BASE;

		$this->vData = array(); // Global Views Data Array
		$this->vData['base_url'] = $this->base;
		$this->vData['base_url_short'] = $this->base_short;
		$this->vData['base_cp'] = $this->base_cp;

		$this->EE->image_helper->define_theme_url();

		$this->mcp_globals();

		$this->site_id = $this->EE->config->item('site_id');

		if (function_exists('ee')) {
			ee()->view->cp_page_title = $this->EE->lang->line('channel_images');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('channel_images'));
		}


		// Debug
		//$this->EE->db->save_queries = TRUE;
		//$this->EE->output->enable_profiler(TRUE);
	}

	// ********************************************************************************* //

	public function index()
	{
		return $this->batch_actions();
	}

	// ********************************************************************************* //

	public function batch_actions()
	{
		// Page Title & BreadCumbs
		$this->vData['section'] = 'actions';

		// -----------------------------------------
		// Grab all channels
		// -----------------------------------------
		$this->vData['channels'] = array();

		$this->EE->db->select('channel_id, channel_title');
		$this->EE->db->from('exp_channels');
		$this->EE->db->where('site_id', $this->site_id);
		$query = $this->EE->db->get();

		foreach ($query->result() as $row)
		{
			$this->vData['channels'][$row->channel_id] = $row->channel_title;
		}

		// -----------------------------------------
		// Grab all fields
		// -----------------------------------------
		$this->vData['fields'] = array();

		$this->EE->db->select('cf.field_id, cf.field_label, fg.group_name');
		$this->EE->db->from('exp_channel_fields cf');
		$this->EE->db->where('cf.site_id', $this->site_id);
		$this->EE->db->where('cf.field_type', 'channel_images');
		$this->EE->db->join('exp_field_groups fg', 'fg.group_id = cf.group_id', 'left');
		$query = $this->EE->db->get('');
		foreach ($query->result() as $row) $this->vData['fields'][$row->group_name][$row->field_id] = $row->field_label;

		return $this->EE->load->view('mcp/actions', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function legacy_settings()
	{
		// Page Title & BreadCumbs
		$this->vData['section'] = 'actions';

		if (function_exists('ee')) {
			ee()->view->cp_page_title = $this->EE->lang->line('ci:legacy_settings');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ci:legacy_settings'));
		}

		$this->EE->load->helper('path');

		// Channels
		$this->vData['channels'] = array();
		$this->EE->db->select('channel_id, channel_title');
		$this->EE->db->where('site_id', $this->site_id);
		$query = $this->EE->db->get('exp_channels');
		foreach ($query->result() as $row) $this->vData['channels'][$row->channel_id] = $row->channel_title;

		// Settings
		$this->EE->db->select('settings');
		$this->EE->db->where('module_name', 'Channel_images');
		$query = $this->EE->db->get('exp_modules');
		$this->vData['settings'] = unserialize( $query->row('settings') );
		$this->vData['settings'] = (isset($this->vData['settings']['site_id:'.$this->site_id]) == TRUE) ? $this->vData['settings']['site_id:'.$this->site_id] : array( 'channels' => array() );

		return $this->EE->load->view('mcp_legacy_settings', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function import()
	{
		// TODO: the import script should have inserted our place holder in the custom_field so that conditional would work
		// Page Title & BreadCumbs

		$this->vData['section'] = 'import';
		if (function_exists('ee')) {
			ee()->view->cp_page_title = $this->EE->lang->line('ci:import');
		} else {
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('ci:import'));
		}

		$this->EE->image_helper->mcp_js_css('js', 'channel_images_mcp.js?v='.CHANNEL_IMAGES_VERSION, 'channel_images', 'mcp_old');
		$this->EE->image_helper->mcp_js_css('css', 'channel_images_mcp.css?v='.CHANNEL_IMAGES_VERSION, 'channel_images', 'mcp_old');

		$this->vData['matrix'] = array();

		// -----------------------------------------
		// Grab all matrix fields
		// -----------------------------------------
		$this->EE->db->select('cf.field_label, cf.field_id, cf.group_id, fg.group_name');
		$this->EE->db->from('exp_channel_fields cf');
		$this->EE->db->join('exp_field_groups fg', 'cf.group_id = fg.group_id', 'left');
		$this->EE->db->where('cf.field_type', 'matrix');
		$this->EE->db->order_by('cf.field_label', 'ASC');
		$query = $this->EE->db->get();

		foreach($query->result() as $row)
		{
			// Grab all channel image fields whithin that field group
			$q2 = $this->EE->db->select('field_id, field_label')->from('exp_channel_fields')->where('group_id', $row->group_id)->where('field_type', 'channel_images')->get();

			// Grab ll matrix columns
			$q3 = $this->EE->db->select('col_id, col_label')->from('exp_matrix_cols')->where('field_id', $row->field_id)->order_by('col_order', 'ASC')->get();

			// Grab channel id's
			$q4 = $this->EE->db->select('channel_id')->from('exp_channels')->where('field_group', $row->group_id)->get();

			// Grab all entry ids
			$q5 = $this->EE->db->select('entry_id')->from('exp_matrix_data')->where('field_id', $row->field_id)->group_by('entry_id')->get();

			$matrix = array();
			$matrix['field_label'] = $row->field_label;
			$matrix['field_id'] = $row->field_id;
			$matrix['group_label'] = $row->group_name;
			$matrix['channel_id'] = $q4->row('channel_id');
			$matrix['cols'] = $q3->result();
			$matrix['ci_fields'] = $q2->result();
			$matrix['entries'] = $q5->result();


			$this->vData['matrix'][] = $matrix;
		}



		//print_r($this->vData['matrix']);



		return $this->EE->load->view('mcp_import', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function mcp_globals()
	{
		$this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('channel_images'));

		// Add Global JS & CSS & JS Scripts
		$this->EE->image_helper->mcp_js_css('gjs');
		$this->EE->image_helper->mcp_js_css('css', 'css/select2.css', 'select2', 'main');
		$this->EE->image_helper->mcp_js_css('css', 'css/mcp_fts.css?v='.CHANNEL_IMAGES_VERSION, 'channel_images', 'main');
		$this->EE->image_helper->mcp_js_css('js', 'js/select2.min.js', 'select2', 'main');
		$this->EE->image_helper->mcp_js_css('js', 'js/handlebars.runtime-1.0.0.min.js', 'handlebars', 'runtime');
		$this->EE->image_helper->mcp_js_css('js', 'js/hbs-templates.js?v='.CHANNEL_IMAGES_VERSION, 'channel_images', 'templates');
		$this->EE->image_helper->mcp_js_css('js', 'js/mcp.min.js?v='.CHANNEL_IMAGES_VERSION, 'channel_images', 'main');
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file mcp.channel_images.php */
/* Location: ./system/expressionengine/third_party/channel_images/mcp.channel_images.php */
