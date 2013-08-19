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
 * @copyright      Copyright (c) 2009-2012, Low
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
	public $settings_exist = 'n';

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
		// Not sure if $data is actually stored in last_call...?

		// if ($this->EE->extensions->last_call !== FALSE)
		// {
		// 	$data = $this->EE->extensions->last_call;
		// }

		// -------------------------------------------
		// Make sure we get all posted categories,
		// including parents if necessary
		// -------------------------------------------

		// Load channel categories API
		$this->EE->load->library('api');
		$this->EE->api->instantiate('channel_categories');

		// Get categories either from data or POST
		$categories = (isset($data['revision_post']['category']))
			? $data['revision_post']['category']
			: $this->EE->input->post('category');

		// Make sure we get the auto-assigned parents, too
		if ($this->EE->config->item('auto_assign_cat_parents') == 'y' &&
			! empty($this->EE->api_channel_categories->cat_parents))
		{
			$categories = array_unique(array_merge(
				$categories, $this->EE->api_channel_categories->cat_parents
			));
		}

		// -------------------------------------------
		// Define array for new orders
		// -------------------------------------------

		$new_orders = array();

		// -------------------------------------------
		// Get sets for this channel
		// -------------------------------------------

		$sets = $this->EE->low_reorder_set_model->get_by_channel($meta['channel_id']);

		foreach ($sets AS $set)
		{
			// Use posted category IDs for Sort By Single Category
			$cat_ids = ($set['cat_option'] == 'one') ? $categories : array(0);

			// Remove references to this entry id from other categories
			$this->EE->low_reorder_order_model->purge_others($set['set_id'], $cat_ids, $entry_id);

			// Create array of all the Orders that must be present
			foreach ($cat_ids AS $cat_id)
			{
				$new_orders[$set['set_id'].'-'.$cat_id] = array($entry_id);
			}

			// Loop through existing orders and update the order in $new_orders
			foreach ($this->EE->low_reorder_order_model->get_orders($set['set_id'], $cat_ids) AS $old)
			{
				$entry_ids = low_delinearize($old['sort_order']);

				// Append/Prepend if it's not there yet
				if ( ! in_array($entry_id, $entry_ids))
				{
					if ($set['new_entries'] == 'prepend')
					{
						$entry_ids = array_merge(array($entry_id), $entry_ids);
					}
					else
					{
						$entry_ids[] = $entry_id;
					}
				}

				$new_orders[$old['set_id'].'-'.$old['cat_id']] = $entry_ids;
			}

			// Loop through new orders and REPLACE INTO orders table
			foreach ($new_orders AS $key => $val)
			{
				list($set_id, $cat_id) = explode('-', $key);

				$this->EE->low_reorder_order_model->replace(array(
					'set_id' => $set_id,
					'cat_id' => $cat_id,
					'sort_order' => low_linearize($val)
				));
			}
		}

		// Cleans up the order table
		$this->EE->low_reorder_order_model->remove_rogues();

		// Play nice
		return $this->EE->extensions->last_call;
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

		if ($this->EE->extensions->last_call !== FALSE)
		{
			$query = $this->EE->extensions->last_call;
		}

		// -------------------------------------------
		// Fire for low_reorder only
		// -------------------------------------------

		if ($this->EE->TMPL->fetch_param('low_reorder') == 'yes')
		{
			// Get the set id
			$set_id = (int) low_get_cache($this->package, 'set_id');
			$cat_id = (int) low_get_cache($this->package, 'cat_id');

			$total_results = count($query);
			$reverse_count = $this->EE->TMPL->fetch_param('reverse_count', 'reverse_count');

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

} // End Class low_reorder_ext

/* End of file ext.low_reorder.php */