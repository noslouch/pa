<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include base class
if ( ! class_exists('Low_reorder_base'))
{
	require_once(PATH_THIRD.'low_reorder/base.low_reorder.php');
}

/**
 * Low Reorder Extension class
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2013, Low
 */
class Low_reorder_ext extends Low_reorder_base
{
	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	 * Do settings exist?
	 *
	 * @var        string	y|n
	 * @access     public
	 */
	public $settings_exist = 'y';

	/**
	 * Required?
	 *
	 * @var        array
	 * @access     public
	 */
	public $required_by = array('module');

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access     public
	 * @param      mixed     Array with settings or FALSE
	 * @return     null
	 */
	public function __construct($settings = array())
	{
		// Get global instance
		parent::__construct();

		// Force settings array
		if ( ! is_array($settings))
		{
			$settings = array();
		}

		// Set settings
		$this->settings = array_merge($this->default_settings, $settings);
	}

	/**
	 * Settings
	 *
	 * @access     public
	 * @param      array
	 * @return     array
	 */
	public function settings()
	{
		// -------------------------------------------
		// Get member groups with access to CP
		// -------------------------------------------

		$query = ee()->db->select('group_id, group_title')
		       ->from('member_groups')
		       ->where('can_access_cp', 'y')
		       ->order_by('group_title')
		       ->get();

		$groups = low_flatten_results($query->result_array(), 'group_title', 'group_id');

		// -------------------------------------------
		// Return list of groups
		// -------------------------------------------

		return array(
			'can_create_sets' => array('ms',
				$groups,
				$this->default_settings['can_create_sets']
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Add/modify entry in sort orders
	 *
	 * @access      public
	 * @param       int
	 * @param       array
	 * @param       array
	 * @return      void
	 */
	public function entry_submission_end($entry_id, $meta, $data)
	{
		// -------------------------------------------
		// Not changing anything; get return value from last_call
		// -------------------------------------------

		$return = ee()->extensions->last_call;

		// -------------------------------------------
		// Get sets for this channel
		// -------------------------------------------

		$sets = ee()->low_reorder_set_model->get_by_channel($meta['channel_id']);
		$sets = low_associate_results($sets, 'set_id');

		// If no sets are found, just bail out early
		if (empty($sets)) return $return;

		// -------------------------------------------
		// Define array for new orders, set needle
		// -------------------------------------------

		$new_orders = $old_orders = array();

		// This entry's pipe-separated id
		$needle = "|{$entry_id}|";

		// -------------------------------------------
		// Get all old orders for the sets found
		// -------------------------------------------

		ee()->db->where_in('set_id', array_keys($sets));
		$old = ee()->low_reorder_order_model->get_all();

		foreach ($old AS $row)
		{
			$key = $row['set_id'].'-'.$row['cat_id'];
			$old_orders[$key] = $row['sort_order'];
		}

		unset($old);

		// -------------------------------------------
		// Make sure we get all posted categories,
		// including parents if necessary
		// -------------------------------------------

		// Get categories either from data or POST
		$categories = (isset($data['revision_post']['category']))
			? $data['revision_post']['category']
			: ee()->input->post('category');

		if ( ! is_array($categories))
		{
			$categories = array(0);
		}

		// Make sure we get the auto-assigned parents, too
		if (ee()->config->item('auto_assign_cat_parents') == 'y')
		{
			// Load channel categories API
			ee()->load->library('api');
			ee()->api->instantiate('channel_categories');

			if ( ! empty(ee()->api_channel_categories->cat_parents))
			{
				$categories = array_unique(array_merge(
					$categories, ee()->api_channel_categories->cat_parents
				));
			}
		}

		// Is this entry sticky?
		$sticky = (@$meta['sticky'] == 'y');

		// -------------------------------------------
		// Loop through applicable sets, populate new_orders
		// -------------------------------------------

		foreach ($sets AS $set)
		{
			// Get the set's parameters
			$params = ee()->low_reorder_set_model->get_params($set['parameters']);

			// Skip non-sticky if sticky only
			if (isset($params['sticky']) && $params['sticky'] == 'yes' && ! $sticky) continue;

			// Use posted category IDs for Sort By Single Category
			$cat_ids = ($set['cat_option'] == 'one') ? $categories : array(0);

			// Create array of all the Orders that must be present
			foreach ($cat_ids AS $cat_id)
			{
				// Set the key
				$key = $set['set_id'].'-'.$cat_id;
				$val = FALSE;

				// Check old orders
				if (isset($old_orders[$key]))
				{
					$old_val = $old_orders[$key];

					// If entry id is not present in old order
					if (strpos($old_val, $needle) === FALSE)
					{
						$val = ($set['new_entries'] == 'prepend')
						     ? $needle.ltrim($old_val, '|')
						     : rtrim($old_val, '|').$needle;
					}

					// remove reference from $old_orders
					unset($old_orders[$key]);
				}
				else
				{
					$val = $needle;
				}

				// Only add new order if we have a valid value
				if ($val !== FALSE)
				{
					$new_orders[$key] = $val;
				}
			}
		}

		// -------------------------------------------
		// Loop through remaining old orders and remove references
		// -------------------------------------------

		foreach ($old_orders AS $key => $val)
		{
			// Get set and cat IDs
			list($set_id, $cat_id) = explode('-', $key);

			// Remove lint
			if (($sets[$set_id]['cat_option'] == 'one' && $cat_id == 0) ||
				($sets[$set_id]['cat_option'] != 'one' && $cat_id > 0))
			{
				ee()->db->where('cat_id', $cat_id);
				ee()->low_reorder_order_model->delete($set_id, 'set_id');
				continue;
			}

			// See if entry id is present in old order,
			// if so, remove it and add new order to array
			if (strpos($val, $needle) !== FALSE)
			{
				$new_orders[$key] = str_replace($needle, '|', $val);
			}
		}

		// -------------------------------------------
		// Update the new orders
		// -------------------------------------------

		if ($new_orders)
		{
			$values = array();

			// Loop through new orders and REPLACE INTO orders table
			foreach ($new_orders AS $key => $val)
			{
				list($set_id, $cat_id) = explode('-', $key);

				$values[] = sprintf("('%s', '%s', '%s')",
					$set_id,
					$cat_id,
					$val
				);
			}

			ee()->db->query(sprintf(
				'REPLACE INTO `%s` (`set_id`, `cat_id`, `sort_order`) VALUES %s',
				ee()->low_reorder_order_model->table(),
				implode(",\n", $values)
			));

			// Cleans up the order table
			ee()->low_reorder_order_model->remove_rogues();
		}

		// Play nice
		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Add reverse count to channel entries
	 *
	 * @access      public
	 * @param       object
	 * @param       array
	 * @return      array
	 */
	public function channel_entries_query_result($obj, $query)
	{
		// -------------------------------------------
		// Get the latest version of $query
		// -------------------------------------------

		if (ee()->extensions->last_call !== FALSE)
		{
			$query = ee()->extensions->last_call;
		}

		// -------------------------------------------
		// Fire for low_reorder only
		// -------------------------------------------

		if (ee()->TMPL->fetch_param('low_reorder') == 'yes')
		{
			// Get the set id
			$set_id = (int) low_get_cache($this->package, 'set_id');
			$cat_id = (int) low_get_cache($this->package, 'cat_id');

			$total_results = count($query);
			$reverse_count = ee()->TMPL->fetch_param('reverse_count', 'reverse_count');

			foreach ($query AS &$row)
			{
				// Add set id & cat id
				$row['low_reorder_set_id'] = $set_id;
				$row['low_reorder_category_id'] = $cat_id;

				// Add reverse count
				if ( ! isset($row[$reverse_count]))
				{
					$row[$reverse_count] = $total_results--;
				}
			}
		}

		// -------------------------------------------
		// Return (modified) query
		// -------------------------------------------

		return $query;
	}

	// --------------------------------------------------------------------

	/**
	 * Order Low Search results by Low Reorder Set
	 *
	 * @access      public
	 * @param       array
	 * @return      array
	 */
	public function low_search_post_search($params)
	{
		// -------------------------------------------
		// Get the latest version of $params
		// -------------------------------------------

		if (ee()->extensions->last_call !== FALSE)
		{
			$params = ee()->extensions->last_call;
		}

		// -------------------------------------------
		// Get the ordered entry IDs
		// -------------------------------------------

		return $this->_get_ordered_ids($params);
	}

	/**
	 * Order Playa entries by Low Reorder Set
	 *
	 * @access      public
	 * @return      void
	 */
	public function playa_parse_relationships()
	{
		ee()->TMPL->tagparams = $this->_get_ordered_ids(ee()->TMPL->tagparams);
	}

	// --------------------------------------------------------------------

	/**
	 * Set fixed_order param based on orderby param
	 *
	 * @access     private
	 * @param      array
	 * @return     array
	 */
	private function _get_ordered_ids($params)
	{
		// -------------------------------------------
		// Do we have to?
		// -------------------------------------------

		if ( ! (isset($params['orderby']) && substr($params['orderby'], 0, 12) == 'low_reorder:'))
		{
			return $params;
		}

		ee()->TMPL->log_item('Low Reorder: Found orderby Low Reorder set parameter');

		// -------------------------------------------
		// Get reorder parameters
		// -------------------------------------------

		$reorder_params = array(
			'set'      => substr($params['orderby'], 12),
			'category' => isset($params['category']) ? $params['category'] : ''
		);

		// -------------------------------------------
		// Trick the template parser
		// -------------------------------------------

		$old_tagdata = ee()->TMPL->tagdata;
		$old_tagparams = ee()->TMPL->tagparams;

		ee()->TMPL->tagdata = '';
		ee()->TMPL->tagparams = $reorder_params;

		// Include the Low Reorder Mod file
		if ( ! class_exists('Low_reorder'))
		{
			ee()->TMPL->log_item('Low Reorder: Including Low Reorder module file');

			include(PATH_THIRD.'low_reorder/mod.low_reorder.php');
		}

		// Instatiate object
		$Low_reorder = new Low_reorder;

		ee()->TMPL->log_item('Low Reorder: Calling Low_reorder::entry_ids()');

		// Get the entry_ids
		$entry_ids = $Low_reorder->entry_ids();

		// And restore
		ee()->TMPL->tagdata = $old_tagdata;
		ee()->TMPL->tagparams = $old_tagparams;

		// -------------------------------------------
		// Filter the entry ids
		// -------------------------------------------

		if ( ! empty($params['entry_id']) && ! empty($entry_ids))
		{
			ee()->TMPL->log_item('Low Reorder: Limiting Low Reorder set ids to entry_id param');

			$entry_ids = implode('|', array_intersect(
				explode('|', $entry_ids),
				explode('|', $params['entry_id'])
			));
		}

		// -------------------------------------------
		// Set params accordingly
		// -------------------------------------------

		// Force no_results by setting entry_id to -1
		if (empty($entry_ids))
		{
			ee()->TMPL->log_item('Low Reorder: No entry ids found');
			$params['entry_id'] = '-1';
		}
		else
		{
			ee()->TMPL->log_item('Low Reorder: Setting fixed_order param to found ids');
			unset($params['entry_id']);
			$params['fixed_order'] = $entry_ids;
		}

		// remove orderby
		unset($params['orderby']);

		// And return the parameters again
		return $params;
	}

} // End Class low_reorder_ext

/* End of file ext.low_reorder.php */