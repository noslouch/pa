<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Low_reorder_base'))
{
	require_once(PATH_THIRD.'low_reorder/base.low_reorder.php');
}

/**
 * Low Reorder Module Control Panel class
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2012, Low
 */
class Low_reorder_mcp extends Low_reorder_base {

	// --------------------------------------------------------------------
	// PUBLIC METHODS
	// --------------------------------------------------------------------

	/**
	* Legacy Constructor
	*
	* @see         __construct()
	*/
	public function Low_reorder_mcp()
	{
		$this->__construct();
	}

	// --------------------------------------------------------------------

	/**
	* Constructor
	*
	* @access      public
	* @return      void
	*/
	public function __construct()
	{
		// -------------------------------------
		//  Call parent constructor
		// -------------------------------------

		parent::__construct();

		// -------------------------------------
		//  Define base url for module
		// -------------------------------------

		$this->set_base_url();

		// --------------------------------------
		// Add themes url for images
		// --------------------------------------

		$this->data['themes_url'] = $this->EE->config->slash_item('theme_folder_url');

		// --------------------------------------
		// Load JS lib
		// --------------------------------------

		$this->EE->load->library('javascript');
	}

	// --------------------------------------------------------------------

	/**
	* Home screen for module
	*
	* @access      public
	* @return      string
	*/
	public function index()
	{
		// --------------------------------------
		// Get all collections
		// --------------------------------------

		$this->EE->db->where('site_id', $this->site_id);
		$this->EE->db->order_by('set_label', 'asc');
		$this->data['sets'] = $this->EE->low_reorder_set_model->get_all();

		foreach ($this->data['sets'] AS &$set)
		{
			$permissions = $this->EE->low_reorder_set_model->get_permissions(json_decode($set['permissions'], TRUE));
			$set = array_merge($set, $permissions);
		}

		// --------------------------------------
		// Set page title and breadcrumb
		// --------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('low_reorder_module_name'));

		// --------------------------------------
		// Load view and return it
		// --------------------------------------

		return $this->view('mcp_index');
	}

	// --------------------------------------------------------------------

