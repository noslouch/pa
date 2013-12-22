<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Include Structure SQL Model
 */
require_once PATH_THIRD.'structure/sql.structure.php';

/**
 * Include Structure Core Mod
 */
require_once PATH_THIRD.'structure/mod.structure.php';
require_once PATH_THIRD.'structure/helper.php';

class Structure_tab
{

	function Structure_tab()
	{
		$this->EE =& get_instance();
		$this->sql = new Sql_structure();
	    $this->structure = new Structure();
	}


	function default_tab()
	{
		$settings[] = array(
			'field_id'				=> '',
			'field_label'			=> '',
			'field_required' 		=> 'n',
			'field_data'			=> '',
			'field_list_items'		=> '',
			'field_fmt'				=> '',
			'field_instructions' 	=> '',
			'field_show_fmt'		=> 'n',
			'field_fmt_options'		=> array(),
			'field_pre_populate'	=> 'n',
			'field_text_direction'	=> 'ltr',
			'field_type' 			=> 'text',
			'field_maxl'			=> '1'
		);

		return $settings;
	}


	function publish_tabs($channel_id, $entry_id = '')
	{
		$settings = array();
		$channel_id = $this->EE->input->get_post('channel_id') ? $this->EE->input->get_post('channel_id') : $this->sql->get_channel_by_entry_id($entry_id);
		$structure_channels = $this->structure->get_structure_channels();
		$channel_type = $structure_channels[$channel_id]['type'];

		// Kill the Structure tab if channel is not managed by Structure
		if (($channel_type != 'page' && $channel_type != 'listing') || (isset($permissions['admin']) && $permissions['admin'] != TRUE))
			return array();

		$this->EE->load->helper('form');
		$this->EE->lang->loadfile('structure');

		if (REQ == 'CP' && ! $this->EE->input->get_post('entry_id')) {
			$this->EE->cp->add_js_script('plugin', 'ee_url_title');

			$this->EE->javascript->output('
				$("#edit_group_prefs").hide();
				$("#title").bind("keyup keydown", function() {
					$(this).ee_url_title("#structure__uri");
				});
			');
		}

		$structure_settings = $this->sql->get_settings();
		$site_pages = $this->sql->get_site_pages();

		$site_id = $this->EE->config->item('site_id');
		$entry_id = $this->EE->input->get_post('entry_id') !== FALSE ? $this->EE->input->get_post('entry_id') : 0;

		$data  = $this->sql->get_data();
		$cids  = isset($data['channel_ids']) ? $data['channel_ids'] : array();
		$lcids = isset($data['listing_cids']) ? $data['listing_cids'] : array();

		// overide defaults and previous data with data from the form if available (SAEFs?)
		// $uri         = $this->EE->input->get_post('structure__uri') ? $this->EE->input->get_post('structure__uri') : $uri;
		// $listing     = $this->EE->input->get_post('structure__listing') ? $this->EE->input->get_post('structure__listing') : $listing;
		// $listing_cid = $this->EE->input->get_post('structure__listing_channel') ? $this->EE->input->get_post('structure__listing_channel') : $listing_cid;

		$listing_parent = $this->sql->get_listing_parent($channel_id);

		/** -------------------------------------
		/**  Field: Parent ID
		/** -------------------------------------*/

		if ($channel_type == 'page' && array_key_exists($entry_id, $data) && !empty($data[$entry_id]['parent_id']))
		{
			$parent_id = $data[$entry_id]['parent_id'];
		}
		elseif ($this->EE->input->get_post('parent_id'))
		{
			$parent_id = $this->EE->input->get_post('parent_id');
		}
		elseif ($listing_parent)
		{
			$parent_id = $listing_parent;
		}
		else
		{
			$parent_id = 0;
		}

		$parent_uri = $channel_type == 'page' && $parent_id && array_key_exists($parent_id, $site_pages['uris']) ? $site_pages['uris'][$parent_id] : NULL;
		$selected_parent = array($parent_id);
		$parent_ids = $this->get_parent_fields($entry_id, $data);

		if (array_key_exists($channel_id, $this->structure->get_structure_channels('page')))
		{
			$settings[] = array(
				'field_id'				=> 'parent_id',
				'field_label'			=> lang('tab_parent_entry'),
				'field_required' 		=> 'n',
				'field_data'			=> $selected_parent,
				'field_list_items'		=> $parent_ids,
				'field_fmt'				=> '',
				'field_instructions' 	=> '',
				'field_show_fmt'		=> 'n',
				'field_fmt_options'		=> array(),
				'field_pre_populate'	=> 'n',
				'field_text_direction'	=> 'ltr',
				'field_type' 			=> 'select'
			);
		}

		/** -------------------------------------
		/**  Field: Page URI/Slug
		/** -------------------------------------*/

		$selected_uri = array_key_exists($entry_id, $site_pages['uris']) ? $site_pages['uris'][$entry_id] : NULL;
		$uri = array_key_exists($entry_id, $site_pages['uris']) ? $site_pages['uris'][$entry_id] : NULL;

		$slug = $uri == '/' ? '/' : end(explode('/', trim($uri, '/')));
		$help_text  = $listing_parent ? "<p class='instruction_override'><strong>URL prefix</strong><style>#sub_hold_field_structure__uri .instruction_text p {display:none;} #sub_hold_field_structure__uri .instruction_text p.instruction_override {display:block;}</style>: ".$site_pages['uris'][$parent_id]."</p>" : '';
		// $uri_override .= '<input type="text" name="structure__uri" value="'.$slug.'" id="structure__uri" dir="ltr" maxlength="100"  />';

		$settings[] = array(
			'field_id'				=> 'uri',
			'field_label'			=> $listing_parent ? lang('tab_listing_url') : lang('tab_page_url'),
			'field_required' 		=> 'n',
			'field_data'			=> $slug,
			'field_list_items'		=> $uri,
			'field_fmt'				=> '',
			'field_instructions' 	=> $help_text,
			'field_show_fmt'		=> 'n',
			'field_fmt_options'		=> array(),
			'field_pre_populate'	=> 'n',
			'field_text_direction'	=> 'ltr',
			'field_type' 			=> 'text',
			'field_maxl'			=> 100,
			// 'string_override'		=> $uri_override
		);

		/** -------------------------------------
		/**  Field: Template
		/** -------------------------------------*/

		$template_id 	= array_key_exists($entry_id, $site_pages['uris']) ? $site_pages['templates'][$entry_id] : $structure_channels[$channel_id]['template_id'];
		$templates = $this->get_template_fields($entry_id, $data, $channel_id, $structure_settings);
		$structure_channels = $this->structure->get_structure_channels('', $channel_id);
		$selected_template = $entry_id != 0 && array_key_exists($entry_id, $site_pages['templates']) ? array($site_pages['templates'][$entry_id]) : array($structure_channels[$channel_id]['template_id']);

		$settings[] = array(
			'field_id'				=> 'template_id',
			'field_label'			=> lang('template'),
			'field_required' 		=> 'n',
			'field_data'			=> $selected_template,
			'field_list_items'		=> $templates,
			'field_fmt'				=> 'text',
			'field_instructions' 	=> '',
			'field_show_fmt'		=> 'n',
			'field_fmt_options'		=> '',
			'field_pre_populate'	=> 'n',
			'field_text_direction'	=> 'ltr',
			'field_type' 			=> 'select'
		);

		if ($channel_type != 'listing')
		{
			/** -------------------------------------
			/**  Field: Hide From Nav
			/** -------------------------------------*/

			$hide_select = array('n'=>'No', 'y'=>'Yes');
			$hide_setting = $this->sql->get_hidden_state($entry_id);

			$settings[] = array(
				'field_id'				=> 'hidden',
				'field_label'			=> 'Hide from nav?',
				'field_required' 		=> 'n',
				'field_data'			=> $hide_setting,
				'field_list_items'		=> $hide_select,
				'field_fmt'				=> 'text',
				'field_instructions' 	=> '',
				'field_show_fmt'		=> 'n',
				'field_fmt_options'		=> '',
				'field_pre_populate'	=> 'n',
				'field_text_direction'	=> 'ltr',
				'field_type' 			=> 'select'
			);
		}

		/** -------------------------------------
		/**  Field: Listing Channel
		/** -------------------------------------*/

		$listing_cid = $entry_id != 0 && array_key_exists($entry_id, $data) ? $data[$entry_id]['listing_cid'] : FALSE;
		$listing_channels = $this->get_listing_channels($entry_id, $data, $channel_id);

		$result = $this->EE->db->query("SELECT listing_cid FROM exp_structure WHERE listing_cid != 0");

		$used_listing_ids = array();
		foreach ($result->result_array() as $row)
		{
			$used_listing_ids[$row['listing_cid']] = $row['listing_cid'];
		}

		unset($used_listing_ids[$listing_cid]);

		$listing_channels = array_diff_key($listing_channels, $used_listing_ids);

		if ( ! array_key_exists($channel_id, $used_listing_ids) || $channel_type != 'listing')
		{
			$settings[] = array(
				'field_id'				=> 'listing_channel',
				'field_label'			=> lang('listing_channel'),
				'field_required' 		=> 'n',
				'field_data'			=> $listing_cid,
				'field_list_items'		=> $listing_channels,
				'field_fmt'				=> '',
				'field_instructions' 	=> '',
				'field_show_fmt'		=> 'n',
				'field_fmt_options'		=> array(),
				'field_pre_populate'	=> 'n',
				'field_text_direction'	=> 'ltr',
				'field_type' 			=> 'select'
				);
		}

		return $settings;
	}


	function publish_data_delete_db($params)
	{
		$this->structure->delete_data($params['entry_ids']);
	}


	function validate_publish($params)
	{
	  
	  $structure_channels = $this->structure->get_structure_channels();
	  $channel_type = $structure_channels[$params[0]['channel_id']]['type'];
	  
	  if ($channel_type == 'page')
		{  
		  $adapter = new Structure_Nestedset_Adapter_Ee('exp_structure', 'lft', 'rgt', 'entry_id');
		  $this->nset = new Structure_Nestedset($adapter);
		
		  $entry_id = $params[0]['entry_id'];
		  $node = $this->nset->getNode($entry_id);
		  
		  $parent_id = $params[0]['parent_id'];
		  $parentNode = $this->nset->getNode($parent_id);
		  
		  if ($parentNode && $parentNode['left'] > $node['left'] && $parentNode['right'] < $node['right'] && $entry_id!=0) {
		    return array('You can not nest a page below itself.' => 'parent_id');
		  }
		}
		else
		{
			return;
		}
	}

	function publish_data_db($params)
	{
		$structure_channels = $this->structure->get_structure_channels();
		$channel_type = $structure_channels[$params['meta']['channel_id']]['type'];
		$allow_dupes = false;

		if ($channel_type == 'page' || $channel_type == 'listing')
		{
			$this->EE->load->helper('url');
			$site_pages = $this->sql->get_site_pages();

			$word_separator = $this->EE->config->item('word_separator');
			$separator = $word_separator != 'dash' ? '_' : '-';
			$title = $params['meta']['title']; // Entry Title
			$structure_uri = isset($params['mod_data']['uri']) ? $params['mod_data']['uri'] : ''; // contents of uri input field

			$uri = $structure_uri == '' ? $this->create_uri($title) : $this->create_uri($structure_uri);

			// If the current channel is not assigned as any sort of Structure channel, then stop
			if ($channel_type == 'page')
			{
				// get form fields
				$data = array(
					'site_id'		=> $params['meta']['site_id'],
					'channel_id'	=> $params['meta']['channel_id'],
					'entry_id'		=> $params['entry_id'],
					'uri'			=> $uri,
					'template_id'	=> $params['mod_data']['template_id'],
					'hidden'		=> $params['mod_data']['hidden'],
					'listing_cid'	=> array_key_exists('listing_channel', $params['mod_data']) ? $params['mod_data']['listing_channel'] : 0
				);

				$data['parent_id'] = array_key_exists('parent_id', $params['mod_data']) ? $params['mod_data']['parent_id'] : 0;
				$parent_uri = isset($site_pages['uris'][$data['parent_id']]) ? $site_pages['uris'][$data['parent_id']] : '/';
				$data['uri'] = $this->structure->create_page_uri($parent_uri, $data['uri']);

				// Duplicate url check
				if ($this->EE->extensions->active_hook('structure_allow_dupes') === TRUE)
				{
					$allow_dupes = $this->EE->extensions->call('structure_allow_dupes', $data['uri']);
				}

				$dupe = $this->sql->is_duplicate_page_uri($data['entry_id'], $data['uri']);

				if ( ! $allow_dupes && $dupe !== FALSE)
				{
					$data['uri'] = $dupe;
				}

				$this->structure->set_data($data);
			}
			elseif ($channel_type == 'listing')
			{
				// get form fields
				$data = array(
					'site_id'     => $params['meta']['site_id'],
					'channel_id'  => $params['meta']['channel_id'],
					'entry_id'    => $params['entry_id'],
					'uri'         => $uri,
					'template_id'	=> $params['mod_data']['template_id'],
					'listing_cid'	=> array_key_exists('listing_channel', $params['mod_data']) ? $params['mod_data']['listing_channel'] : 0
				);
				$data['parent_id'] = $this->sql->get_listing_parent($data['channel_id']);

				// Duplicate url checks
				$dupe_count = $this->sql->is_duplicate_listing_uri($data['entry_id'], $uri, $data['parent_id']);

				if ($this->EE->extensions->active_hook('structure_allow_dupes') === TRUE)
				{
					$allow_dupes = $this->EE->extensions->call('structure_allow_dupes', $data['uri']);
				}

				if ( ! $allow_dupes && $dupe_count !== FALSE)
				{
					for ($i=1; ; $i++)
					{
						if ($this->sql->is_duplicate_listing_uri($data['entry_id'], $uri.$separator.$i, $data['parent_id']) == FALSE)
						{
							$data['uri'] = $uri.$separator.$i;
							break;
						}
					}

				}

				$data['parent_uri'] = $site_pages['uris'][$data['parent_id']];

				$this->sql->set_listing_data($data);

			}
		}
		else
		{
			return;
		}
	}


	/** -------------------------------------
	/**  Utility functions
	/** -------------------------------------*/

	function get_template_fields($entry_id, $data, $channel_id, $structure_settings)
	{
		$site_id = $this->EE->config->item('site_id');

		$template_id = isset($structure_settings['template_channel_' . $channel_id]) ? $structure_settings['template_channel_' . $channel_id] : 0;
		$template_id = $this->EE->input->get_post('structure__template_id') ? $this->EE->input->get_post('structure__template_id') : $template_id;

		$templates = $this->sql->get_templates();
		$options = array();

		foreach ($templates as $template_row)
		{
			$template_id = $template_row['template_id'];
			$template_group = $template_row['group_name'] . "/" . $template_row['template_name'];
			$options[$template_id] = $template_group;
		}

		return $options;
	}


	function get_parent_fields()
	{

		// Build Parent Entries Select Box
		$parent_id = $this->EE->input->get_post('structure__parent_id') ? $this->EE->input->get_post('structure__parent_id') : 0;
		$parent_ids = array();
		$parent_ids[0] = "NONE";

		$data = $this->sql->get_data($this->EE->input->get('entry_id'));

		foreach ($data as $eid => $entry)
		{
			// Add faux indent with "--" double dashes
			$option  = str_repeat("--", @$entry['depth']);
			$option .= @$entry['title'];

			$parent_ids[$eid] = $option;
		}

		return $parent_ids;
	}


	function get_listing_channels($entry_id, $data, $channel_id)
	{

		$site_id = $this->EE->config->item('site_id');
		$structure_data = $this->sql->get_data();


		$listings = $this->structure->get_structure_channels('listing');
		$count_listings = count($listings);

		// Build Listing Channels Select Box
		$listing_channel = $this->EE->input->get_post('structure__listing_channel') ? $this->EE->input->get_post('structure__listing_channel') : 0;
		$listing_channels = array();
		$listing_channels[0] = "==None Selected==";

		if ($count_listings > 0)
		{
			foreach ($listings as $channel_id => $row)
			{
				$listing_channels[$channel_id] = $row['channel_title'];
			}
		}

		return $listing_channels;
	}

	function create_uri($str)
	{
		return url_title($str, $this->EE->config->item('word_separator'), TRUE);
	}
}
/* END Class */

/* End of file tab.structure.php */
/* Location: ./system/expressionengine/third_party/structure/tab.structure.php */