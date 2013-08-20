<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Low Reorder Order Model class
 *
 * @package        low_reorder
 * @author         Lodewijk Schutte <hi@gotolow.com>
 * @link           http://gotolow.com/addons/low-reorder
 * @copyright      Copyright (c) 2009-2012, Low
 */
class Low_reorder_order_model extends Low_reorder_model {

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
			'low_reorder_orders',
			array('set_id', 'cat_id'),
			array(
				'sort_order' => 'TEXT'
			)
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Get orders by set_id and cat_id
	 *
	 * @access      public
	 * @param       array
	 * @return      void
	 */
	public function get_orders($set_id, $cat_ids = 0)
	{
		if ( ! is_array($cat_ids))
		{
			$cat_ids = array($cat_ids);
		}

		$this->EE->db->where('set_id', $set_id);
		$this->EE->db->where_in('cat_id', $cat_ids);
		return $this->get_all();
	}


	/**
	 * REPLACE INTO query
	 *
	 * @access      public
	 * @param       array
	 * @return      void
	 */
	public function replace($data = array())
	{
		$sql = $this->EE->db->insert_string(
			$this->table(),
			$data
		);

		$this->EE->db->query(str_replace('INSERT', 'REPLACE', $sql));
	}

	/**
	 * INSERT IGNORE query
	 *
	 * @access      public
	 * @param       array
	 * @return      void
	 */
	public function insert_ignore($data = array())
	{
		$sql = $this->EE->db->insert_string(
			$this->table(),
			$data
		);

		$this->EE->db->query(str_replace('INSERT', 'INSERT IGNORE', $sql));
	}

	// --------------------------------------------------------------------

	/**
	 * Remove given Entry ID from orders
	 *
	 * @access      public
	 * @param       int
	 * @param       array
	 * @param       int
	 * @return      void
	 */
	public function purge_others($set_id, $cat_ids, $entry_id)
	{
		// Bail out if no cat IDS
		if (empty($cat_ids)) return;

		$sql_cat_ids = implode(',', $cat_ids);
		$sql = "UPDATE {$this->table()} SET `sort_order` = REPLACE(`sort_order`, '|{$entry_id}|', '|')
				WHERE set_id = {$set_id}
				AND cat_id NOT IN ({$sql_cat_ids})";

		$this->EE->db->query($sql);
	}


	/**
	 * Remove rogue records
 	 *
	 * @access      public
	 * @return      void
	 */
	public function remove_rogues()
	{
		// Delete rogue records
		$this->EE->db->where('sort_order', '|');
		$this->EE->db->delete($this->table());
	}

	// --------------------------------------------------------------------

} // End class

/* End of file Low_reorder_order_model.php */