	/**
	* Edit reorder set
	*
	* @access      public
	* @return      string
	*/
	public function edit()
	{
		$this->EE->load->helper('form');

		// --------------------------------------
		// Get set by id or empty row
		// --------------------------------------

		$set_id = $this->EE->input->get('set_id');
		$set    = ($set_id === FALSE)
		        ? $this->EE->low_reorder_set_model->empty_row()
		        : $this->EE->low_reorder_set_model->get_one($set_id);

		// --------------------------------------
		// Get settings & permissions
		// --------------------------------------

		$set['parameters']  = $this->EE->low_reorder_set_model->get_params($set['parameters']);
		$set['permissions'] = json_decode($set['permissions'], TRUE);

		$perm = $this->EE->low_reorder_set_model->get_permissions($set['permissions']);

		if (( ! $set_id && $this->member_group != 1) || ($set_id && ! $perm['can_edit']))
		{
			show_error('Operation not permitted');
		}

		// --------------------------------------
		// Friendly user form elements
		// --------------------------------------

		$this->data['yesno_cache']   = $this->_yesno('clear_cache', (@$set['clear_cache'] == 'y' ? 'yes' : 'no'));
		$this->data['yesno_expired'] = $this->_yesno('parameters[show_expired]', @$set['parameters']['show_expired']);
		$this->data['yesno_future']  = $this->_yesno('parameters[show_future_entries]', @$set['parameters']['show_future_entries']);

		// --------------------------------------
		// Get all channels
		// --------------------------------------

		$query = $this->EE->db->select('channel_id, channel_name, channel_title, field_group, cat_group, status_group')
		       ->from('channels')
		       ->where('site_id', $this->site_id)
		       ->order_by('channel_title', 'asc')
		       ->get();
		$channels = low_associate_results($query->result_array(), 'channel_id');

		// Create multiple select for filter options
		$this->data['select_channel'] = form_multiselect(
			'channels[]',
			low_flatten_results($channels, 'channel_title', 'channel_id'),
			low_delinearize(@$set['channels']),
			low_multiselect_size(count($channels))
		);

		// --------------------------------------
		// Statuses
		// --------------------------------------

		$query = $this->EE->db->select('status, group_name')
		       ->from('statuses')
		       ->join('status_groups', 'statuses.group_id = status_groups.group_id')
		       ->where('statuses.site_id', $this->site_id)
		       ->where_not_in('status', array('open', 'closed'))
		       ->order_by('group_name, status_order')
		       ->get();

		$statuses = array(
			'open'   => 'Open',
			'closed' => 'Closed'
		);

		foreach ($query->result() AS $row)
		{
			$statuses[$row->group_name][$row->status] = $row->status;
		}

		$this->data['select_status'] = form_multiselect(
			'parameters[status][]',
			$statuses,
			low_delinearize(@$set['parameters']['status']),
			low_multiselect_size(count($statuses) + $query->num_rows())
		);

		// --------------------------------------
		// Categories
		// --------------------------------------

		// Initiate some vars
		$cat_groups = array();

		// We need the category api for that
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_categories');

		// get group details from DB
		$query = $this->EE->db->select('group_id, group_name, sort_order')
		       ->from('category_groups')
		       ->where('site_id', $this->site_id)
		       ->order_by('group_name', 'asc')
		       ->get();

		$cat_groups = $query->result_array();
		$cat_count  = 0;

		// Loop through groups and get the category group from API
		foreach ($cat_groups AS &$cat_group)
		{
			$this->EE->api_channel_categories->categories = array();
			$this->EE->api_channel_categories->category_tree($cat_group['group_id'], '', $cat_group['sort_order']);

			$cat_group['categories'] = $this->EE->api_channel_categories->categories;
			$cat_count += count($cat_group['categories']);
		}

		$this->data['category_groups'] = $cat_groups;
		$this->data['selected_category_ids']  = low_delinearize(@$set['parameters']['category']);

		// Multi select for category groups
		$this->data['select_category_groups'] = form_multiselect(
			'cat_groups[]',
			low_flatten_results($cat_groups, 'group_name', 'group_id'),
			low_delinearize(@$set['cat_groups']),
			low_multiselect_size(count($cat_groups))
		);

		// --------------------------------------
		// Get channel fields for search: params
		// --------------------------------------

		$query = $this->EE->db->select('cf.field_id, cf.field_name, cf.field_label, fg.group_name')
		       ->from('channel_fields cf')
		       ->join('field_groups fg', 'cf.group_id = fg.group_id')
		       ->where('cf.site_id', $this->site_id)
		       ->order_by('fg.group_name', 'asc')
		       ->order_by('cf.field_order', 'asc')
		       ->get();

		$fields = array('' => '--');

		foreach ($query->result() AS $row)
		{
			$fields[$row->group_name][$row->field_name] = $row->field_label;
		}

		$this->data['select_field_name'] = form_dropdown(
			'search[fields][]',
			$fields,
			array()
		);

		$this->data['select_field_value'] = form_input(array(
			'name' => 'search[values][]',
			'class' => 'medium'
		));

		// --------------------------------------
		// Get existing search filters
		// --------------------------------------

		$search = $this->EE->low_reorder_set_model->get_search_params($set['parameters']);

		$this->data['json_fields'] = $this->EE->javascript->generate_json($search);

		// --------------------------------------
		// Member groups
		// --------------------------------------

		$query = $this->EE->db->select('group_id, group_title')
		       ->from('member_groups')
		       ->where_not_in('group_id', array('1','2','3','4'))
		       ->where('can_access_cp', 'y')
		       ->order_by('group_title', 'asc')
		       ->get();

		$this->data['member_groups'] = low_flatten_results($query->result_array(), 'group_title', 'group_id');

		// Set non-existing groups to 0
		foreach (array_keys($this->data['member_groups']) AS $group_id)
		{
			if ( ! isset($set['settings']['permissions'][$group_id]))
			{
				$set['settings']['permissions'][$group_id] = 0;
			}
		}

		// --------------------------------------
		// Add set data to view
		// --------------------------------------

		$this->data['set'] = $set;

		// --------------------------------------
		// Add extra nav item if permitted
		// --------------------------------------

		if ($set_id && $perm['can_reorder'])
		{
			$this->extra_nav['reorder_entries'] = $this->base_url.AMP.'method=reorder&amp;set_id='.$set_id;
		}

		// --------------------------------------
		// Set title and breadcrumb
		// --------------------------------------

		$title = ($set_id === FALSE)
		       ? lang('create_new_set')
		       : lang('edit_set').' #'.$set_id;

		$this->EE->cp->set_variable('cp_page_title', $title);
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_reorder_module_name'));

		// Return settings form
		return $this->view('mcp_edit');
	}

