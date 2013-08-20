<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Reorder Set Model class
 *
 * @package         low_reorder
 * @author          Lodewijk Schutte ~ Low <hi@gotolow.com>
 * @link            http://gotolow.com/addons/low-reorder
 * @copyright       Copyright (c) 2011, Low
 */
class Low_reorder_set_model extends Low_reorder_model {

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Default set parameters
	 *
	 * @access      private
	 * @var         array
	 */
	private $default_params = array(
		'status' => 'open'
	);

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access      public
	 * @return      void
	 */
	function __construct()
	{
		// Call parent constructor
		parent::__construct();

		// Initialize this model
		$this->initialize(
			'low_reorder_sets',
			'set_id',
			array(
				'site_id'     => 'int(4) unsigned DEFAULT 1 NOT NULL',
				'set_label'   => 'varchar(100) NOT NULL',
				'set_name'    => 'varchar(50) NOT NULL',
				'set_notes'   => 'text NOT NULL',
				'new_entries' => "enum('append', 'prepend') DEFAULT 'append' NOT NULL",
				'clear_cache' => "enum('y', 'n') DEFAULT 'y' NOT NULL",
				'channels'    => 'varchar(255) NOT NULL',
				'cat_option'  => "enum('all', 'some', 'one', 'none') DEFAULT 'all' NOT NULL",
				'cat_groups'  => 'varchar(255) NOT NULL',
				'parameters'  => 'text NOT NULL',
				'permissions' => 'text NOT NULL'
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Installs given table
	 *
	 * @access      public
	 * @return      void
	 */
	public function install()
	{
		// Call parent install
		parent::install();

		// Add indexes to table
		$this->EE->db->query("ALTER TABLE {$this->table()} ADD INDEX (`site_id`)");
		$this->EE->db->query("ALTER TABLE {$this->table()} ADD INDEX (`set_name`)");
	}

	// --------------------------------------------------------------------

	/**
	 * Get all sets for given channel ID
	 *
	 * @access      public
	 * @param       int
	 * @return      array
	 */
	public function get_by_channel($channel_id)
	{
		$this->EE->db->like('channels', "|{$channel_id}|");
		return $this->get_all();
	}

	// --------------------------------------------------------------

	/**
	 * Get complete settings for given settings
	 *
	 * @access      public
	 * @param       mixed     encoded or decoded settings
	 * @return      array
	 */
	public function get_params($params = array())
	{
		// Try to decode
		if ( ! is_array($params) && ! ($params = json_decode($params, TRUE)))
		{
			$params = array();
		}

		// Merge default and given settings
		$params = array_merge($this->default_params, $params);

		// Convert values to string if necessary
		foreach ($params AS $key => &$val)
		{
			if (is_array($val))
			{
				$val = implode('|', $val);
			}
		}

		return array_filter($params);
	}

	// --------------------------------------------------------------

	/**
	 * Get complete settings for given settings
	 *
	 * @access      public
	 * @param       mixed     encoded or decoded settings
	 * @return      array
	 */
	public function get_search_params($params = array())
	{
		// Try to decode
		if ( ! is_array($params))
		{
			$params = $this->get_params($params);
		}

		// Init default
		$search = array();

		// Filter out search params and return them
		foreach ($params AS $key => $val)
		{
			if (substr($key, 0, 7) == 'search:')
			{
				$search[substr($key, 7)] = $val;
			}
		}

		return $search;
	}

	// --------------------------------------------------------------

	/**
	 * Get shortcut permissions for given permissions
	 *
	 * @access      public
	 * @param       array     current permissions
	 * @return      array
	 */
	public function get_permissions($given_permissions = array())
	{
		// Shortcut for current member group
		$group_id = $this->EE->session->userdata('group_id');

		// Default value for permissions
		$default = ($group_id == 1);

		// Default permissions
		$permissions = array(
			'can_edit'    => $default,
			'can_reorder' => $default
		);

		if ($group_id != 1)
		{
			// Get permission integer from settings
			$permission = isset($given_permissions[$group_id]) ? $given_permissions[$group_id] : 0;

			// Set permissions
			// 2: can do both
			if ($permission == 2)
			{
				$permissions['can_edit'] = $permissions['can_reorder'] = TRUE;
			}
			// 1: can only reorder
			elseif ($permission == 1)
			{
				$permissions['can_reorder'] = TRUE;
			}
		}

		// Return shortcut permissions
		return $permissions;
	}

	// --------------------------------------------------------------

	/**
	 * Check if given set_name is unique for given site
	 */
	public function name_is_unique($set_id, $set_name, $site_id = 0)
	{
		$count = $this->EE->db->where('set_id !=', $set_id)
		       ->where('set_name', $set_name)
		       ->where('site_id', $site_id)
		       ->count_all_results($this->table());

		return empty($count);
	}

} // End class

/* End of file Low_reorder_set_model.php */