<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Control Panel (MCP) File for Structure
 *
 * This file must be in your /system/third_party/structure directory of your ExpressionEngine installation
 *
 * @package             Structure for EE2
 * @author              Jack McDade (jack@jackmcdade.com)
 * @author              Travis Schmeisser (travis@rockthenroll.com)
 * @copyright           Copyright (c) 2013 Travis Schmeisser
 * @link                http://buildwithstructure.com
 */


require_once PATH_THIRD.'structure/config.php';
require_once PATH_THIRD.'structure/helper.php';
require_once PATH_THIRD.'structure/sql.structure.php';
require_once PATH_THIRD.'structure/mod.structure.php';


class Structure_mcp
{
	var $debug = FALSE;
	var $logging = FALSE;
	var $version = STRUCTURE_VERSION;
	var $structure;
	var $sql;
	var $perms = array(
		'perm_admin_structure'         => 'Manage module settings',
		'perm_admin_channels'          => 'Manage channel settings',
		'perm_view_global_add_page'    => 'View global "Add page" link above page tree',
		'perm_view_add_page'           => 'View "Add page" link in page tree rows',
		'perm_view_view_page'          => 'View "View page" link in page tree rows',
		'perm_delete'                  => 'Can delete',
		'perm_reorder'                 => 'Can reorder'

	);
	// Enable additional reordering options on a per-level basis
	// Used only in conjunction with per-member group reorder settings
	var $extra_reorder_options = FALSE; // Default: FALSE


	/**
	 * Constructor
	 * @param bool $switch
	 */
	function Structure_mcp($switch = TRUE)
	{
		$this->EE =& get_instance();
		$this->sql = new Sql_structure();
		$this->structure = new Structure();
		$this->site_id = $this->EE->config->item('site_id');

		$this->base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure';

		if ( ! function_exists('json_decode'))
			$this->EE->load->library('Services_json');

		if ($this->logging === TRUE)
			$this->EE->load->library('logger');

		$settings = $this->sql->get_settings();
		$channel_data = $this->structure->get_structure_channels('page');

		$nav = array('Pages' => $this->base_url);

		if ($this->sql->user_access('perm_admin_channels', $settings))
			$nav['Channel Settings'] = $this->base_url.AMP.'method=channel_settings';

		if ($this->sql->user_access('perm_admin_structure', $settings))
			$nav['Module Settings'] = $this->base_url.AMP.'method=module_settings';

		if ($this->debug === TRUE)
			$nav['Debug'] = $this->base_url.AMP.'method=debug';

		$this->EE->cp->set_right_nav($nav);
		$this->EE->cp->add_to_head("<link rel='stylesheet' href='".$this->sql->theme_url() ."css/structure.css'>");
	}

