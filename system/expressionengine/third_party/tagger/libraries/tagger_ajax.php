<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tagger AJAX File
 *
 * @package			DevDemon_Tagger
 * @version			2.1.2
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Tagger_ajax
{

	public function __construct()
	{
		$this->EE =& get_instance();

		if ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		else $this->site_id = $this->EE->config->item('site_id');

		$this->EE->tagger_helper->define_theme_url();
		$this->EE->lang->loadfile('tagger');
		$this->EE->load->library('tagger_helper');
		$this->EE->tagger_helper->define_theme_url();

		$this->EE->config->load('tagger_config');
	}

	// ********************************************************************************* //

	public function tag_search()
	{
		header('Content-Type: text/html; charset=UTF-8');

		$this->EE->db->select('tag_name, tag_id');
		$this->EE->db->from('exp_tagger');
		$this->EE->db->like('tag_name', $this->EE->input->get('term'), 'both');
		$this->EE->db->where('site_id', $this->site_id);
		$this->EE->db->order_by('tag_name');
		$this->EE->db->limit(30);
		$query = $this->EE->db->get();

		$tags = array();
		foreach ($query->result() as $row)
		{
			$tags[] = array('id' => $row->tag_id, 'value' => $row->tag_name, 'label' => $row->tag_name);
		}

		exit($this->EE->tagger_helper->generate_json($tags));
	}

	// ********************************************************************************* //

	public function add_to_group()
	{
		$groups = $this->EE->input->post('groups');
		$tag_id = $this->EE->input->post('tag_id');

		// First check if groups is empty
		if (is_array($groups) == FALSE OR empty($groups) == TRUE OR $groups == FALSE)
		{
			echo "Groups is Empty \n";

			// Delete all groups from this Tag
			$this->EE->db->where('tag_id', $tag_id)->delete('tagger_groups_entries');
		}
		else
		{
			echo "Groups Found \n";

			// Delete all groups from this Tag
			$this->EE->db->where('tag_id', $tag_id)->delete('tagger_groups_entries');

			// Then add only what we need
			foreach ($groups as $group_id)
			{
				$this->EE->db->insert('tagger_groups_entries', array('tag_id' => $tag_id, 'group_id' => $group_id));
			}

		}

		echo 'DONE';
		exit();
	}

	// ********************************************************************************* //

	public function tags_dt()
	{
		$this->EE->load->helper('form');

		//----------------------------------------
		// Grab All Groups
		//----------------------------------------
		$groups = array();
		$this->EE->db->select('group_id, group_title');
		$this->EE->db->from('exp_tagger_groups');
		$this->EE->db->where('site_id', $this->site_id);
		$query = $this->EE->db->get();
		foreach($query->result() as $row) $groups[$row->group_id] = $row->group_title;

		$data = array();
		$data['aaData'] = array();
		$data['iTotalDisplayRecords'] = 0; // Total records, after filtering (i.e. the total number of records after filtering has been applied - not just the number of records being returned in this result set)
		$data['sEcho'] = $this->EE->input->get_post('sEcho');

		// Total records, before filtering (i.e. the total number of records in the database)
		$data['iTotalRecords'] = $this->EE->db->count_all('exp_tagger');

		//----------------------------------------
		// Column Search
		//----------------------------------------
		$tag_search = FALSE;
		if ($this->EE->input->get_post('sSearch') != FALSE)
		{
			$tag_search = $this->EE->input->get_post('sSearch');
		}

		//----------------------------------------
		// Total after filter
		//----------------------------------------
		$this->EE->db->select('COUNT(*) as total_records', FALSE);
		$this->EE->db->from('exp_tagger tg');
		$this->EE->db->where('tg.site_id', $this->site_id);
		if ($tag_search != FALSE) $this->EE->db->like('tg.tag_name', $tag_search, 'both');
		$query = $this->EE->db->get();
		$data['iTotalDisplayRecords'] = $query->row('total_records');
		$query->free_result();

		//----------------------------------------
		// Real Query
		//----------------------------------------
		$this->EE->db->select('tg.*');
		$this->EE->db->from('exp_tagger tg');
		$this->EE->db->where('tg.site_id', $this->site_id);

		//----------------------------------------
		// Sort By
		//----------------------------------------
		$sort_cols = $this->EE->input->get_post('iSortingCols');

		for ($i = 0; $i < $sort_cols; $i++)
		{
			$col = $this->EE->input->get_post('iSortCol_'.$i);
			$sort =  $this->EE->input->get_post('sSortDir_'.$i);

			switch ($col)
			{
				case 1: // Tag Name
					$this->EE->db->order_by('tg.tag_name', $sort);
					break;
				case 2: // Total Entries
					$this->EE->db->order_by('tg.total_entries', $sort);
					break;
			}
		}

		//----------------------------------------
		// Limit
		//----------------------------------------
		$limit = 10;
		if ($this->EE->input->get_post('iDisplayLength') !== FALSE)
		{
			$limit = $this->EE->input->get_post('iDisplayLength');
			if ($limit < 1) $limit = 999999;
		}

		//----------------------------------------
		// Offset
		//----------------------------------------
		$offset = 10;
		if ($this->EE->input->get_post('iDisplayStart') !== FALSE)
		{
			$offset = $this->EE->input->get_post('iDisplayStart');
		}

		if ($tag_search != FALSE) $this->EE->db->like('tg.tag_name', $tag_search, 'both');

		$this->EE->db->limit($limit, $offset);
		$query = $this->EE->db->get();


		//----------------------------------------
		// Loop Over all
		//----------------------------------------
		foreach ($query->result() as $row)
		{
			//----------------------------------------
			// Group Relationships
			//----------------------------------------
			$selected = array();
			$temp = $this->EE->db->select('group_id')->from('exp_tagger_groups_entries')->where('tag_id', $row->tag_id)->get();
			foreach($temp->result() as $sel) $selected[] = $sel->group_id;

			//----------------------------------------
			// Create Group TD
			//----------------------------------------
			if (empty($groups) == FALSE)
			{
				$td = form_multiselect('group[]', $groups, $selected, 'class="gSel" rel="'.$row->tag_id.'"');
				foreach($selected as $group_id) $td .= '<small>' . $groups[$group_id] . '</small>';
			}
			else
			{
				$td = '&nbsp;';
			}


			//----------------------------------------
			// Create TR row
			//----------------------------------------
			$trow = array();
			$trow[] = $row->tag_id;
			$trow[] = $row->tag_name;
			$trow[] = $row->total_entries;
			$trow[] = $td;
			$trow[] = '<a href="#" class="EditTag"></a><a href="#" class="DelTag"></a>';
			$data['aaData'][] = $trow;
		}

		exit($this->EE->tagger_helper->generate_json($data));
	}

	// ********************************************************************************* //

	public function del_tag()
	{
		$tag_id = $this->EE->input->post('tag_id');

		// Delete from exp_tagger
		$this->EE->db->where('tag_id', $tag_id)->delete('exp_tagger');

		//Delete from exp_tagger_links
		$this->EE->db->where('tag_id', $tag_id)->delete('exp_tagger_links');

		//Delete from exp_tagger_groups_entries
		$this->EE->db->where('tag_id', $tag_id)->delete('exp_tagger_groups_entries');
	}

	// ********************************************************************************* //

	public function edit_tag()
	{
		$tag_id = $this->EE->input->post('tag_id');
		$tag = $this->EE->input->post('tag');

		// -----------------------------------------
		// Load Settings
		// -----------------------------------------
		$conf = $this->EE->config->item('tagger_defaults');

		// Grab Settings
		$query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name = 'Tagger'");
		if ($query->row('settings') != FALSE)
		{
			$settings = @unserialize($query->row('settings'));
			if ($settings != FALSE && isset($settings['site:'.$this->site_id]))
			{
				$conf = array_merge($conf, $settings['site:'.$this->site_id]);
			}
		}

		// lowecase?
		$lc = ($conf['lowercase_tags'] == 'yes') ? TRUE : FALSE;

		if ($lc == TRUE) $tag = strtolower($tag);

		// Update Tag
		$this->EE->db->set('tag_name', $tag);
		$this->EE->db->where('tag_id', $tag_id);
		$this->EE->db->update('exp_tagger');
	}

	// ********************************************************************************* //

	public function merge_tags()
	{
		$tags = $this->EE->input->post('tags');
		$tags = explode(',', $tags);

		foreach ($tags as $key => $tag)
		{
			$tag = trim($tag);
			if (is_numeric($tag) == FALSE) unset($tags[$key]);
		}

		// Lets check
		if (count($tags) < 2) exit('Not Enough');

		// Grab the master
		$master = $tags[0];
		unset($tags[0]);

		foreach ($tags as $tag_id)
		{
			// Grab all rels
			$query = $this->EE->db->select('rel_id, entry_id')->from('exp_tagger_links')->where('tag_id', $tag_id)->get();

			// Check each for duplicates
			foreach($query->result() as $row)
			{
				$q2 = $this->EE->db->select('rel_id')->from('exp_tagger_links')->where('tag_id', $master)->where('entry_id', $query->row('entry_id'))->get();

				// Duplicate? Remove it!
				if ($q2->num_rows() > 0)
				{
					$this->EE->db->query("DELETE FROM exp_tagger_links WHERE rel_id = " . $query->row('rel_id'));
				}
				else
				{
					$this->EE->db->set('tag_id', $master)->where('rel_id', $query->row('rel_id'))->update('exp_tagger_links');
				}
			}

			// Delete the other tag
			$this->EE->db->query("DELETE FROM exp_tagger WHERE tag_id = " . $tag_id);
		}

		// Count!
		$query = $this->EE->db->query('SELECT COUNT(*) as count FROM exp_tagger_links WHERE tag_id = '.$master);
		$this->EE->db->set('total_entries', $query->row('count'))->where('tag_id', $master)->update('exp_tagger');
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file tagger_ajax.php  */
/* Location: ./system/expressionengine/third_party/tagger/modules/libraries/tagger_ajax.php */