	/**
	* Save set settings
	*
	* @access      public
	* @return      void
	*/
	public function save_set()
	{
		// --------------------------------------
		// Get Set id
		// --------------------------------------

		if ( ! ($set_id = $this->EE->input->post('set_id')))
		{
			return $this->_show_error('invalid_request');
		}

		// --------------------------------------
		// Init data array
		// --------------------------------------

		$data = array();

		// --------------------------------------
		// Regular fields
		// --------------------------------------

		foreach ($this->EE->low_reorder_set_model->attributes() AS $attr)
		{
			$data[$attr] = $this->EE->input->post($attr);
		}

		// Clean up params
		$data['parameters'] = array_filter($data['parameters']);

		// --------------------------------------
		// Validate some fields
		// --------------------------------------

		// Label shouldn't be empty
		if (empty($data['set_label']))
		{
			show_error(lang('set_label_empty'));
		}

		// Set name should be valid
		if ( ! preg_match('/^[-_\w]+$/i', $data['set_name']))
		{
			show_error(lang('set_name_invalid'));
		}

		// Check if set name is unique for this site
		if ( ! $this->EE->low_reorder_set_model->name_is_unique($set_id, $data['set_name'], $this->site_id))
		{
			show_error(lang('set_name_not_unique'));
		}

		// Channels shouldn't be empty
		if (empty($data['channels']))
		{
			show_error(lang('channels_empty'));
		}

		// Default to status = open if none are given
		if (empty($data['parameters']['status']))
		{
			$data['parameters']['status'][] = 'open';
		}

		// Set cat_groups to empty
		if ($data['cat_option'] != 'one')
		{
			$data['cat_groups'] = '';
		}
		else
		{
			if (empty($data['cat_groups']))
			{
				show_error(lang('cat_groups_empty'));
			}
		}

		// Unset category parameter
		if ($data['cat_option'] != 'some')
		{
			unset($data['parameters']['category']);
		}

		// Set clear_cache to 'y' or 'n'
		$data['clear_cache'] = ($data['clear_cache'] == 'yes') ? 'y' : 'n';

		// --------------------------------------
		// Set site id
		// --------------------------------------

		$data['site_id'] = $this->site_id;

		// --------------------------------------
		// Get parameters
		// --------------------------------------

		// Store channel_short_names in parameters
		if ( ! empty($data['channels']))
		{
			$query = $this->EE->db->select('channel_name')
			       ->from('channels')
			       ->where_in('channel_id', $data['channels'])
			       ->get();

			$data['parameters']['channel'] = implode('|', low_flatten_results($query->result_array(), 'channel_name'));
		}

		// --------------------------------------
		// Add search filters to parameters
		// --------------------------------------

		if ($search = $this->EE->input->post('search'))
		{
			foreach ($search['fields'] AS $i => $key)
			{
				$val = (string) @$search['values'][$i];

				if (strlen($key) && strlen($val))
				{
					$data['parameters']["search:{$key}"] = $val;
				}
			}
		}

		// --------------------------------------
		// Get parameters and permissions
		// --------------------------------------

		$data['parameters']  = $this->EE->low_reorder_set_model->get_params($data['parameters']);
		$data['permissions'] = (array) $this->EE->input->post('permissions');

		// Copy data to $sql_data
		$sql_data = $data;

		// --------------------------------------
		// Convert sql_data to strings
		// --------------------------------------

		$sql_data['parameters']  = $this->EE->javascript->generate_json($sql_data['parameters']);
		$sql_data['permissions'] = $this->EE->javascript->generate_json($sql_data['permissions']);

		foreach ($sql_data AS &$val)
		{
			if (is_array($val))
			{
				$val = count($val) ? low_linearize($val) : '';
			}
		}

		// --------------------------------------
		// Insert or Update
		// --------------------------------------

		if ($set_id == 'new')
		{
			$set_id = $this->EE->low_reorder_set_model->insert($sql_data);
		}
		else
		{
			$this->EE->low_reorder_set_model->update($set_id, $sql_data);
		}

		// --------------------------------------
		// Insert order
		// --------------------------------------

		// Prep params
		$params = $data['parameters'] + array(
			'channel_id' => implode('|', $data['channels'])
		);

		// Initiate orders for all categories
		if ($data['cat_option'] == 'one')
		{
			$query = $this->EE->db->select("c.cat_id, GROUP_CONCAT(t.entry_id ORDER BY t.entry_date DESC SEPARATOR '|') AS entry_ids", FALSE)
			       ->from('channel_titles t')
			       ->join('category_posts cp', 't.entry_id = cp.entry_id')
			       ->join('categories c', 'c.cat_id = cp.cat_id')
			       ->where_in('t.channel_id', $data['channels'])
			       ->where_in('t.status', explode('|', $data['parameters']['status']))
			       ->where_in('c.group_id', $data['cat_groups'])
			       ->group_by('c.cat_id')
			       ->get();

			foreach ($query->result() AS $row)
			{
				$this->EE->low_reorder_order_model->insert_ignore(array(
					'set_id'     => $set_id,
					'cat_id'     => $row->cat_id,
					'sort_order' => ($row->entry_ids ? "|{$row->entry_ids}|" : '')
				));
			}
		}
		// Initiate order for regular sets
		else
		{
			$entries = low_flatten_results($this->get_entries($params), 'entry_id');

			$this->EE->low_reorder_order_model->insert_ignore(array(
				'set_id'     => $set_id,
				'cat_id'     => 0,
				'sort_order' => low_linearize($entries)
			));
		}

		// -------------------------------------
		// 'low_reorder_post_save_set' hook.
		//  - Do something after the (new) set has been saved
		// -------------------------------------

		if ($this->EE->extensions->active_hook('low_reorder_post_save_set') === TRUE)
		{
			// Use raw, non-encoded data to pass through
			$this->EE->extensions->call('low_reorder_post_save_set', $set_id, $data);
		}

		// --------------------------------------
		// Set feedback message
		// --------------------------------------

		$this->EE->session->set_flashdata('msg', lang('settings_saved'));

		// --------------------------------------
		// Go back to set or reoder page
		// --------------------------------------

		$method = $this->EE->input->post('reorder') ? 'reorder' : 'edit';

		$this->EE->functions->redirect($this->base_url.AMP.'method='.$method.AMP.'set_id='.$set_id);
	}