	/**
	 * Main CP page
	 * @param string $message
	 */
	function index($message = FALSE)
	{
		$this->set_cp_title('structure_module_name');

		$settings = $this->sql->get_settings();

		// Load Libraries and Helpers
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('path');
		$this->EE->load->helper('form');

		// Check if we have admin permission
		$permissions = array();
		$permissions['admin'] = $this->sql->user_access('perm_admin_structure', $settings);
		$permissions['view_add_page'] = $this->sql->user_access('perm_view_add_page', $settings);
		$permissions['view_view_page'] = $this->sql->user_access('perm_view_view_page', $settings);
		$permissions['view_global_add_page'] = $this->sql->user_access('perm_view_global_add_page', $settings);
		$permissions['delete'] = $this->sql->user_access('perm_delete', $settings);
		$permissions['reorder'] = $this->sql->user_access('perm_reorder', $settings);

		// Enable/disable dragging and reordering
		// if ((isset($permissions['reorder']) && $permissions['reorder']) || $permissions['admin'])
		$this->EE->cp->load_package_js('jquery.ui.nestedsortable');
		$this->EE->cp->load_package_js('structure-nested');
		$this->EE->cp->load_package_js('structure-actions');
		$this->EE->cp->load_package_js('structure-collapse');

		$site_pages = $this->sql->get_site_pages();
		$data['tabs']              = array('page-ui' => lang('all_pages'));
		$data['data']              = array('page-ui' => $this->sql->get_data());
		$data['valid_channels']    = $this->sql->get_structure_channels('page', '', 'alpha', TRUE);
		$data['listing_cids']      = $this->structure->get_data_cids(TRUE);
		$data['settings']          = $settings;
		$data['member_settings']   = $this->sql->get_member_settings();
		$data['cp_asset_data']     = $this->sql->get_cp_asset_data();
		$data['site_pages']        = count($site_pages > 0) ? $site_pages : array();
		$data['site_uris']         = is_array($data['site_pages']) && array_key_exists('uris', $data['site_pages']) ? $data['site_pages']['uris'] : array();
		$data['asset_path']        = PATH_THIRD.'structure/views/';
		$data['permissions']       = $permissions;
		$data['page_count']        = $this->sql->get_page_count();
		$data['attributes']        = array('class' => 'structure-form', 'id' => 'delete_form');
		$data['status_colors']     = $this->sql->get_status_colors();
		$data['assigned_channels'] = is_array($this->EE->session->userdata('assigned_channels')) ? $this->EE->session->userdata('assigned_channels') : array();
		$data['action_url']        = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=delete';
		$data['theme_url']         = $this->EE->config->item('theme_folder_url') . 'third_party/structure';
		$data['extra_reorder_options']  = $this->extra_reorder_options;
		$data['homepage']          = array_search('/', $site_pages['uris']);

		// -------------------------------------------
		// 'structure_index_view_data' hook.
		// - Used to expand the tree switcher (new tabs and content)
		//
			if ($this->EE->extensions->active_hook('structure_index_view_data') === TRUE)
			{
				$data = $this->EE->extensions->call('structure_index_view_data', $data);
			}
		//
		// -------------------------------------------

		$page_choices = array();
		if (is_array($data['valid_channels']))
			$page_choices = array_intersect_key($data['valid_channels'], $data['assigned_channels']);

		$data['page_choices'] = $page_choices;

		if ($page_choices && count($page_choices == 1))
			$data['add_page_url'] = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.key($page_choices);
		elseif ($data['page_count'] == 0)
			$data['add_page_url'] = $this->base_url.AMP.'method=channel_settings';
		else
			$data['add_page_url'] = '#';

		$add_body = '';
		$add_urls = array();

		$vc_total = count($page_choices);
		$vci = 0;
		if (is_array($page_choices) && count($page_choices) > 0)
		{
			foreach ($page_choices as $key => $channel)
			{
				$vci++;
				$add_url = BASE.AMP.'D=cp'.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$key.AMP.'template_id='.$channel['template_id'];
				$add_urls[] = $add_url;
				$add_body .= '<li';
				$add_body .= $vci == $vc_total ? ' class="last">' : '>';
				$add_body .= '<a href="'.$add_url.'">'.$channel['channel_title'].'</a></li>';
			}
		}
		if ($add_body)
			$add_body = '<ul>' . $add_body . '</ul>';

		$dialogs = array(
			'add' => array(
				'urls' => $add_urls,
				'title' => $this->EE->lang->line('select_page_type'),
				'body' => $add_body,
				'buttons' => array('cancel' => $this->EE->lang->line('cancel'))
			),
			'del' => array(
				'title' => '',
				'body' => $this->EE->lang->line('structure_delete_confirm'),
				'buttons' => array(
					'del' => $this->EE->lang->line('delete_page'),
					'cancel' => $this->EE->lang->line('cancel')
				)
			)
		);

		$settings_array = array(
			'dialogs' => $dialogs,
			'site_id' => $this->EE->config->item('site_id'),
			'xid' => XID_SECURE_HASH,
			'global_add_page' => $settings['show_global_add_page'],
			'show_picker' => $settings['show_picker'],
			'can_reorder' => $permissions['reorder'] ? true : false,
			'admin' => $permissions['admin'] ? true : false
		);

		$settings_json = json_encode($settings_array);

		$this->EE->cp->add_to_head('
		<script type="text/javascript">
			var structure_settings = ' . $settings_json . ';
		</script>');

		if (empty($data['data']['page-ui']))
			return $this->EE->load->view('get_started', $data, TRUE);

		return $this->EE->load->view('index', $data, TRUE);
	}


	function ajax_collapse()
	{
		$closed_ids = json_encode($this->EE->input->get_post('collapsed'));
		$member_id = $this->EE->session->userdata('member_id');
		$site_id = $this->EE->input->get_post('site_id');

		$data = array(
			'site_id' => $site_id,
			'member_id' => $member_id,
			'nav_state' => $closed_ids
		);

		$result = $this->EE->db->get_where('structure_members', array('site_id' => $site_id, 'member_id' => $member_id), 1);

		if ($result->num_rows > 0)
		{
			$this->EE->db->where(array('site_id' => $site_id, 'member_id' => $member_id))->update('structure_members', $data);
		}
		else
		{
			$this->EE->db->insert('structure_members', $data);
		}
		die(json_encode($data, TRUE));
	}


	function link()
	{
		$entry_id = $this->EE->input->get_post('entry_id');
		$site_pages = $this->sql->get_site_pages();

		$url = $this->EE->functions->create_page_url($site_pages['url'], $site_pages['uris'][$entry_id], false);

		redirect($url);
	}


	/**
	 * Reorder Structure Pages
	 *
	 * @return AJAX POST for reordering
	 **/
	function ajax_reorder()
	{
		// Grab the AJAX post
		if (isset($_POST['page-ui']) && is_array($_POST['page-ui']))
		{
			$sortable = $_POST['page-ui'];
		}
		else
		{
			die('no page data');
		}

		if (isset($_GET['site_id']) && is_numeric($_GET['site_id']) && $_GET['site_id'] > 0)
		{
			$site_id = $_GET['site_id'];
		}
		else
		{
			die('no site_id');
		}

		// Convert the array to php
		$data = $this->structure->nestedsortable_to_nestedset($sortable);

		$titles = array();
		$site_pages = $this->sql->get_site_pages(false, true);
		$structure_data = $this->sql->get_data();

		$uris = $site_pages['uris'];

		// Get Page Slugs
		foreach ($uris as $key => $uri)
		{
			$slug = trim($uri, '/');
			if (strpos($slug, '/'))
				$slug = substr(strrchr($slug, '/'), 1);

			if ($uri == "/")
				$slug = $uri;

			@$titles[$key] .= $slug;
		}

		// Build an array with all current channel_ids
		$results = $this->EE->db->query("SELECT entry_id,channel_id FROM exp_channel_data WHERE site_id = $this->site_id");

		$channel_data = array();
		if ($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$channel_data[$row['entry_id']] = $row['channel_id'];
			}
		}

		$row_insert = array();
		$page_uris = array();

		foreach($data as $key => $row)
		{
			$depth = count($row['crumb']);

			$row['site_id'] = $site_id;
			$row['entry_id'] = $entry_id = $row['crumb'][$depth - 1];
			$row['parent_id'] = $depth < 2 ? 0 : $row['crumb'][$depth - 2];
			$row['channel_id'] = $channel_data[$entry_id];
			$row['listing_cid'] = $structure_data[$entry_id]['listing_cid'];
			$row['dead'] = '';
			$row['hidden'] = $structure_data[$entry_id]['hidden'];

			// build URI path for pages
			$uri_titles = array();
			foreach($data[$key]['crumb'] as $entry_id)
			{
				$uri_titles[] = $titles[$entry_id];
			}

			// Remove invalid row fields
			unset($row['depth']);
			unset($row['crumb']);

			// Build pages URI
			$page_uris[$key] = trim(implode('/', $uri_titles), '/');

			// Account for "/" home page
			$page_uris[$key] = $page_uris[$key] == '' ? '/' : '/'.$page_uris[$key];

			// be sanitary
			foreach($row as $field => $value)
			{
				$row[$field] = $this->EE->db->escape_str($value);
			}

			// build insert rows
			$row_insert[] = "('".implode("','", $row)."')";
		}

		// Multi-line insert of all Structure Data
		$sql = "REPLACE INTO exp_structure (".implode(', ', array_keys($row)).") VALUES ".implode(', ', $row_insert);
		$this->EE->db->query($sql);

		// Update Site Pages
		$site_pages['uris'] = $page_uris;

		// Sorting pages blows away the listing data, so all URLs for listing pages
		// are no longer in the site_pages array... lets fix that.
		foreach($site_pages['uris'] as $entry_id => $uri)
		{

			$listing_channel = $this->sql->get_listing_channel($entry_id);

			if ($listing_channel !== FALSE)
			{

				// Retrieve all entries for channel
				$listing_entries = $this->sql->get_channel_listing_entries($listing_channel);

				$channel_entries = $this->EE->db->query("SELECT entry_id, url_title FROM exp_channel_titles WHERE channel_id = $listing_channel AND site_id = $site_id");

				$structure_channels = $this->structure->get_structure_channels();
				$default_template = $structure_channels[$listing_channel]['template_id'];

				$listing_data = array();
				foreach ($channel_entries->result_array() as $c_entry)
				{
					$listing_data[] = array(
						'site_id' => $site_id,
						'channel_id' => $listing_channel,
						'parent_id' => $entry_id,
						'entry_id' => $c_entry['entry_id'],
						'template_id' => $listing_entries[$c_entry['entry_id']]['template_id'] ? $listing_entries[$c_entry['entry_id']]['template_id'] : $default_template,
						'parent_uri' => $site_pages['uris'][$entry_id],
						'uri' => $listing_entries[$c_entry['entry_id']]['uri'] ? $listing_entries[$c_entry['entry_id']]['uri'] : $c_entry['url_title']
					);

					$site_pages['uris'][$c_entry['entry_id']] = $this->structure->create_full_uri($site_pages['uris'][$entry_id], $listing_entries[$c_entry['entry_id']]['uri'] ? $listing_entries[$c_entry['entry_id']]['uri'] : $c_entry['url_title']);
					$site_pages['templates'][$c_entry['entry_id']] = $listing_entries[$c_entry['entry_id']]['template_id'] ? $listing_entries[$c_entry['entry_id']]['template_id'] : $default_template;
				}
				// Update structure_listings table, and site_pages array with proper data
				// $this->structure->set_listings($listing_data);
			}
		}

		// And save this moved page to the array
		$this->structure->set_site_pages($site_id, $site_pages);
		if ($this->logging === TRUE) {
			$this->EE->logger->log_action("Nav Reordered by ".$this->EE->session->userdata('screen_name'));
		}

		// -------------------------------------------
		// 'structure_reorder_end' hook.
		//
		if ($this->EE->extensions->active_hook('structure_reorder_end') === TRUE) {
			$this->EE->extensions->call('structure_reorder_end', $data, $site_pages);
		}
		//
		// -------------------------------------------

		die('Reordered');
	}


