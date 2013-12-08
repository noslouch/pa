<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extension for Structure
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

class Structure_ext {

	var $name			= STRUCTURE_NAME;
	var $version 		= STRUCTURE_VERSION;
	var $docs_url		= STRUCTURE_DOCS;
	var $description	= 'Enable some nice Structure-friendly control panel features';
	var $settings 		= array();
	var $settings_exist	= 'n';


	function Structure_ext($settings = '')
	{
		$this->EE =& get_instance();
		$this->sql = new Sql_structure();
		$this->site_pages = $this->sql->get_site_pages();

		if ( ! $this->sql->module_is_installed() || ! is_array($this->site_pages))
			return FALSE;

		$this->entry_id = FALSE;
		$this->parent_id = FALSE;
	}


	function sessions_start($ee)
	{
		if ((REQ == 'PAGE' || REQ == 'ACTION') && array_key_exists('uris', $this->site_pages) && is_array($this->site_pages['uris']) && count($this->site_pages['uris']) > 0)
		{
			// -------------------------------------------
			//  Sanitize the URL for pagination and other bypasses
			// -------------------------------------------
			// $this->_create_clean_structure_segments();

			// -------------------------------------------
			//  Set all other class variables
			// -------------------------------------------

			$this->uri = $this->sql->get_uri();


			// Make sure there is Structure data
			if (array_key_exists('uris', $this->site_pages) && is_array($this->site_pages['uris']) && count($this->site_pages['uris']) > 0)
			{
				$settings = $this->sql->get_settings();
				$trailing_slash = isset($settings['add_trailing_slash']) && $settings['add_trailing_slash'] === 'y' ? '/' : null;

				$this->entry_id = array_search($this->uri, $this->site_pages['uris']);
				$this->parent_id = $this->sql->get_parent_id($this->entry_id);
				$this->segment_1 = $this->EE->uri->segment(1) ? '/'.$this->EE->uri->segment(1) : FALSE;

				$this->top_id = array_search($this->segment_1.$trailing_slash, $this->site_pages['uris']);
			}

			// -------------------------------------------
			//  Create all Structure global variabes
			// -------------------------------------------

			$this->_create_global_vars();

		}
	}


	function entry_submission_redirect($entry_id, $meta, $data, $cp_call, $orig_loc)
	{
		$settings = $this->sql->get_settings();
		if ($cp_call === TRUE && isset($settings['redirect_on_publish']) && $settings['redirect_on_publish'] != 'n')
		{
			if ($settings['redirect_on_publish'] == 'y')
				return BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure';

			if ($settings['redirect_on_publish'] == 'structure_only')
			{
				$ci = $this->EE->db->get_where('structure_channels', array('channel_id' => $meta['channel_id']));
				if($ci->num_rows() > 0 && $ci->row('type') != 'unmanaged')
					return BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure';
			}

		}

		if ($this->EE->extensions->last_call !== FALSE)
		{
			$orig_loc = $this->EE->extensions->last_call;
		}

		return $orig_loc;

	}