	// --------------------------------------------------------------------

	/**
	* List entries for single channel/field combo
	*
	* @access      public
	* @return      string
	*/
	public function reorder()
	{
		// --------------------------------------
		// Get Set id
		// --------------------------------------

		if ( ! ($set_id = $this->EE->input->get('set_id')))
		{
			return $this->_show_error('invalid_request');
		}

		if ( ! ($set = $this->EE->low_reorder_set_model->get_one($set_id)))
		{
			show_error('Reorder set not found');
		}

		// --------------------------------------
		// Get settings
		// --------------------------------------

		$params = $this->EE->low_reorder_set_model->get_params($set['parameters']);
		$perm   = $this->EE->low_reorder_set_model->get_permissions($set['permissions']);

		// --------------------------------------
		// Change channels to array
		// --------------------------------------

		$set['channels']   = low_delinearize($set['channels']);
		$set['cat_groups'] = low_delinearize($set['cat_groups']);

		// --------------------------------------
		// Pre-define some variables for the view
		// --------------------------------------

		$this->data['show_entries']    = TRUE;
		$this->data['select_category'] = FALSE;

		// --------------------------------------
		// Get selected category, if there is one
		// --------------------------------------

		if (($set['cat_id'] = $this->EE->input->get('category')) === FALSE)
		{
			$set['cat_id'] = 0;
		}

		// --------------------------------------
		// If cat_option == 'one', a category must be selected first
		// And we need to get a list of categories to put in the
		// category selection drop down
		// --------------------------------------

		if ($set['cat_option'] == 'one' && ! empty($set['cat_groups']))
		{
			// Showing entries depends on selected category
			$this->data['show_entries'] = ($set['cat_id'] > 0);
			$this->data['select_category'] = TRUE;
			$this->data['selected_category'] = $set['cat_id'];

			// Limit query to selected category
			$params['category'] = $set['cat_id'];

			// Load categories API
			$this->EE->load->library('api');
			$this->EE->api->instantiate('channel_categories');

			// get group details from DB
			$query = $this->EE->db->select('group_id, group_name, sort_order')
			       ->from('category_groups')
			       ->where_in('group_id', $set['cat_groups'])
			       ->order_by('group_name', 'asc')
			       ->get();

			$this->data['category_groups'] = $query->result_array();
			$this->data['total_groups'] = $query->num_rows();

			// Loop through groups and get the category group from API
			foreach ($this->data['category_groups'] AS &$row)
			{
				$this->EE->api_channel_categories->categories = array();
				$this->EE->api_channel_categories->category_tree($row['group_id'], '', $row['sort_order']);
				$row['categories'] = $this->EE->api_channel_categories->categories;
			}

			$this->data['url'] = $this->base_url.AMP.'method=reorder'.AMP.'set_id='.$set_id.AMP.'category=';
		}

		// --------------------------------------
		// If we're showing entries, get them first
		// --------------------------------------

		if ($this->data['show_entries'])
		{
			// Get the current order from the DB
			$this->EE->db->where('cat_id', $set['cat_id']);
			$order = $this->EE->low_reorder_order_model->get_one($set_id, 'set_id');
			$set_order = empty($order) ? array() : low_delinearize($order['sort_order']);

			// Add channel_id as parameter
			$params['channel_id'] = implode('|', $set['channels']);

			// Get 'em, sonny boy
			$entries = $this->get_entries($params, $set_order);

			// Edit entry url
			$edit_tmpl = '<a href="'.BASE.'&amp;C=content_publish&amp;M=entry_form&amp;channel_id=%s&amp;entry_id=%s">%s</a>';

			// Loop through row, add stuff
			foreach ($entries AS &$row)
			{
				// Escape title
				$row['title'] = htmlspecialchars($row['title']);

				// Add default hidden divs
				$row['hidden'] = array(
					sprintf($edit_tmpl, $row['channel_id'], $row['entry_id'], lang('edit')),
					ucfirst($row['status']),
					'#'.$row['entry_id'],
				);
			}

			// -------------------------------------
			// 'low_reorder_show_entries' hook.
			//  - Change the output of entries displayed in the CP reorder list
			// -------------------------------------

			if ($this->EE->extensions->active_hook('low_reorder_show_entries') === TRUE)
			{
				$entries = $this->EE->extensions->call('low_reorder_show_entries', $entries, $set);
			}

			$this->data['entries'] = $entries;
		}

		// Add settings to data as well
		$this->data['set'] = $set;
		$this->data['params'] = $params;

		// --------------------------------------
		// Add extra nav item if permitted
		// --------------------------------------

		if ($set_id && $perm['can_edit'])
		{
			$this->extra_nav['edit_set'] = $this->base_url.AMP.'method=edit&amp;set_id='.$set_id;
		}

		// --------------------------------------
		// Set title and breadcrumb
		// --------------------------------------

		$this->EE->cp->set_variable('cp_page_title', $set['set_label']);
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_reorder_module_name'));

		// Return settings form
		return $this->view('mcp_reorder');
	}

