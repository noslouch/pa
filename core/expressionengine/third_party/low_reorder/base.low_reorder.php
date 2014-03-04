<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include(PATH_THIRD.'low_reorder/config.php');

/**
 * Low Reorder Base Class
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2013, Low
 */
class Low_reorder_base {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Add-on name
	 *
	 * @var        string
	 * @access     public
	 */
	public $name = LOW_REORDER_NAME;

	/**
	 * Add-on version
	 *
	 * @var        string
	 * @access     public
	 */
	public $version = LOW_REORDER_VERSION;

	/**
	 * URL to module docs
	 *
	 * @var        string
	 * @access     public
	 */
	public $docs_url = LOW_REORDER_DOCS;

	/**
	 * Settings array
	 *
	 * @var        array
	 * @access     public
	 */
	public $settings = array();

	// --------------------------------------------------------------------

	/**
	 * Package name
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $package = LOW_REORDER_PACKAGE;

	/**
	 * Main class name
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $class_name;

	/**
	 * Site id shortcut
	 *
	 * @var        int
	 * @access     protected
	 */
	protected $site_id;

	/**
	 * Member group shortcut
	 *
	 * @var        int
	 * @access     protected
	 */
	protected $member_group;

	/**
	 * Base url for module
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $base_url;

	/**
	 * Base url for extension
	 *
	 * @var        string
	 * @access     protected
	 */
	protected $ext_url;

	/**
	 * Data array for views
	 *
	 * @var        array
	 * @access     protected
	 */
	protected $data = array();

	/**
	 * Default settings array
	 *
	 * @var        array
	 * @access     protected
	 */
	protected $default_settings = array(
		'can_create_sets' => array(1)
	);

	/**
	 * Extra nav in CP
	 *
	 * @var        array
	 * @access     protected
	 */
	protected $extra_nav = array();

	// --------------------------------------------------------------------

	/**
	 * Control Panel assets
	 *
	 * @var        array
	 * @access     private
	 */
	private $mcp_assets = array();

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @return     void
	 */
	public function __construct()
	{
		// -------------------------------------
		//  Define the package path
		// -------------------------------------

		ee()->load->add_package_path(PATH_THIRD.$this->package);

		// -------------------------------------
		//  Load helper
		// -------------------------------------

		ee()->load->helper($this->package);

		// -------------------------------------
		//  Libraries
		// -------------------------------------

		ee()->load->library('Low_reorder_model');

		// -------------------------------------
		//  Load the models
		// -------------------------------------

		Low_reorder_model::load_models();

		// -------------------------------------
		//  Set main class name
		// -------------------------------------

		$this->class_name = ucfirst($this->package);

		// -------------------------------------
		//  Get site shortcut
		// -------------------------------------

		$this->site_id = (int) ee()->config->item('site_id');

		// -------------------------------------
		//  Get member group shortcut
		// -------------------------------------

		$this->member_group = (int) @ee()->session->userdata['group_id'];
	}

	// --------------------------------------------------------------------

