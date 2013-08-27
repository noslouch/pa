<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tagger Module Model Class
 *
 * @package			DevDemon_Tagger
 * @version			2.1.2
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		Commercial
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/development/modules.html#control_panel_file
 */
class Tagger_model
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
	}

	// ********************************************************************************* //

	/**
	 * Grab all tags
	 *
	 * @param int $limit
	 * @access public
	 * @return array
	 */
	public function get_tags($limit=FALSE, $tag_id=FALSE)
	{
		$this->EE->db->select('*');
		$this->EE->db->from('exp_tagger');
		$this->EE->db->order_by('tag_name', 'asc');
		if ($tag_id != FALSE) $this->EE->db->where('tag_id', $tag_id);
		if ($limit != FALSE) $this->EE->db->limit($limit);
		$this->EE->db->where('site_id', $this->site_id);
		$query = $this->EE->db->get();

		if ($tag_id) $results = $query->row();
		else $results = $query->result();

		$query->free_result();

		return $results;
	}

	// ********************************************************************************* //

	/**
	 * Grab all tagger groups
	 *
	 * @param int $group_id
	 * @access public
	 * @return array
	 */
	public function get_groups($group_id=FALSE)
	{
		$this->EE->db->select('group_id, group_title, group_name, group_desc');
		$this->EE->db->from('exp_tagger_groups');
		if ($group_id != FALSE) $this->EE->db->where('group_id', $group_id);
		$this->EE->db->where('site_id', $this->site_id);
		$query = $this->EE->db->get();

		$results = $query->result();
		$query->free_result();

		return $results;
	}

	// ********************************************************************************* //

	/**
	 * Grab all group_entries
	 *
	 * @param int $limit
	 * @access public
	 * @return array
	 */
	public function get_groups_entries()
	{
		$this->EE->db->select('group_id, tag_id');
		$this->EE->db->from('exp_tagger_groups_entries ');
		$query = $this->EE->db->get();

		$results = array();

		foreach ($query->result() as $row)
		{
			$results[$row->tag_id][] = $row->group_id;
		}

		$query->free_result();

		return $results;
	}

	// ********************************************************************************* //



	// TEMP SOLUTION FOR EE 2.1.1 SIGH!!!
	public function _assign_libraries()
	{

	}



} // END CLASS

/* End of file tagger_model.php  */
/* Location: ./system/expressionengine/third_party/tagger/modules/models/tagger_model.php */