	/**
	* Save the New Order (dundundun)
	*
	* @access      public
	* @return      void
	*/
	public function save_order()
	{
		// --------------------------------------
		// Get Set id
		// --------------------------------------

		if ( ! ($set_id = $this->EE->input->post('set_id')))
		{
			return $this->_show_error('invalid_request');
		}

		// --------------------------------------
		// Get Cat id
		// --------------------------------------

		$cat_id = $this->EE->input->post('cat_id');

		// --------------------------------------
		// Get entries
		// --------------------------------------

		$entries = (array) $this->EE->input->post('entries');

		// --------------------------------------
		// Reverse entries if sort = desc
		// --------------------------------------

		if ($this->EE->input->post('sort') == 'desc')
		{
			$entries = array_reverse($entries);
		}

		// --------------------------------------
		// REPLACE INTO table statement
		// --------------------------------------

		$this->EE->low_reorder_order_model->replace(array(
			'set_id' => $set_id,
			'cat_id' => $cat_id,
			'sort_order' => low_linearize($entries)
		));

		// --------------------------------------
		// That's the entries updated
		// Now, do we need to clear the cache?
		// --------------------------------------

		$clear_cache = ($this->EE->input->post('clear_caching') == 'y');

		if ($clear_cache)
		{
			$this->EE->functions->clear_caching('all', '', TRUE);
		}

		// -------------------------------------
		// 'low_reorder_post_sort' hook.
		//  - Do something after new order is saved
		// -------------------------------------

		if ($this->EE->extensions->active_hook('low_reorder_post_sort') === TRUE)
		{
			$this->EE->extensions->call('low_reorder_post_sort', $entries, $clear_cache);
		}

		// --------------------------------------
		// Get ready to redirect back
		// --------------------------------------

		$url = $this->base_url.AMP.'method=reorder&amp;set_id='.$set_id;

		// Redirect to selected category, if any
		if ($cat_id)
		{
			$url .= AMP.'category='.$cat_id;
		}

		// --------------------------------------
		// Set flashdata for feedback
		// --------------------------------------

		$this->EE->session->set_flashdata('msg', lang('new_order_saved'));

		// And go back
		$this->EE->functions->redirect($url);
		exit;
	}

