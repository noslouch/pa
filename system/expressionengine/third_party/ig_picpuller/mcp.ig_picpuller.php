<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include (PATH_THIRD.'ig_picpuller/config.php');

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
 * ig_picpuller Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		John Morton
 * @link		http://picpuller.com
 */

class Ig_picpuller_mcp {

	public $return_data;

	private $_base_url;

	// $_currentSite will identify whatever is the current site in the control panel for use in cases where MSM is being used.
	private $_currentSite;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$this->_currentSite = $this->EE->config->config['site_id'];

		$this->_currentAppId = $this->getCurrentAppId();

		$this->_the_server = $_SERVER['HTTP_HOST'];

		$this->_base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ig_picpuller';

		$this->EE->load->library('session');

		if ( $this->isSuperAdmin() ) {

		$this->EE->cp->set_right_nav(array(
			'ig_set_up'	=> $this->_base_url,
			'ig_info' => $this->_base_url.AMP.'method=ig_info',
			'ig_users' => $this->_base_url.AMP.'method=ig_users',
			'ig_all_app_info' => $this->_base_url.AMP.'method=ig_all_app_info',
			'ig_advanced_menu' => $this->_base_url.AMP.'method=ig_advanced_menu'
			// Add more right nav items here in needed
		));
		} else {

			// ==============================================================
			// = A non-SuperAdmin doesn't get to see the rest of the module =
			// = so only the link to the home page of the module is here.   =
			// ==============================================================

			$this->EE->cp->set_right_nav(array(
				'ig_set_up'	=> $this->_base_url
				// Add more right nav items here in needed
			));
		}