	/**
	 * Channel settings page
	 * @param string $message
	 */
	function channel_settings($message = FALSE)
	{
		// Load Libraries and Helpers
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');

		$this->EE->cp->load_package_js('structure-actions');
		$this->EE->cp->load_package_js('structure-forms');

		// Set Breadcrumb and Page Title
		$this->EE->cp->set_breadcrumb($this->base_url, $this->EE->lang->line('structure_module_name'));
		$this->set_cp_title('cp_channel_settings_title');

		$settings = $this->sql->get_settings();

		// Check if we have admin permission
		$permissions = array();
		$permissions['admin'] = $this->sql->user_access('perm_admin_structure', $settings);
		$permissions['view_add_page'] = $this->sql->user_access('perm_view_add_page', $settings);
		$permissions['delete'] = $this->sql->user_access('perm_limited_delete', $settings);
		$permissions['admin_channels']= $this->sql->user_access('perm_admin_channels', $settings);

		// Vars to send into view
		$vars = array();
		$vars['data'] = $this->sql->get_data();
		$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=channel_settings_submit';
		$vars['attributes'] = array('class' => 'structure-form', 'id' => 'structure_settings');
		$vars['channel_data'] = $this->sql->get_structure_channels('','','alpha');
		$vars['are_page_channels'] = $this->sql->get_structure_channels('page','','alpha');
		$vars['page_count'] = $this->sql->get_page_count();
		$vars['templates'] = $this->sql->get_templates();
		$vars['permissions'] = $permissions;
		$vars['channel_check']	= FALSE;
		$vars['valid_channels']    = $this->sql->get_structure_channels('page', '', 'alpha', TRUE);
		$vars['assigned_channels'] = is_array($this->EE->session->userdata('assigned_channels')) ? $this->EE->session->userdata('assigned_channels') : array();

		$page_choices = array();
		if (is_array($vars['valid_channels']))
			$page_choices = array_intersect_key($vars['valid_channels'], $vars['assigned_channels']);

		$vars['page_choices'] = $page_choices;

		if ($page_choices && count($page_choices == 1))
			$vars['add_page_url'] = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.key($page_choices);
		else
			$vars['add_page_url'] = '#';

		$add_body = '';
		$add_urls = array();

		$vc_total = count($vars['valid_channels']);
		$vci = 0;
		if (is_array($vars['valid_channels']) && count($vars['valid_channels']) > 0)
		{
			foreach ($vars['valid_channels'] as $key => $channel)
			{
				$vci++;
				$add_url = BASE.AMP.'D=cp'.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$key.AMP.'template_id='.$channel['template_id'];
				$add_urls[] = $add_url;
				$add_body .= '<li';
				$add_body .= $vci == $vc_total ? ' class="last">' : '>';
				$add_body .= '<a href="'.$add_url.'">'.$channel['channel_title'].'</a></li>';
			}
		}
		if ($add_body)
			$add_body = '<ul>' . $add_body . '</ul>';

		$dialogs = array(
			'add' => array(
				'urls' => $add_urls,
				'title' => $this->EE->lang->line('select_page_type'),
				'body' => $add_body,
				'buttons' => array('cancel' => $this->EE->lang->line('cancel'))
			),
			'del' => array(
				'title' => '',
				'body' => $this->EE->lang->line('structure_delete_confirm'),
				'buttons' => array(
					'del' => $this->EE->lang->line('delete_page'),
					'cancel' => $this->EE->lang->line('cancel')
				)
			)
		);

		$settings_array = array(
			'dialogs' => $dialogs,
			'site_id' => $this->EE->config->item('site_id'),
			'xid' => XID_SECURE_HASH,
			'global_add_page' => $settings['show_global_add_page'],
			'show_picker' => $settings['show_picker'],
		);

		$settings_json = json_encode($settings_array);

		$this->EE->cp->add_to_head('
		<script type="text/javascript">
			var structure_settings = ' . $settings_json . ';
		</script>');

		// Check for ANY channels
		$query = $this->EE->db->query("SELECT channel_id FROM exp_channels WHERE site_id = $this->site_id");
		if ($query->num_rows() > 0)
			$vars['channel_check'] = TRUE;

		return $this->EE->load->view('channel_settings', $vars, TRUE);
	}


	// Process form data from the channel settings area
	function channel_settings_submit()
	{
		$channel_data = $this->sql->get_structure_channels('','','alpha');

		$form_data = array();
		foreach ($_POST as $key => $value)
		{
			$form_data[$key] = array(
				'site_id' => $this->site_id,
				'channel_id' => $key,
				'type' => $value['type'],
				'template_id' => $value['template_id'],
				'split_assets' => isset($value['split_assets']) ? $value['split_assets'] : 'n',
				'show_in_page_selector' => isset($value['show_in_page_selector']) ? $value['show_in_page_selector'] : 'n'
			);
		}

		$channels = array();
		$results = $this->EE->db->get_where('structure_channels', array('site_id' => $this->site_id));
		if ($results->num_rows() > 0)
		{
			foreach ($results->result_array() as $row)
				$channels[$row['channel_id']] = $row;
		}

		$vars = array();
		$to_be_deleted = array();

		// Insert the shiny new data
		foreach($form_data as $key => $data)
		{
			// Update or Insert
			if (count($channels) > 0 && array_key_exists($key, $channels))
			{
				$this->EE->db->where(array('channel_id' => $key, 'site_id' => $this->site_id))->update('structure_channels', $data);
			}
			else
			{
				$this->EE->db->insert('structure_channels', $data);
			}


			// If channel is updated to be 'unmanaged', remove all nodes in that channel
			if ($data['type'] == 'unmanaged' && ($channel_data[$key]['type'] === 'page' || $channel_data[$key]['type'] === 'listing' ))
			{
				$to_be_deleted[] = $data['channel_id'];
			}
				// $this->structure->delete_data_by_channel($key);
		}

		if (count($to_be_deleted) > 0)
		{
			$this->set_cp_title('delete_channels');
			$this->EE->cp->set_breadcrumb($this->base_url, $this->EE->lang->line('structure_module_name'));

			// Get channel titles
			$this->EE->db->select('channel_id, channel_title')->from('channels')->where_in('channel_id', $to_be_deleted);
			$results = $this->EE->db->get();
			$vars['channel_titles'] = $results->result_array();
			$vars['to_be_deleted'] = implode(',',$to_be_deleted);

			$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=delete_channels';
			$vars['attributes'] = array('class' => 'form', 'id' => 'delete_channel');
			$vars['base_url'] = $this->base_url;

			return $this->EE->load->view('delete_channels_confirm', $vars, TRUE);
			// $this->EE->functions->redirect($this->base_url.AMP.'method=delete_channel_confirm'.AMP.'channels='.$ids);
		}

		$this->EE->session->set_flashdata('message_success', "Channel Settings Updated");
		$this->EE->functions->redirect($this->base_url.AMP.'method=channel_settings');
	}

	function delete_channels()
	{
		$channel_ids = explode(',',$this->EE->input->get_post('channel_ids'));

		foreach ($channel_ids as $key => $channel)
		{
			$this->structure->delete_data_by_channel($channel);
		}


		$this->EE->session->set_flashdata('message_success', "Channels Removed Successfully!");
		$this->EE->functions->redirect($this->base_url.AMP.'method=channel_settings');
	}


	/**
	 * Module settings page
	 * @param string $message
	 */
	function module_settings($message = FALSE)
	{
		// Load Libraries and Helpers
		$this->EE->load->library('javascript');
		$this->EE->load->library('table');
		$this->EE->load->helper('form');
		$this->EE->cp->load_package_js('structure-forms');

		$site_id = $this->EE->config->item('site_id');

		$defaults = array(
			'show_picker' 			=> 'y',
			'show_view_page' 		=> 'y',
			'show_status' 			=> 'y',
			'show_page_type' 		=> 'y',
			'show_global_add_page' 	=> 'y',
			'redirect_on_login' 	=> 'n',
			'redirect_on_publish' 	=> 'n',
			'hide_hidden_templates' => 'n',
			'add_trailing_slash' => 'y'
		);

		// Set Breadcrumb and Page Title
		$this->EE->cp->set_breadcrumb($this->base_url, $this->EE->lang->line('structure_module_name'));
		$this->set_cp_title('cp_module_settings_title');

		$settings = $this->sql->get_settings();
		$groups = $this->sql->get_member_groups();

		// Check if we have admin permission
		$permissions = array();
		$permissions['admin'] = $this->sql->user_access('perm_admin_structure', $settings);
		$permissions['reorder'] = $this->sql->user_access('perm_reorder', $settings);
		$permissions['view_add_page'] = $this->sql->user_access('perm_view_add_page', $settings);
		$permissions['delete'] = $this->sql->user_access('perm_limited_delete', $settings);

		// Vars to send into view
		$vars = array();
		$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=module_settings_submit';
		$vars['attributes'] = array('class' => 'structure-form', 'id' => 'module_settings');
		$vars['groups'] = $groups;
		$vars['perms'] = $this->perms;
		$vars['settings'] = $settings;
		$vars['permissions'] = $permissions;
		$vars['extension_is_installed'] = $this->sql->extension_is_installed();
		$vars['redirect_types'] = array(
			'y' => 'All Entries',
			'structure_only' => 'Structure Managed Only',
			'n' => 'No'
		);
		if ($this->extra_reorder_options === TRUE)
		{
			$vars['level_permission_types'] = array(
				'all'       => 'All pages',
				'not_top_1' => 'All but top level',
				'not_top_2' => 'All but top 2 levels',
				'not_top_3' => 'All but top 3 levels',
				'none'      => 'No pages'
			);
		}
		else
		{
			$vars['level_permission_types'] = array(
				'all' => 'All pages',
				'not_top_1' => 'All but top level',
				'none' => 'No pages'
			);
		}

		// Check to make sure all settings have a value
		foreach ($defaults as $key => $default)
		{
			if ( ! isset($vars['settings'][$key]))
			{
				$vars['settings'][$key] = $default;
			}
		}

		return $this->EE->load->view('module_settings', $vars, TRUE);
	}

	// Process form data from the module settings area
	function module_settings_submit()
	{
		$site_id = $this->EE->config->item('site_id');

		// clense current settings out of DB
		$sql = "DELETE FROM exp_structure_settings WHERE site_id = $site_id";
		$this->EE->db->query($sql);

		// insert settings into DB
		foreach ($_POST as $key => $value)
		{
			// Good heavens, this is just plain ghetto. If there is no "perm", it's a "setting"
			// if if there's no "perm" AND it's not a number, then it's a multi-option permission.
			$value = strpos($key, 'perm_') === 0 && is_numeric($value) ? 'y' : $value;
			if ($key !== 'submit')
			{
				$this->EE->db->query($this->EE->db->insert_string(
					"exp_structure_settings",
					array(
						'var'       => $key,
						'var_value' => $value,
						'site_id'   => $site_id
					)
				));
			}
		}

		$this->EE->session->set_flashdata('message_success', "Structure Settings Updated");
		$this->EE->functions->redirect($this->base_url.AMP.'method=module_settings');
	}


	function delete()
	{
	    $ids = $this->EE->input->get_post('toggle');

	    $this->structure->delete_data($ids);

		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');
	}


	/**
	 * Retrieve site path
	 */
	function get_site_path()
	{
		// extract path info
		$site_url_path = parse_url($this->EE->functions->fetch_site_index(), PHP_URL_PATH);

		$path_parts = pathinfo($site_url_path);
		$site_path = $path_parts['dirname'];

		$site_path = str_replace("\\", "/", $site_path);

		return $site_path;
	}


	/**
	 * Temporary debug page to fix some bugs
	 **/
	function debug()
	{
		if ($this->debug === FALSE)
			return FALSE;

		$listing_channels = $this->sql->get_structure_channels('listing');

		foreach ($listing_channels as $channel_id => $row)
		{
			$this->EE->db->where('channel_id', $channel_id);
			$this->EE->db->update('structure_listings', array('site_id' => $this->site_id));
		}

		$vars = array();

		// Set Breadcrumb and Page Title
		$this->EE->cp->set_breadcrumb(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure', $this->EE->lang->line('structure_module_name'));
		$this->set_cp_title('debug');
		$duplicates = $this->sql->cleanup_check();

		$vars['action_url'] = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=debug_submit';
		$vars['attributes'] = array('class' => 'structure-form', 'id' => 'debug');

		$vars['duplicate_entries'] = $duplicates['duplicate_entries'];
		$vars['duplicate_rights'] = $duplicates['duplicate_rights'];
		$vars['duplicate_lefts'] = $duplicates['duplicate_lefts'];

		return $this->EE->load->view('debug', $vars, TRUE);
	}

	// Process form data from the module settings area
	function debug_submit()
	{
		$this->sql->cleanup();
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=debug');
	}

	function listing_site_id_fix()
	{
		$listing_channels = $this->sql->get_structure_channels('listing');

		foreach ($listing_channels as $channel_id => $row)
		{
			$this->EE->db->where('channel_id', $channel_id);
			$this->EE->db->update('structure_listings', array('site_id' => $this->site_id));
		}

		$this->EE->session->set_flashdata('message_success', "Updated!");
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=debug');

	}

	private function set_cp_title($title)
	{
	    if (APP_VER < '2.6.0') {
	        $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line($title));
	    } else {
	        $this->EE->view->cp_page_title = $this->EE->lang->line($title);
	    }
	}
	
	function hook_change_27()
	{
		$appver = str_replace('.','',APP_VER);
		
		
		if ( substr($appver, 0, 2) != "27" )
			{ 
				// We're not actually running a version of 2.7, therefore we should just return an error and silently fail.
				$this->EE->session->set_flashdata('message_failure',lang('not_ee27'));
				
				$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=module_settings');
				
			}
			else
			{
				$fields = array(
					'method'	=> 'channel_form_submit_entry_end',
					'hook'		=> 'channel_form_submit_entry_end',
				);
				
				$this->EE->db->where("class","Structure_ext")
							->where("hook","safecracker_submit_entry_end")
							->update("extensions",$fields);
				
				$this->EE->session->set_flashdata('message_success',lang('ee27_hook_complete'));
				
				$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure'.AMP.'method=module_settings');
				
			}
		
		
	}




}
/* END Class */

/* End of file mcp.structure.php */
/* Location: ./system/expressionengine/third_party/structure/mcp.structure.php */