	function cp_member_login()
	{
		$settings = $this->sql->get_settings();

		if (isset($settings['redirect_on_login']) && $settings['redirect_on_login'] == 'y')
		{

			if (APP_VER < '2.6.0') {
				$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');
			} else {

				// Yay, workaround for EE 2.6.0 session bug
				$s = 0;
				switch ($this->EE->config->item('admin_session_type')){
					case 's':
						$s = $this->EE->session->userdata('session_id', 0);
						break;
					case 'cs':
						$s = $this->EE->session->userdata('fingerprint', 0);
						break;
				}
				$base = SELF.'?S='.$s.'&amp;D=cp';

				$this->EE->functions->redirect($base.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=structure');
			}
		}
	}

	function _is_search()
	{
		$qstring = $this->EE->uri->query_string;
		$string_array = explode("/",$qstring);

		$search_id_key = count($string_array)-2;
		$search_id = array_key_exists($search_id_key, $string_array) ? $string_array[$search_id_key] : FALSE;

		if ($search_id !== FALSE)
		{
			$query = $this->EE->db->get_where('modules', array('module_name' => 'Search'));
			if ($query->num_rows() > 0)
			{
				// Fetch the cached search query
				$query = $this->EE->db->get_where('search', array('search_id' => $search_id));

				// if ($query->num_rows() > 0 || $query->row('total_results') > 0)
				if (count($query->result_array()) > 0 && ($query->num_rows() > 0 || $query->row('total_results') > 0))
					return TRUE;
			}
		}

		return FALSE;
	}


	private function _create_clean_structure_segments()
	{

		// Create pagination_segment and last_segment
		$segment_count = $this->EE->uri->total_segments();
		$last_segment = $this->EE->uri->segment($segment_count);

		// Check for pagination
		$pagination_segment = FALSE;
		if (preg_match("/^P\d/", $last_segment) && $this->_is_search() === FALSE)
		{
			$pagination_segment = $segment_count;
			$pagination_page = substr($last_segment,1);

			$this->EE->config->_global_vars['structure_pagination_segment'] = $pagination_segment; // {structure_pagination_segment}
			$this->EE->config->_global_vars['structure_pagination_page'] = $pagination_page; // {structure_pagination_page}
			$this->EE->config->_global_vars['structure_last_segment'] = $last_segment; // {structure_last_segment}

			// Clean and dirty laundry, thanks to Freebie's cleverness
			$clean_array	= array();
			$dirty_array	= explode('/', $this->EE->uri->uri_string);

			// move any segments that don't match patterns to clean array
			foreach ($dirty_array as $segment)
			{
				if ($pagination_segment !== FALSE && $segment != 'P'.$pagination_page)
				{
					array_push($clean_array, $segment);
				}
			}

			// -------------------------------------------
			//  Clean up and overwrite the URI vars
			// -------------------------------------------

			// Rewrite the uri_string
			if (count($clean_array) != 0)
			{
				$clean_string = '/'.implode('/', $clean_array);

				if (array_search($clean_string, $this->site_pages['uris']))
				{
					$this->EE->uri->uri_string = $clean_string;

					$this->EE->config->_global_vars['structure_debug_uri_cleaned'] = $this->EE->uri->uri_string;

					$this->EE->uri->segments = array();
					$this->EE->uri->rsegments = array();
					$this->EE->uri->_explode_segments();

					// Load the router class
					$RTR =& load_class('Router', 'core');
					$RTR->_parse_routes();

					// re-index the segments
					$this->EE->uri->_reindex_segments();
				}
			}
		}
	}


	private function _create_global_vars()
	{
		$settings = $this->sql->get_settings();

		$trailing_slash = isset($settings['add_trailing_slash']) && $settings['add_trailing_slash'] === 'y' ? '/' : null;

		// utility global vars
		$this->EE->config->_global_vars['structure:is:page']           = $this->entry_id !== FALSE && $this->sql->is_listing_entry($this->entry_id) !== TRUE ? TRUE : FALSE;
		$this->EE->config->_global_vars['structure:is:listing']        = $this->sql->is_listing_entry($this->entry_id);
		$this->EE->config->_global_vars['structure:is:listing:parent'] = $this->sql->get_listing_channel($this->entry_id) !== FALSE && $this->sql->is_listing_entry($this->entry_id) === FALSE ? TRUE : FALSE;

		// current page global vars
		$this->EE->config->_global_vars['structure:page:entry_id']           = $this->entry_id !== FALSE ? $this->entry_id : FALSE; // {page:entry_id}
		$this->EE->config->_global_vars['structure:page:template_id']        = $this->entry_id !== FALSE ? $this->site_pages['templates'][$this->entry_id] : FALSE; // {page:template_id}
		$this->EE->config->_global_vars['structure:page:title']              = $this->entry_id !== FALSE ? $this->sql->get_page_title($this->entry_id) : FALSE; // {page:title}
		$this->EE->config->_global_vars['structure:page:slug']               = $this->entry_id !== FALSE ? $this->EE->uri->segment($this->EE->uri->total_segments()) : FALSE;
		$this->EE->config->_global_vars['structure:page:uri']                = $this->entry_id !== FALSE ? $this->uri : FALSE;
		$this->EE->config->_global_vars['structure:page:url']                = $this->entry_id !== FALSE ? Structure_Helper::remove_double_slashes($this->site_pages['url'] . $this->EE->config->_global_vars['structure:page:uri']) : FALSE; // {page:url}
		$this->EE->config->_global_vars['structure:page:channel']            = $this->entry_id !== FALSE ? $this->sql->get_channel_by_entry_id($this->entry_id) : FALSE; // {page:channel}
		$this->EE->config->_global_vars['structure:page:channel_short_name'] = $this->entry_id !== FALSE ?  $this->sql->get_channel_name_by_channel_id($this->EE->config->_global_vars['structure:page:channel']) : FALSE; // {page:channel_short_name}

		// parent page global vars
		$this->EE->config->_global_vars['structure:parent:entry_id']           = $this->parent_id !== FALSE ? $this->parent_id : FALSE; // {page:entry_id}
		$this->EE->config->_global_vars['structure:parent:title']              = $this->parent_id !== FALSE ? $this->sql->get_page_title($this->parent_id) : FALSE; // {page:title}
		$this->EE->config->_global_vars['structure:parent:slug']               = $this->parent_id !== FALSE ? $this->EE->uri->segment($this->EE->uri->total_segments() - 1)  : FALSE; // {parent:slug}
		$this->EE->config->_global_vars['structure:parent:uri']                = $this->parent_id !== FALSE && isset($this->site_pages['uris'][$this->parent_id]) ? $this->site_pages['uris'][$this->parent_id]  : FALSE; // {parent:relative_url}
		$this->EE->config->_global_vars['structure:parent:url']                = $this->parent_id !== FALSE && $this->EE->config->_global_vars['structure:parent:uri'] !== FALSE ? Structure_Helper::remove_double_slashes($this->site_pages['url'] . $this->EE->config->_global_vars['structure:parent:uri'])  : FALSE; // {parent:url}
		$this->EE->config->_global_vars['structure:parent:child_ids']          = $this->parent_id !== FALSE && $this->EE->uri->segment(2) ? implode('|',$this->sql->get_child_entries($this->parent_id)) : FALSE;
		$this->EE->config->_global_vars['structure:parent:channel']            = $this->parent_id !== FALSE ? $this->sql->get_channel_by_entry_id($this->parent_id) : FALSE; // {page:channel}
		$this->EE->config->_global_vars['structure:parent:channel_short_name'] = $this->parent_id !== FALSE ?  $this->sql->get_channel_name_by_channel_id($this->EE->config->_global_vars['structure:parent:channel']) : FALSE; // {page:channel_short_name}

		// top page global vars
		$this->EE->config->_global_vars['structure:top:entry_id']      = $this->segment_1 !== FALSE ? $this->top_id : FALSE; // {top:entry_id}
		$this->EE->config->_global_vars['structure:top:title']         = $this->segment_1 !== FALSE ? $this->sql->get_page_title($this->top_id) : FALSE; // {top:title}
		$this->EE->config->_global_vars['structure:top:slug']          = $this->segment_1 !== FALSE ? $this->EE->uri->segment(1) : FALSE; // {top:slug}
		$this->EE->config->_global_vars['structure:top:uri']           = $this->segment_1 !== FALSE ? '/'.$this->EE->uri->segment(1).$trailing_slash : FALSE; // {top:relative_url}
		$this->EE->config->_global_vars['structure:top:url']           = $this->segment_1 !== FALSE ? Structure_Helper::remove_double_slashes($this->site_pages['url'].$this->EE->uri->segment(1).$trailing_slash)  : FALSE; // {top:url}

		// listing global vars
		$this->EE->config->_global_vars['structure:child_listing:channel_id'] = $this->sql->get_listing_channel($this->entry_id) !== FALSE && is_numeric($this->entry_id)? $this->sql->get_listing_channel($this->entry_id) : FALSE;
		$this->EE->config->_global_vars['structure:child_listing:short_name'] = $this->sql->get_listing_channel($this->entry_id) !== FALSE && is_numeric($this->entry_id)? $this->sql->get_listing_channel_short_name($this->EE->config->_global_vars['structure:child_listing:channel_id']) : FALSE;

		// freebie
		$this->EE->config->_global_vars['structure:freebie:entry_id'] = isset($this->EE->config->_global_vars['freebie_debug_uri']) ? array_search('/'.$this->EE->config->_global_vars['freebie_debug_uri'], $this->site_pages['uris']) : FALSE;

		// child global var
		$child_ids = $this->sql->get_child_entries($this->entry_id);
		$this->EE->config->_global_vars['structure:child_ids'] = is_array($child_ids) && count($child_ids > 0) ? implode('|', $child_ids) : false;

		// sibling global var
		$sibling_ids = array_diff($this->sql->get_child_entries($this->parent_id), array($this->entry_id));
		$this->EE->config->_global_vars['structure:sibling_ids'] = is_array($sibling_ids) && count($sibling_ids > 0) ? implode('|', $sibling_ids) : false;



		// structure_segment global vars
		$segments = array_pad($this->EE->uri->segments, 10, '');
		for ($i = 1; $i <= count($segments); $i++)
		{
			$this->EE->config->_global_vars['structure_'.$i] = $segments[$i - 1]; // {structure_X}
		}

		$segment_count = $this->EE->uri->total_segments();
		$last_segment = $this->EE->uri->segment($segment_count);
		$this->EE->config->_global_vars['structure_last_segment'] = $last_segment; // {structure_last_segment}

	}


	function channel_module_create_pagination($ee_obj)
	{

		if (version_compare(APP_VER, 2.4, '<'))
		{
			if ($this->_is_search() === FALSE && isset($this->EE->config->_global_vars['structure_pagination_segment']))
			{
				$ee_obj->EE->uri->uri_string = $ee_obj->EE->uri->uri_string . "/P" . $this->EE->config->_global_vars['structure_pagination_page'];
				$ee_obj->p_page = $this->EE->config->_global_vars['structure_pagination_page'];
			}
		}
		else
		{
			$segment_array = explode('/', $this->EE->uri->uri_string);
			$segment_count = count($segment_array);
			$last_segment = $segment_array[$segment_count-1];

			unset($segment_array[$segment_count -1]);
			$new_basepath = Structure_Helper::remove_double_slashes('/'.implode('/', $segment_array));

			if (preg_match('/P\d+/', $last_segment))
			{
				$ee_obj->offset = substr($last_segment,1);
				$ee_obj->basepath = $this->site_pages['url'].$new_basepath;
			}
		}

	}

	function core_template_route($uri_string)
	{
		$segment_array = explode('/', $uri_string);
		$segment_count = count($segment_array);
		$last_segment = $segment_array[$segment_count-1];

		if (preg_match('/P\d+/', $last_segment))
		{
			$settings = $this->sql->get_settings();
			unset($segment_array[$segment_count-1]);
			$trailing_slash = isset($settings['add_trailing_slash']) && $settings['add_trailing_slash'] === 'y' ? '/' : null;

			$new_uri_string = Structure_Helper::remove_double_slashes('/'.implode('/', $segment_array).$trailing_slash);

			$entry_id = array_search($new_uri_string, $this->site_pages['uris']);

			if ($entry_id)
			{
				$template_id = $this->site_pages['templates'][$entry_id];

				$this->EE->db->select('*');
				$this->EE->db->from('templates');
				$this->EE->db->join('template_groups', 'templates.group_id = template_groups.group_id');
				$this->EE->db->where('templates.template_id', $template_id);

				$result = $this->EE->db->get();
				if ($result->num_rows() > 0)
				{
					$row = $result->row();
					$this->EE->uri->page_query_string = $entry_id;

					return array($row->group_name, $row->template_name);
				}
			}
		}
	}


	/**
	* wygwam_config hook
	*/
	function wygwam_config($config, $settings)
	{
		// If another extension shares the same hook,
		// we need to get the latest and greatest config
		if ($this->EE->extensions->last_call !== FALSE)
			$config = $this->EE->extensions->last_call;


		// get EE's record of site pages
		$site_pages = $this->EE->config->item('site_pages');
		$site_id = $this->EE->config->item('site_id');

		if (is_array($site_pages))
		{
			$pages = $this->sql->get_data();
			foreach ($pages as $entry_id => $page_data)
			{
				// ignore if EE doesn't have a record of this page
				if ( ! isset($site_pages[$site_id]['uris'][$entry_id])) continue;

				// add this page to the config
				$config['link_types']['Structure Pages'][] = array(
					'label' => $page_data['title'],
					'label_depth' => $page_data['depth'],
					'url' => $this->EE->functions->create_page_url($site_pages[$site_id]['url'], $site_pages[$site_id]['uris'][$entry_id], false)
				);
			}

			$listing_channels = $this->sql->get_structure_channels('listing');

			if ($listing_channels !== FALSE)
			{
				foreach ($listing_channels as $channel => $row)
				{
					$entries = $this->sql->get_entry_titles_by_channel($row['channel_id']);
					foreach ($entries as $page_data)
					{
						// ignore if EE doesn't have a record of this page
						if ( ! isset($site_pages[$site_id]['uris'][$page_data['entry_id']])) continue;

						$config['link_types']['Structure Listing: ' . $row['channel_title']][] = array(
							'label' => $page_data['title'],
							'label_depth' => 0,
							'url' => $this->EE->functions->create_page_url($site_pages[$site_id]['url'], $site_pages[$site_id]['uris'][$page_data['entry_id']], false)
						);
					}
				}
			}

		}

		return $config;
	}


	function entry_submission_end($entry_id, $meta, $data)
	{
		// if (REQ == 'CP')
		// 	return;

		// $channel_id = $meta['channel_id'];
		// $channel_type = $this->sql->get_channel_type($channel_id);

		// // If the current channel is not assigned as any sort of Structure channel, then stop
		// if ($channel_type == 'page' || $channel_type == 'listing')
		// {
		// 	$site_pages = $this->sql->get_site_pages();

		// 	// get form fields
		// 	$entry_data = array(
		// 		'channel_id'	=> $channel_id,
		// 		'entry_id'		=> $entry_id,
		// 		'uri'			=> array_key_exists('structure_uri', $data) ? $data['structure_uri'] : $meta['url_title'],
		// 		'template_id'	=> array_key_exists('structure_template', $data) ? $data['structure_template'] : $this->sql->get_default_template($channel_id),
		// 		'listing_cid'	=> 0,
		// 		'hidden' 		=> array_key_exists('structure_hidden', $data) && $data['structure_hidden'] == 'y' ? 'y' : 'n'
		// 	);

		// 	if ($channel_type == 'listing')
		// 	{
		// 		$entry_data['parent_id'] = $this->sql->get_listing_parent($channel_id);
		// 		$entry_data['listing_cid'] = $this->sql->get_listing_channel($entry_data['parent_id']);
		// 		$entry_data['uri'] = $this->sql->create_uri($entry_data['uri'], $meta['url_title']);
		// 		$entry_data['parent_uri'] = $site_pages['uris'][$entry_data['parent_id']];

		// 		$this->sql->set_listing_data($entry_data);
		// 	}
		// 	else // page
		// 	{
		// 		$entry_data['parent_id'] = array_key_exists('structure_parent_id', $data) ? $data['structure_parent_id'] : 0;
		// 		$parent_uri = isset($site_pages['uris'][$entry_data['parent_id']]) ? $site_pages['uris'][$entry_data['parent_id']] : '/';
		// 		$entry_data['uri'] = $this->sql->create_page_uri($parent_uri, $entry_data['uri']);
		// 		$entry_data['listing_cid'] = $this->sql->get_listing_channel_by_id($entry_id) ? $this->sql->get_listing_channel_by_id($entry_id) : 0;

		// 		require_once PATH_THIRD.'structure/mod.structure.php';
		//         $this->structure = new Structure();

		// 		$this->structure->set_data($entry_data, true);
		// 	}
		// }
	}
	
	// For 2.7 Compatibility
	function channel_form_submit_entry_end($obj)
	{
		$this->EE->load->helper('url');

		// The constants in this safecracker game.
		$channel_id       = $obj->channel['channel_id'];
		$channel_type     = $this->sql->get_channel_type($channel_id);

		
		if ($channel_type == NULL) return;
		
		// If we're not working with Structure data, let's kill this quickly.
		if ( ! isset($obj->entry['entry_id']) && ($channel_type != 'page' || $channel_type != 'listing')) {
			return;
		}
		
		// These may not always be available so putting them *after* the conditional
		$entry_id         = $obj->entry['entry_id'];
		

		// This defaults to false if not a listing entry
		$listing_entry = $this->sql->get_listing_entry($entry_id);

		/*
		|-------------------------------------------------------------------------
		| Template ID
		|-------------------------------------------------------------------------
		*/
		$default_template = $listing_entry ? $listing_entry['template_id'] : $this->sql->get_default_template($channel_id);

		$template_id = pick(
			array_get($obj->entry, 'structure_template_id'),
			array_get($this->site_pages['templates'], $entry_id)
		);

		if ( ! $this->sql->is_valid_template($template_id)) {
			$template_id = $default_template;
		}

		/*
		|-------------------------------------------------------------------------
		| URI
		|-------------------------------------------------------------------------
		*/
		$default_uri = $listing_entry ? array_get($listing_entry, 'uri') : array_get($this->site_pages['uris'], $entry_id);

		$uri = Structure_Helper::tidy_url(
			pick(
				array_get($obj->entry, 'structure_uri'),
				Structure_Helper::get_slug($default_uri),
				$obj->entry['url_title']
			)
		);

		/*
		|-------------------------------------------------------------------------
		| Parent ID
		|-------------------------------------------------------------------------
		*/
		$default_parent_id = $channel_type == 'listing' ? $this->sql->get_listing_parent($channel_id) : 0;

		$parent_id = pick(
			array_get($obj->entry, 'structure_parent_id'),
			$this->sql->get_parent_id($entry_id, null),
			$default_parent_id
		);

		/*
		|-------------------------------------------------------------------------
		| Parent URI
		|-------------------------------------------------------------------------
		*/
		$parent_uri  = array_get($this->site_pages['uris'], $parent_id, '/');

		/*
		|-------------------------------------------------------------------------
		| URL
		|-------------------------------------------------------------------------
		*/
		$url = $channel_type == 'listing' ? $uri : $this->sql->create_full_uri($parent_uri, $uri);

		/*
		|-------------------------------------------------------------------------
		| Listing Channel ID
		|-------------------------------------------------------------------------
		*/
		$listing_cid = $this->sql->get_listing_channel($parent_id);

		/*
		|-------------------------------------------------------------------------
		| Hidden State
		|-------------------------------------------------------------------------
		*/
		$hidden = pick(
			array_get($obj->entry, 'structure_hidden'),
			$this->sql->get_hidden_state($entry_id),
			'n'
		);

		/*
		|-------------------------------------------------------------------------
		| Entry data to be processed and saved
		|-------------------------------------------------------------------------
		*/
		$entry_data = array(
			'channel_id'  => $channel_id,
			'entry_id'    => $entry_id,
			'uri'         => $url,
			'parent_uri'  => $parent_uri,
			'template_id' => $template_id,
			'parent_id'   => $parent_id,
			'listing_cid' => $listing_cid,
			'hidden'      => $hidden
		);

		if ($channel_type == 'listing') {
			$this->sql->set_listing_data($entry_data);
		} else {
			require_once PATH_THIRD.'structure/mod.structure.php';
	        $this->structure = new Structure();
			$this->structure->set_data($entry_data);
		}
		
	}

	function safecracker_submit_entry_end($obj)
	{
		$this->EE->load->helper('url');

		// The constants in this safecracker game.
		$channel_id       = $obj->channel['channel_id'];
		$channel_type     = $this->sql->get_channel_type($channel_id);

		
		if ($channel_type == NULL) return;
		
		// If we're not working with Structure data, let's kill this quickly.
		if ( ! isset($obj->entry['entry_id']) && ($channel_type != 'page' || $channel_type != 'listing')) {
			return;
		}
		
		// These may not always be available so putting them *after* the conditional
		$entry_id         = $obj->entry['entry_id'];
		

		// This defaults to false if not a listing entry
		$listing_entry = $this->sql->get_listing_entry($entry_id);

		/*
		|-------------------------------------------------------------------------
		| Template ID
		|-------------------------------------------------------------------------
		*/
		$default_template = $listing_entry ? $listing_entry['template_id'] : $this->sql->get_default_template($channel_id);

		$template_id = pick(
			array_get($obj->EE->api_sc_channel_entries->data, 'structure_template_id'),
			array_get($this->site_pages['templates'], $entry_id)
		);

		if ( ! $this->sql->is_valid_template($template_id)) {
			$template_id = $default_template;
		}

		/*
		|-------------------------------------------------------------------------
		| URI
		|-------------------------------------------------------------------------
		*/
		$default_uri = $listing_entry ? array_get($listing_entry, 'uri') : array_get($this->site_pages['uris'], $entry_id);

		$uri = Structure_Helper::tidy_url(
			pick(
				array_get($obj->EE->api_sc_channel_entries->data, 'structure_uri'),
				Structure_Helper::get_slug($default_uri),
				$obj->entry['url_title']
			)
		);

		/*
		|-------------------------------------------------------------------------
		| Parent ID
		|-------------------------------------------------------------------------
		*/
		$default_parent_id = $channel_type == 'listing' ? $this->sql->get_listing_parent($channel_id) : 0;

		$parent_id = pick(
			array_get($obj->EE->api_sc_channel_entries->data, 'structure_parent_id'),
			$this->sql->get_parent_id($entry_id, null),
			$default_parent_id
		);

		/*
		|-------------------------------------------------------------------------
		| Parent URI
		|-------------------------------------------------------------------------
		*/
		$parent_uri  = array_get($this->site_pages['uris'], $parent_id, '/');

		/*
		|-------------------------------------------------------------------------
		| URL
		|-------------------------------------------------------------------------
		*/
		$url = $channel_type == 'listing' ? $uri : $this->sql->create_full_uri($parent_uri, $uri);

		/*
		|-------------------------------------------------------------------------
		| Listing Channel ID
		|-------------------------------------------------------------------------
		*/
		$listing_cid = $this->sql->get_listing_channel($parent_id);

		/*
		|-------------------------------------------------------------------------
		| Hidden State
		|-------------------------------------------------------------------------
		*/
		$hidden = pick(
			array_get($obj->EE->api_sc_channel_entries->data, 'structure_hidden'),
			$this->sql->get_hidden_state($entry_id),
			'n'
		);

		/*
		|-------------------------------------------------------------------------
		| Entry data to be processed and saved
		|-------------------------------------------------------------------------
		*/
		$entry_data = array(
			'channel_id'  => $channel_id,
			'entry_id'    => $entry_id,
			'uri'         => $url,
			'parent_uri'  => $parent_uri,
			'template_id' => $template_id,
			'parent_id'   => $parent_id,
			'listing_cid' => $listing_cid,
			'hidden'      => $hidden
		);

		if ($channel_type == 'listing') {
			$this->sql->set_listing_data($entry_data);
		} else {
			require_once PATH_THIRD.'structure/mod.structure.php';
	        $this->structure = new Structure();
			$this->structure->set_data($entry_data);
		}
	}


	function template_post_parse($final_template, $sub, $site_id)
	{
		if (isset($this->EE->extensions->last_call) && $this->EE->extensions->last_call)
		{
		    $final_template = $this->EE->extensions->last_call;
		}

		// page_url_for
		$final_template = preg_replace_callback("({structure:page_url_for:(\d{1,})})", array(&$this, '_parse_tag_url_for'), $final_template);

		// page_uri_for
		$final_template = preg_replace_callback("({structure:page_uri_for:(\d{1,})})", array(&$this, '_parse_tag_uri_for'), $final_template);

		// page_title_for
		$final_template = preg_replace_callback("({structure:page_title_for:(\d{1,})})", array(&$this, '_parse_tag_title_for'), $final_template);

		// page_slug_for
		$final_template = preg_replace_callback("({structure:page_slug_for:(\d{1,})})", array(&$this, '_parse_tag_slug_for'), $final_template);

		$final_template = preg_replace_callback("({structure:child_ids_for:(\d{1,})})", array(&$this, '_parse_tag_child_ids_for'), $final_template);

		return $final_template;
	}

	function _parse_tag_url_for($m)
	{
		$url = array_key_exists($m[1], $this->site_pages['uris']) ? Structure_Helper::remove_double_slashes($this->site_pages['url'] . $this->site_pages['uris'][$m[1]]) : NULL;
		return $url;
	}

	function _parse_tag_uri_for($m)
	{
		$slug = array_key_exists($m[1], $this->site_pages['uris']) ? $this->site_pages['uris'][$m[1]] : NULL;
		return $slug;
	}

	function _parse_tag_title_for($m)
	{
		$title = $this->sql->get_entry_title($m[1]);
		return $title;
	}

	function _parse_tag_slug_for($m)
	{
		$slug = array_key_exists($m[1], $this->site_pages['uris']) ? $this->site_pages['uris'][$m[1]] : NULL;

		return $this->sql->get_slug($slug);
	}

	function _parse_tag_child_ids_for($m)
	{
		$child_ids = $this->sql->get_child_entries($m[1]);
		return $child_ids !== FALSE && count($child_ids > 0) ? implode('|', $child_ids) : false;
	}






	/**
	 * Activate Extension
	 * @return void
	 */
	function activate_extension()
	{
		$hooks = array(
			'entry_submission_redirect'         => 'entry_submission_redirect',
			'cp_member_login'                   => 'cp_member_login',
			'sessions_start'                    => 'sessions_start',
			'channel_module_create_pagination' 	=> 'channel_module_create_pagination',
			'wygwam_config'                     => 'wygwam_config',
			'core_template_route'               => 'core_template_route',
			'entry_submission_end'              => 'entry_submission_end',
			'safecracker_submit_entry_end'      => 'safecracker_submit_entry_end',
			'template_post_parse'               => 'template_post_parse'
			);

		foreach ($hooks as $hook => $method)
		{
			$priority = $hook == 'channel_module_create_pagination' ? 9 : 10;
			
			$app_ver = str_replace(".","",APP_VER);
			
			
			// Check in place for 2.7 to install new hooks for safecracker
			if(($hook=="safecracker_submit_entry_end") && (substr($app_ver, 0, 2)=="27"))
			{
				$hook = 'channel_form_submit_entry_end';
				$method  = 'channel_form_submit_entry_end';
			}

			$data = array(
				'class'		=> __CLASS__,
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> '',
				'priority'	=> $priority,
				'version'	=> $this->version,
				'enabled'	=> 'y'
				);
			$this->EE->db->insert('extensions', $data);
		}

	}


	/**
	 * Disable Extension
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}


	/**
	 * Update Extension
	 * @return 	mixed	void on update / false if none
	 */

	function update_extension($current = FALSE)
	{
		if ( ! $current || $current == $this->version)
			return FALSE;

		// add core_template_route hook
		if ($current < '3.2.5')
			$this->EE->db->insert('extensions', array('class' => __CLASS__, 'method' => 'core_template_route', 'hook' => 'core_template_route', 'settings' => '', 'priority' => 10, 'version' => $this->version, 'enabled' => 'y'));

		// add safecracker hook
		if ($current < '3.2.4')
		{
			$this->EE->db->insert('extensions', array('class' => __CLASS__, 'method' => 'template_post_parse', 'hook' => 'template_post_parse', 'settings' => '', 'priority' => 10, 'version' => $this->version, 'enabled' => 'y'));
		}

		// add safecracker hook
		if ($current < '3.1.4')
			$this->EE->db->insert('extensions', array('class' => __CLASS__, 'method' => 'safecracker_submit_entry_end', 'hook' => 'safecracker_submit_entry_end', 'settings' => '', 'priority' => 10, 'version' => $this->version, 'enabled' => 'y'));

		// add saef hook
		if ($current < '3.0.5')
			$this->EE->db->insert('extensions', array('class' => __CLASS__, 'method' => 'entry_submission_end', 'hook' => 'entry_submission_end', 'settings' => '', 'priority' => 10, 'version' => $this->version, 'enabled' => 'y'));

		// add pagination and wygwam hooks
		if ($current < '3.0')
		{
			$hooks = array('channel_module_create_pagination' => 'channel_module_create_pagination', 'wygwam_config' => 'wygwam_config');

			foreach ($hooks as $hook => $method)
			{
				$data = array('class' => __CLASS__, 'method' => $method, 'hook' => $hook, 'settings' => '', 'priority' => 10, 'version' => $this->version, 'enabled' => 'y');
				$this->EE->db->insert('extensions', $data);
			}
		}

		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions', array('version' => $this->version));
	}

}