	/**
	 * Get settings
	 *
	 * @access     protected
	 * @param      string
	 * @return     mixed
	 */
	protected function get_settings($which = FALSE)
	{
		if (empty($this->settings))
		{
			// Check cache
			if (($this->settings = low_get_cache($this->package, 'settings')) === FALSE)
			{
				// Not in cache? Get from DB and add to cache
				$query = ee()->db->select('settings')
				       ->from('extensions')
				       ->where('class', $this->class_name.'_ext')
				       ->limit(1)
				       ->get();

				$this->settings = (array) @unserialize($query->row('settings'));

				// Add to cache
				low_set_cache($this->package, 'settings', $this->settings);
			}
		}

		// Always fallback to default settings
		$this->settings = array_merge($this->default_settings, $this->settings);

		if ($which !== FALSE)
		{
			return isset($this->settings[$which]) ? $this->settings[$which] : FALSE;
		}
		else
		{
			return $this->settings;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Sets base url for views
	 *
	 * @access     protected
	 * @return     void
	 */
	protected function set_base_url()
	{
		$this->data['base_url'] = $this->base_url = BASE.AMP.'C=addons_modules&amp;M=show_module_cp&amp;module='.$this->package;
		$this->data['ext_url'] = $this->ext_url = BASE.AMP.'C=addons_extensions&amp;M=extension_settings&amp;file='.$this->package;
	}

	/**
	 * View add-on page
	 *
	 * @access     protected
	 * @param      string
	 * @return     string
	 */
	protected function view($file)
	{
		// -------------------------------------
		//  Load CSS and JS
		// -------------------------------------

		$version = '&amp;v=' . (LOW_REORDER_DEBUG ? time() : LOW_REORDER_VERSION);

		ee()->cp->load_package_css($this->package.$version);
		ee()->cp->load_package_js($this->package.$version);

		// -------------------------------------
		//  Add feedback msg to output
		// -------------------------------------

		if ($this->data['message'] = ee()->session->flashdata('msg'))
		{
			ee()->javascript->output(array(
				'$.ee_notice("'.lang($this->data['message']).'",{type:"success",open:true});',
				'window.setTimeout(function(){$.ee_notice.destroy()}, 2000);'
			));
		}

		// -------------------------------------
		//  Add menu to page if manager
		// -------------------------------------

		$nav = array();
		$nav['low_reorder_module_name'] = $this->base_url;

		if ($this->member_group == 1 || in_array($this->member_group, $this->get_settings('can_create_sets')))
		{
			$nav['create_new_set'] = $this->base_url.AMP.'method=edit';
		}

		$nav += $this->extra_nav;

		ee()->cp->set_right_nav($nav);

		return ee()->load->view($file, $this->data, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Get simple list of entries based on given parameters, set order and limit
	 *
	 * @access     private
	 * @param      array
	 * @param      string
	 * @param      int
	 * @return     array
	 */
	protected function get_entries($params, $set_order = array(), $limit = FALSE)
	{
		// --------------------------------------
		// Check search params
		// --------------------------------------

		if ($search = ee()->low_reorder_set_model->get_search_params($params))
		{
			$search_where = $this->_search_where($search, 'd.');
		}

		// --------------------------------------
		//	Site id
		// --------------------------------------

		$site_ids = (REQ == 'CP')
		          ? array($this->site_id)
		          : array_values(ee()->TMPL->site_ids);

		// --------------------------------------
		//	Get channel entries
		// --------------------------------------

		ee()->db->select('DISTINCT(t.entry_id), t.channel_id, t.title, t.status, t.url_title')
		             ->from('channel_titles t')
		             ->where_in('t.site_id', $site_ids);

		// Limit by channel_ids
		if ( ! empty($params['channel_id']))
		{
			// Determine which statuses to filter by
			list($channel_ids, $in) = low_explode_param($params['channel_id']);

			// Adjust query accordingly
			ee()->db->{($in ? 'where_in' : 'where_not_in')}('t.channel_id', $channel_ids);
		}

		// Limit by entry_ids
		if ( ! empty($params['entry_id']))
		{
			// Determine which statuses to filter by
			list($entry_ids, $in) = low_explode_param($params['entry_id']);

			// Adjust query accordingly
			ee()->db->{($in ? 'where_in' : 'where_not_in')}('t.entry_id', $entry_ids);
		}

		// Limit by status
		if ( ! empty($params['status']))
		{
			// Determine which statuses to filter by
			list($status, $in) = low_explode_param($params['status']);

			// Adjust query accordingly
			ee()->db->{($in ? 'where_in' : 'where_not_in')}('t.status', $status);
		}

		// Limit by category
		if ( ! empty($params['category']))
		{
			// Determine which categories to filter by
			list($categories, $in) = low_explode_param($params['category']);

			// Join table
			ee()->db->join('category_posts cp', 't.entry_id = cp.entry_id');
			ee()->db->{($in ? 'where_in' : 'where_not_in')}('cp.cat_id', $categories);
		}

		// Hide expired entries
		if (@$params['show_expired'] != 'yes')
		{
			ee()->db->where(sprintf("(t.expiration_date = 0 OR t.expiration_date >= '%s')",
				ee()->localize->now));
		}

		// Hide expired entries
		if (@$params['show_future_entries'] != 'yes')
		{
			ee()->db->where('t.entry_date <=', ee()->localize->now);
		}

		// Sticky only
		if (@$params['sticky'] == 'yes')
		{
			ee()->db->where('t.sticky', 'y');
		}

		// Limit by where search
		if ( ! empty($search_where))
		{
			ee()->db->join('channel_data d', 't.entry_id = d.entry_id');
			ee()->db->where(implode(' AND ', $search_where), NULL, FALSE);
		}

		// Order by given set order or entry date as fallback
		if ($set_order !== FALSE)
		{
			if ($set_order)
			{
				// Reverse it
				if (@$params['sort'] == 'desc')
				{
					$set_order = array_reverse($set_order);
				}

				ee()->db->order_by('FIELD(t.entry_id,'.implode(',', $set_order).')', FALSE, FALSE);
			}
			else
			{
				// Order by custom order, fallback to entry date
				ee()->db->order_by('t.entry_date', 'desc');
			}
		}

		// Optional limit
		if ($limit)
		{
			ee()->db->limit($limit);
		}

		$query = ee()->db->get();

		// --------------------------------------
		// Return the retrieved entries
		// --------------------------------------

		return $query->result_array();
	}

	/**
	 * Create a list of where-clauses for given search parameters
	 *
	 * @access     private
	 * @param      array
	 * @param      string
	 * @return     array
	 */
	private function _search_where($search = array(), $prefix = '')
	{
		// --------------------------------------
		// Initiate where array
		// --------------------------------------

		$where = array();

		// --------------------------------------
		// Get field ids for given search fields
		// --------------------------------------

		$fields = $this->_get_channel_fields();

		// --------------------------------------
		// Loop through search filters and create where clause accordingly
		// --------------------------------------

		foreach ($search AS $key => $val)
		{
			// Skip non-existent fields
			if ( ! isset($fields[$key])) continue;

			// Initiate some vars
			$exact = $all = FALSE;
			$field = $prefix.'field_id_'.$fields[$key];

			// Exact matches
			if (substr($val, 0, 1) == '=')
			{
				$val   = substr($val, 1);
				$exact = TRUE;
			}

			// All items? -> && instead of |
			if (strpos($val, '&&') !== FALSE)
			{
				$all = TRUE;
			}

			// Convert parameter to bool and array
			list($items, $in) = low_explode_param($val);

			// Init sql for where clause
			$sql = array();

			// Loop through each sub-item of the filter an create sub-clause
			foreach ($items AS $item)
			{
				// Convert IS_EMPTY constant to empty string
				$empty = ($item == 'IS_EMPTY');
				$item  = str_replace('IS_EMPTY', '', $item);

				// greater/less than matches
				if (preg_match('/^([<>]=?)(\d+)$/', $item, $matches))
				{
					$gtlt = $matches[1];
					$item = $matches[2];
				}
				else
				{
					$gtlt = FALSE;
				}

				// whole word? Regexp search
				if (substr($item, -2) == '\W')
				{
					$operand = $in ? 'REGEXP' : 'NOT REGEXP';
					$item    = '[[:<:]]'.preg_quote(substr($item, 0, -2)).'[[:>:]]';
				}
				else
				{
					// Not a whole word
					if ($exact || $empty)
					{
						// Use exact operand if empty or = was the first char in param
						$operand = $in ? '=' : '!=';
						$item = "'".ee()->db->escape_str($item)."'";
					}
					// Greater/Less than option
					elseif ($gtlt !== FALSE)
					{
						$operand = $gtlt;
						$item = "'".ee()->db->escape_str($item)."'";
					}
					else
					{
						// Use like operand in all other cases
						$operand = $in ? 'LIKE' : 'NOT LIKE';
						$item = "'%".ee()->db->escape_str($item)."%'";
					}
				}

				// Add sub-clause to this statement
				$sql[] = sprintf("(%s %s %s)", $field, $operand, $item);
			}

			// Inclusive or exclusive
			$andor = $all ? ' AND ' : ' OR ';

			// Add complete clause to where array
			$where[] = '('.implode($andor, $sql).')';
		}

		// --------------------------------------
		// Where now contains a list of clauses
		// --------------------------------------

		return $where;
	}

	/**
	 * Get channel fields from Cache or DB
	 *
	 * @access     private
	 * @return     array
	 */
	private function _get_channel_fields()
	{
		// --------------------------------------
		// Try and get channel field data from cache
		// --------------------------------------

		if ( ! ($fields = low_get_cache('channel', 'custom_channel_fields')))
		{
			// Load channel fields API
			ee()->load->library('api');
			ee()->api->instantiate('channel_fields');

			// Call API
			$fields = ee()->api_channel_fields->fetch_custom_channel_fields();

			// Register to cache
			foreach ($fields AS $key => $val)
			{
				low_set_cache('channel', $key, $val);
			}

			// Shortcut
			$fields = $fields['custom_channel_fields'];
		}

		// --------------------------------------
		// Return the custom channel fields
		// --------------------------------------

		return $fields[$this->site_id];
	}

	// --------------------------------------------------------------------

} // End class low_reorder_base