	// --------------------------------------------------------------------

	/**
	 * Confirm deletion of a set
	 *
	 * @access      public
	 * @return      string
	 */
	public function delete_confirm()
	{
		// --------------------------------------
		// Redirect back to module home if no set is given
		// --------------------------------------

		if ( ! ($set_id = $this->EE->input->get('set_id')))
		{
			$this->EE->functions->redirect($this->base_url);
		}

		// --------------------------------------
		// Get collection from DB
		// --------------------------------------

		if ( ! ($set = $this->EE->low_reorder_set_model->get_one($set_id)))
		{
			$this->EE->functions->redirect($this->base_url);
		}

		// --------------------------------------
		// Compose data
		// --------------------------------------

		$this->data = array_merge($this->data, $set);

		// --------------------------------------
		// Title and Crumbs
		// --------------------------------------

		$this->EE->cp->set_variable('cp_page_title', lang('delete_set_confirm'));
		$this->EE->cp->set_breadcrumb($this->base_url, lang('low_reorder_module_name'));

		// --------------------------------------
		// Load up view
		// --------------------------------------

		return $this->view('mcp_delete');
	}

	/**
	 * Delete a set
	 *
	 * @access      public
	 * @return      void
	 */
	public function delete()
	{
		// --------------------------------------
		// Check set id
		// --------------------------------------

		if ($set_id = $this->EE->input->post('set_id'))
		{
			// --------------------------------------
			// Delete in 2 tables
			// --------------------------------------

			$this->EE->low_reorder_set_model->delete($set_id);
			$this->EE->low_reorder_order_model->delete($set_id, 'set_id');

			// --------------------------------------
			// Set feedback message
			// --------------------------------------

			$this->EE->session->set_flashdata('msg', 'set_deleted');
		}

		// --------------------------------------
		// Go home
		// --------------------------------------

		$this->EE->functions->redirect($this->base_url);
	}

	// --------------------------------------------------------------------
	// PRIVATE METHODS
	// --------------------------------------------------------------------

	/**
	* Show error message in module
	*
	* @access      private
	* @param       string
	* @return      string
	*/
	private function _show_error($msg)
	{
		// Set page title
		$this->EE->cp->set_variable('cp_page_title', lang('error'));

		// Set breadcrumb
		$this->EE->cp->set_breadcrumb(BASE.AMP.$this->base_url, lang('low_reorder_module_name'));

		$this->data['error_msg'] = $msg;

		return $this->view('mcp_error');
	}

	// --------------------------------------------------------------------

	/**
	* Yes/No radio buttons
	*
	* @access      private
	* @param       string
	* @param       string
	* @return      string
	*/
	private function _yesno($name = '', $value = '')
	{
		return '<label>'.form_radio($name, 'yes', ($value == 'yes')).NBS.lang('yes').'</label>'
		     . str_repeat(NBS,5)
		     . '<label>'.form_radio($name, '',    ($value != 'yes')).NBS.lang('no').'</label>';
	}
}
// End mcp.low_reorder.php