		// set the name of the CP title
		if (function_exists('ee')) {
			$this->EE->view->cp_page_title = lang('ig_picpuller_module_name');
		} else {
			$this->EE->cp->set_variable('cp_page_title', lang('ig_picpuller_module_name'));
		}
	}

	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{

		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');
		$vars['site_label'] = $this->getSiteLabel();

		$vars['adv_menu_url'] = $this->_base_url.'&method=ig_advanced_menu';
		$vars['ig_advanced_menu'] = lang('ig_advanced_menu');
		$vars['ig_adv_user_auth'] = lang('ig_adv_user_auth');
		$vars['adv_user_url'] = $this->_base_url.'&method=adv_user_admin';

		$baseURLpattern = '/(?:https?:\/\/)?(?:www\.)?([a-zA-Z0-9\.\-]*\/)/';;

		preg_match($baseURLpattern, $this->EE->config->config['base_url'], $current_base_url);
		$current_base_url = $current_base_url[1];

		preg_match($baseURLpattern, $this->EE->config->config['cp_url'], $current_cp_url);
		$current_cp_url = $current_cp_url[1];
		/*
		if ($current_cp_url === $current_base_url){
			//$vars['debugger'] = $this->EE->config->config['base_url'] . ' ??? ' . $current_base_url[0];//$this->_currentSite;
			$vars['debugger'] = 'Yes, you can validate successfully here.';
		} else {
			//
			$vars['debugger'] =  'No, you can\'t validate successfully here. ' . $current_base_url;
		}
		*/
		$vars['ableToAuthorizeFromThisURL'] = ($current_cp_url === $current_base_url);
		$vars['frontend_auth_url'] = $this->getFrontEndAuth();

		if ($this->appAuthorized()) {
			$vars['delete_method'] = $string = $this->_base_url.'&method=removeAuthorization';

			return $this->EE->load->view('authorized', $vars, TRUE);
		}

		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		// Help the user figure out the oAuth redirect URL

		$vars['full_auth_url'] = $this->getRedirectURL();

		$vars['redirect_url'] = $this->getRedirectURL(true);

		$action_id = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->cp->fetch_action_id('ig_picpuller', 'authorization');

		$vars['action_id'] = $action_id;

		/*
		FUTURE:
		Use JS to help user construct EE code for each tag pair.

		$this->EE->javascript->output(array(
				'// add my own jQuery here.'
				)
		);
		$this->EE->javascript->compile();
		*/

		$vars['form_hidden'] = NULL;
		$vars['options'] = array(
						'edit'  => lang('edit_selected'),
						'delete'    => lang('delete_selected')
						);

		if ( $this->appExistsInDb() )
		{
			// app exists in DB so the Action URL is the Redirect URL for authroizing an app

			$vars['preexisting_app'] = TRUE;
			$vars['action_url'] = $this->getRedirectURL();
			$vars['clientID'] = $this->getClientID();

		}
		else
		{
			// no app exists in DB so the Action URL is to save setting to the DB

			$vars['preexisting_app'] = FALSE;
			$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ig_picpuller'.AMP.'method=save_settings';
		}

		if ( $this->isSuperAdmin() ) {
			return $this->EE->load->view('index', $vars, TRUE);
		}
		else
		{
			return $this->EE->load->view('index_nonsuperadmin', $vars, TRUE);
		}
	}

	/**
	 * Display info within the control panel about the **current** Instagram app in Pic Puller
	 * @return a view "ig_about"
	 */
	public function ig_info()
	{
		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');
		$vars['site_label'] = $this->getSiteLabel();
		$vars['client_id'] = $this->getClientID();
		$vars['client_secret'] = $this->getSecret();
		$vars['frontend_auth_url'] = $this->getFrontEndAuth();
		$vars['ig_picpuller_prefix'] = $this->get_ig_picpuller_prefix();
		// The URLs to edit the app
		$vars['delete_method'] = $this->_base_url.'&method=preview_delete_app';
		$vars['edit_secret'] = $this->_base_url.'&method=edit_secret';
		$vars['edit_prefix'] = $this->_base_url.'&method=edit_prefix';
		$vars['edit_frontend_url'] = $this->_base_url.'&method=edit_frontend_url';
		return $this->EE->load->view('ig_about', $vars, TRUE);
	}

	/**
	 * Display info within the control panel about **all** Instagram apps in Pic Puller
	 * @return a view "ig_about_all_apps"
	 */

	public function ig_all_app_info()
	{
		$this->EE->db->select('site_id,app_id,ig_client_id,ig_client_secret,site_label,ig_picpuller_prefix');
		$this->EE->db->from('ig_picpuller_credentials');
		$this->EE->db->join('sites', 'ig_picpuller_credentials.ig_site_id = sites.site_id');
		$query = $this->EE->db->get();

		// echo "<pre>";
		// print_r( $query->result() );
		// echo "</pre>";

		$site_ids = array();
		$app_ids = array();
		$site_labels = array();
		$client_ids = array();
		$client_secrets = array();
		$ig_picpuller_prefixs = array();

		foreach ($query->result() as $row)
		{
			array_push($site_ids, $row->site_id);
			array_push($app_ids, $row->app_id);
			array_push($site_labels, $row->site_label);
			array_push($client_ids, $row->ig_client_id);
			array_push($client_secrets, $row->ig_client_secret);
			array_push($ig_picpuller_prefixs, $row->ig_picpuller_prefix);
		}

		$vars['site_ids'] = $site_ids;
		$vars['app_ids'] = $app_ids;
		$vars['site_labels'] = $site_labels;
		$vars['client_ids']	= $client_ids;
		$vars['client_secrets']	= $client_secrets;
		$vars['ig_picpuller_prefixs'] = $ig_picpuller_prefixs;
		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');

		$vars['app_info_link'] = $this->_base_url.'&method=ig_info';
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['current_site_id'] = $this->_currentSite;

		return $this->EE->load->view('ig_about_all_apps', $vars, TRUE);
	}

	/**
	 * Display the ADVANCED menu
	 * @return   a view "ig_advanced"
	 */

	public function ig_advanced_menu()
	{
		$vars['site_label'] = $this->getSiteLabel();
		$vars['adv_user_url'] = $this->_base_url.'&method=adv_user_admin';
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';

		// menu names from lang file
		$vars['ig_advanced_menu'] = lang('ig_advanced_menu');
		$vars['ig_adv_user_auth'] = lang('ig_adv_user_auth');


		return $this->EE->load->view('ig_advanced', $vars, TRUE);
	}


	/**
	 * Display users within the control panel of users who have authorized Pic Puller to talk to their Instagram account
	 * @return a view "ig_users"
	 */
	public function ig_users()
	{
		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');
		$vars['site_label'] = $this->getSiteLabel();
		$member_ids= array();
		$screen_names= array();
		$oauths= array();

		// don't run the db query is there is no app define b/c there will be no users for an undefined app
		if ($this-> appExistsInDb() ) {
			$vars['appexists'] =  TRUE;
			$this->EE->db->select('ig_picpuller_oauths.member_id, screen_name, oauth');
			$this->EE->db->where('app_id', $this->_currentAppId );
			$this->EE->db->from('ig_picpuller_oauths');

			$this->EE->db->join('members', 'ig_picpuller_oauths.member_id = members.member_id');
			$query = $this->EE->db->get();


			foreach ($query->result() as $row)
			{
				array_push($member_ids, $row->member_id);
				array_push($screen_names, $row->screen_name);
				array_push($oauths, $row->oauth);
			}

		} else {
			$vars['appexists'] =  FALSE;
		}

		$vars['member_ids'] = $member_ids;
		$vars['screen_names'] = $screen_names;
		$vars['oauths']	= $oauths;

		return $this->EE->load->view('ig_users', $vars, TRUE);
	}

	/**
	 * Display a view that will let user update the secret (aka password) of their Instram App
	 * @return a view "ig_secret_update"
	 */
	public function edit_secret()
	{
		$vars['site_label'] = $this->getSiteLabel();
		$vars['client_id'] = $this->getClientID();
		$vars['client_secret'] = $this->getSecret();
		$vars['form_hidden'] = NULL;
		$vars['update_secret_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ig_picpuller'.AMP.'method=edit_secret_step2';
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';

		return $this->EE->load->view('ig_secret_update', $vars, TRUE);
	}

	/**
	 * Update the secret (aka password) in the database
	 * @return a view "save_settings"
	 */
	public function edit_secret_step2()
	{
		$vars['site_label'] = $this->getSiteLabel();
		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');
		$this->EE->cp->set_variable('cp_page_title', lang('ig_picpuller_module_name'));
		$ig_client_id = $this->getClientID();
		$ig_client_secret = $this->EE->input->post('ig_client_secret', TRUE);
		$data = array(
			'ig_client_secret' => $ig_client_secret
		);

		$this->EE->db->where('ig_client_id', $ig_client_id);
		$this->EE->db->update('ig_picpuller_credentials', $data);

		$vars['ig_client_secret'] = $ig_client_secret;
		$vars['client_id'] = $ig_client_id;
		$vars['client_secret'] = $ig_client_secret;
		$vars['frontend_auth_url'] = $this->getFrontEndAuth();
		$vars['ig_picpuller_prefix'] =$this->get_ig_picpuller_prefix();
		$vars['homeurl'] = $this->_base_url;
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';
		return $this->EE->load->view('update_settings_confirmation', $vars, TRUE);
	}

	/**
	 * Display a view that will let user update the secret (aka password) of their Instram App
	 * @return a view "ig_secret_update"
	 */
	public function edit_prefix()
	{
		$vars['site_label'] = $this->getSiteLabel();
		$vars['client_id'] = $this->getClientID();
		$vars['client_secret'] = $this->getSecret();
		$vars['ig_picpuller_prefix'] = $this->get_ig_picpuller_prefix();
		$vars['form_hidden'] = NULL;
		$vars['update_secret_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ig_picpuller'.AMP.'method=edit_prefix_step2';
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';

		return $this->EE->load->view('ig_prefix_update', $vars, TRUE);
	}

	/**
	 * Update the secret (aka password) in the database
	 * @return a view "save_settings"
	 */
	public function edit_prefix_step2()
	{
		$vars['site_label'] = $this->getSiteLabel();
		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');
		$this->EE->cp->set_variable('cp_page_title', lang('ig_picpuller_module_name'));
		$ig_client_id = $this->getClientID();
		$ig_picpuller_prefix = $this->EE->input->post('ig_picpuller_prefix', TRUE);
		$data = array(
			'ig_picpuller_prefix' => $ig_picpuller_prefix
		);

		$this->EE->db->where('ig_client_id', $ig_client_id);
		$this->EE->db->update('ig_picpuller_credentials', $data);

		$vars['client_id'] = $ig_client_id;
		$vars['client_secret'] = $this->getSecret();
		$vars['frontend_auth_url'] = $this->getFrontEndAuth();
		$vars['ig_picpuller_prefix'] =$ig_picpuller_prefix;
		$vars['homeurl'] = $this->_base_url;
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';
		return $this->EE->load->view('update_settings_confirmation', $vars, TRUE);
	}

	/**
	 * Display a view that will let user update the secret (aka password) of their Instram App
	 * @return a view "ig_secret_update"
	 */
	public function edit_frontend_url()
	{
		$vars['site_label'] = $this->getSiteLabel();
		$vars['client_id'] = $this->getClientID();
		$vars['client_secret'] = $this->getSecret();
		$vars['frontend_auth_url'] = $this->getFrontEndAuth();
		$vars['form_hidden'] = NULL;
		$vars['update_frontend_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ig_picpuller'.AMP.'method=edit_frontend_url_step2';
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';

		return $this->EE->load->view('ig_frontedurl_update', $vars, TRUE);
	}

	//ig_frontedurl_update

	/**
	 * Update the secret (aka password) in the database
	 * @return a view "save_settings"
	 */
	public function edit_frontend_url_step2()
	{
		$vars['site_label'] = $this->getSiteLabel();
		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');
		$this->EE->cp->set_variable('cp_page_title', lang('ig_picpuller_module_name'));
		$ig_client_id = $this->getClientID();

		$frontend_auth_url = $this->EE->input->post('frontend_auth_url', TRUE);
		$data = array(
			'frontend_auth_url' => $frontend_auth_url
		);

		$this->EE->db->where('ig_client_id', $ig_client_id);
		$this->EE->db->update('ig_picpuller_credentials', $data);

		$vars['client_id'] = $ig_client_id;
		$vars['client_secret'] = $this->getSecret();
		$vars['frontend_auth_url'] = $this->getFrontEndAuth();
		$vars['ig_picpuller_prefix'] =$this->get_ig_picpuller_prefix();
		$vars['homeurl'] = $this->_base_url;
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';
		return $this->EE->load->view('update_settings_confirmation', $vars, TRUE);
	}

	/**
	 * Save a user's Instagram Client ID and Client Secret into the EE database
	 * @return a view "save_settings"
	 */
	public function save_settings()
	{
		// in this function save the client ID and the client secret for the user created application

		// NOTES:
		//
		// table: ig_picpuller_credentials
		//
		// fields:
		//  ig_client_id
		//  ig_client_secret
		//  ig_picpuller_prefix

		$default_prefix = 'ig_';

		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');
		$vars['app_info_link'] = $this->_base_url.'&method=ig_info';
		$vars['edit_tab_name'] =  $this->EE->lang->line('ig_info');

		$this->EE->cp->set_variable('cp_page_title', lang('ig_picpuller_module_name'));
		$ig_client_id = $this->EE->input->post('ig_client_id', TRUE);
		$ig_client_secret = $this->EE->input->post('ig_client_secret', TRUE);

		// Update new settings
		// NO - cant empty table now $this->EE->db->empty_table('ig_picpuller_credentials');
		$this->EE->db->set('ig_client_id', $ig_client_id);
		$this->EE->db->set('ig_client_secret', $ig_client_secret);
		$this->EE->db->set('ig_picpuller_prefix', $default_prefix);
		$this->EE->db->set('ig_site_id', $this->_currentSite);
		$this->EE->db->set('auth_url', $this->getRedirectURL() );
		$this->EE->db->insert('ig_picpuller_credentials');

		$vars['client_id'] = $ig_client_id;
		$vars['client_secret'] = $ig_client_secret;

		$vars['homeurl'] = $this->_base_url;
		return $this->EE->load->view('save_settings', $vars, TRUE);
	}

	/**
	 * First step, a warning, when a user attempts to delete their Instagram App
	 * @return a view "ig_about_delete_confirmation"
	 */
	public function preview_delete_app()
	{
		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');

		$vars['client_id'] = $this->getClientID();

		$vars['delete_method'] = $this->_base_url.'&method=delete_app';
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';

		$vars['site_label'] = $this->getSiteLabel();

		return $this->EE->load->view('ig_about_delete_confirmation', $vars, TRUE);
	}

	/**
	 * Second step when a user attempts to delete their Instagram App. This DOES the actual deletion
	 * @return a view - the index , aka, the home set up page for Pic Puller
	 */
	public function delete_app()
	{
		/// only SuperAdmins can delete the app
		if ( $this->isSuperAdmin() ) {
			$appID = $this->getCurrentAppId();

			$this->EE->db->delete('ig_picpuller_credentials', array('app_id' => $appID));
			$this->EE->db->delete('ig_picpuller_oauths', array('app_id' => $appID));

			// return to the top level of Pic Puller
			return $this->index();
		}
	}

	/**
	 * Remove a single user's authorization from the EE database for the Instagram App
	 * @return a view "authorized_removed"
	 */
	public function removeAuthorization()
	{
		$appID = $this->getCurrentAppId();

		$vars['moduleTitle'] = lang('ig_picpuller_module_name');
		$vars['moduleShortTitle'] = lang('ig_picpuller_short_module_name');

		$this->EE->db->select('*');
		$this->EE->db->limit('1');
		$this->EE->db->where('member_id', $this->getLoggedInUserId() );
		$this->EE->db->where('app_id', $appID );
		$this->EE->db->delete('ig_picpuller_oauths');
		return $this->EE->load->view('authorized_removed', $vars, TRUE);
	}

	/**
	 * ADVANCED: User Admin
	 */

	public function adv_user_admin()
	{
		$vars['app_id'] = $this->_currentAppId;
		$vars['site_label'] = $this->getSiteLabel();
		$vars['ig_adv_user_auth'] = lang('ig_adv_user_auth');
		$vars['ig_advanced_menu'] = lang('ig_advanced_menu');
		$authURL = $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->cp->fetch_action_id('ig_picpuller', 'authorization');
		$vars['alt_url'] = 'https://instagram.com/oauth/authorize/?client_id='.$this->getClientID().'&redirect_uri='.$authURL.'&response_type=token';

		$vars['update_user_info_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=ig_picpuller'.AMP.'method=save_user_info_adv';
		$vars['ig_info_name'] =  $this->EE->lang->line('ig_info');
		$vars['cancel_url'] = $this->_base_url.'&method=ig_info';
		$vars['form_hidden'] = NULL;
		$vars['setup_link'] = $this->_base_url;

		//  getting current user info if present

		$this->EE->db->select('instagram_id,oauth');
		$this->EE->db->from('ig_picpuller_oauths');
		$this->EE->db->where('ig_picpuller_oauths.app_id', $this->_currentAppId );
		$this->EE->db->where('member_id', $this->getLoggedInUserId());
		$this->EE->db->limit(1);
		$query = $this->EE->db->get();

		 // 4echo "<pre>";
		 // 4print_r( $query->result() );
		 // 4echo "</pre>";

		foreach ($query->result() as $row)
		{
			$vars['ig_user_id'] = $row->instagram_id;
			$vars['ig_user_oauth'] = $row->oauth;
		}

		if (!isset($vars['ig_user_id'])){
			$vars['ig_user_id'] ='';
		}

		if (!isset($vars['ig_user_oauth'])){
			$vars['ig_user_oauth'] ='';
		}

		return $this->EE->load->view('ig_advanced_user_admin', $vars, TRUE);
	}

	public function save_user_info_adv() {


		$ig_user_id = $this->EE->input->post('ig_user_id', TRUE);
		$ig_user_oauth = $this->EE->input->post('ig_user_oauth', TRUE);

		$this->remove_auth_logged_in_user();
		//echo "ig_user_id: ".$ig_user_id . ' and ig_user_oauth: '. $ig_user_oauth;

		$this->EE->db->set('oauth', $ig_user_oauth);
		$this->EE->db->set('instagram_id', $ig_user_id);
		$this->EE->db->set('member_id', $this->getLoggedInUserId());
		$this->EE->db->set('app_id', $this->_currentAppId);
		$this->EE->db->insert('ig_picpuller_oauths');


		// return to the top level of Pic Puller
		return $this->index();
	}

	/**
	 * Remove Authorization of Logged In User
	 *
	 * Remove the logged in users oAuth credentials from the database
	 *
	 * @access	private
	 * @return	NULL
	 */

	private function remove_auth_logged_in_user()
	{
		// TO DO : remove the select * - not needed, but want to test first, so I've left it in for this version.

		$this->EE->db->select('*');
		$this->EE->db->limit('1');
		$this->EE->db->where('member_id', $this->getLoggedInUserId() );
		$this->EE->db->where('app_id', $this->_currentAppId);
		$this->EE->db->delete('ig_picpuller_oauths');
	}

	// HELPER FUNCTIONS BELOW

	private function getClientID()
	{
		$this->EE->db->select('ig_client_id');
		$this->EE->db->where('ig_site_id', $this->_currentSite);
		$this->EE->db->limit(1);
		$query = $this->EE->db->get('ig_picpuller_credentials');

		foreach ($query->result() as $row)
		{
			$ig_client_id = $row->ig_client_id;
		}
		if (isset($ig_client_id)){
			return $ig_client_id;
		} else {
			return;
		}
	}

	/**
	 * Get ig_picpuller_prefix
	 *
	 * Get the prefix from the Pic Puller Credentials table for the existing Pic Puller application.
	 * This prefix will be used for all tags for this application.
	 *
	 * @access	private
	 * @return	mixed - returns a string, the prefix, if available in DB, or an empty string if unavailable
	 */

	private function get_ig_picpuller_prefix()
	{
		$this->EE->db->select('ig_picpuller_prefix');
		$this->EE->db->where('ig_site_id', $this->_currentSite);
		$this->EE->db->limit(1);
		$query = $this->EE->db->get('ig_picpuller_credentials');

		foreach ($query->result() as $row)
		{
			$ig_picpuller_prefix = $row->ig_picpuller_prefix;
		}
		if (isset($ig_picpuller_prefix)){
			return $ig_picpuller_prefix;
		} else {
			return '';
		}
	}

	private function getSecret()
	{
		$this->EE->db->select('ig_client_secret');
		$this->EE->db->where('ig_site_id', $this->_currentSite);
		$this->EE->db->limit(1);
		$query = $this->EE->db->get('ig_picpuller_credentials');

		foreach ($query->result() as $row)
		{
			$ig_client_secret = $row->ig_client_secret;
		}
		if (isset($ig_client_secret)){
			return $ig_client_secret;
		} else {
			return;
		}
	}

	private function getFrontEndAuth()
	{
		$this->EE->db->select('frontend_auth_url');
		$this->EE->db->where('ig_site_id', $this->_currentSite);
		$this->EE->db->limit(1);
		$query = $this->EE->db->get('ig_picpuller_credentials');

		foreach ($query->result() as $row)
		{
			$frontend_auth_url = $row->frontend_auth_url;
		}
		if (isset($frontend_auth_url)){
			return $frontend_auth_url;
		} else {
			return;
		}
	}

	private function getRedirectURL($urlEncoded = false)
	{
		return $this->EE->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$this->EE->cp->fetch_action_id('ig_picpuller', 'authorization');

	}

	private function getCurrentAppId()
	{

		$this->EE->db->select('app_id');
		$this->EE->db->where('ig_site_id', $this->_currentSite);
		$this->EE->db->limit(1);
		$this->EE->db->from('ig_picpuller_credentials');

		$query = $this->EE->db->get();

		foreach ($query->result() as $row)
		{
			$current_app_id = $row->app_id;
		}

		// echo '<pre>';
		// echo $current_app_id;
		// echo '</pre>';


		if (isset($current_app_id)){
			return $current_app_id;
		} else {
			return false;
		}
	}

	private function getSiteLabel()
	{

		$this->EE->db->select('site_label, site_id');
		$this->EE->db->where('site_id', $this->_currentSite);
		$this->EE->db->limit(1);
		$this->EE->db->from('sites');

		$query = $this->EE->db->get();

		foreach ($query->result() as $row)
		{
			$site_label = $row->site_label;
		}

		if (isset($site_label)){
			return $site_label;
		} else {
			return false;
		}
	}

	private function appAuthorized()
	{
		/// NEED TO CHECK that we're talking about the current CP site
		// $this->EE->config->config['site_id']

		$this->EE->db->select('oauth');
		$this->EE->db->where('member_id', $this->getLoggedInUserId() );
		$this->EE->db->join('ig_picpuller_credentials', 'ig_picpuller_credentials.app_id = ig_picpuller_oauths.app_id');
		$this->EE->db->where('ig_picpuller_credentials.ig_site_id', $this->_currentSite);
		$this->EE->db->limit('1');
		$this->EE->db->from('ig_picpuller_oauths');
		$query = $this->EE->db->get();

		if ($query->num_rows() == 1)
		{
			return true;
		} else {
			return false;
		}
	}

	private function appExistsInDb()
	{

		// is there an application already defined in the database?
		$this->EE->db->select('*');
		$this->EE->db->where('ig_site_id', $this->_currentSite );
		$this->EE->db->limit('1');
		$this->EE->db->from('ig_picpuller_credentials');
		$query = $this->EE->db->get();

		if ($query->num_rows() == 0)
		{
			return false;
		} else {
			return true;
		}
	}

	private function getLoggedInUserId()
	{
		$this->EE->load->library('session');
		return $this->EE->session->userdata('member_id');
	}

	private function isSuperAdmin()
	{
		if ($this->EE->session->userdata['group_id'] === '1' )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}

	}

}
/* End of file mcp.ig_picpuller.php */
/* Location: /system/expressionengine/third_party/ig_picpuller/mcp.ig_picpuller.php */