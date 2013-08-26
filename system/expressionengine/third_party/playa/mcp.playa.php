<?php if (! defined('BASEPATH')) exit('Invalid file request');


/**
 * Playa Module CP Class
 *
 * @package   Playa
 * @author    Brandon Kelly <brandon@pixelandtonic.com>
 * @copyright Copyright (c) 2011 Pixel & Tonic, Inc
 */
class Playa_mcp {

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();

		// -------------------------------------------
		//  Load the helper
		// -------------------------------------------

		if (! class_exists('Playa_Helper'))
		{
			require_once PATH_THIRD.'playa/helper.php';
		}

		$this->helper = new Playa_Helper();
	}

	// --------------------------------------------------------------------

	/**
	 * Filter Entries
	 */
	function filter_entries()
	{
		if (! isset($_POST['field_id']) || ! isset($_POST['field_name'])) exit('Invalid input data');

		$_POST['field_name'] = preg_replace('/[^a-z0-9\-_\[\]]/i', '', $_POST['field_name']);

		// -------------------------------------------
		//  Main params
		// -------------------------------------------

		$params = array();

		$params['show_expired'] = ($this->EE->input->post('expired') == 'y' ? 'yes' : '');
		$params['show_future_entries'] = ($this->EE->input->post('future') == 'y' ? 'yes' : '');

		if (isset($_POST['site']))     $params['site_id']    = $this->EE->input->post('site');
		if (isset($_POST['channel']))  $params['channel_id'] = $this->EE->input->post('channel');
		if (isset($_POST['category'])) $params['category']   = $this->EE->input->post('category');
		if (isset($_POST['author']))   $params['author_id']  = $this->EE->input->post('author');
		if (isset($_POST['member_groups']))   $params['member_groups']  = $this->EE->input->post('member_groups');
		if (isset($_POST['status']))   $params['status']     = $this->EE->input->post('status');
		if (isset($_POST['keywords'])) $params['keywords']   = $this->EE->input->post('keywords');

		// -------------------------------------------
		//  Limit or Order
		// -------------------------------------------

		if (isset($_POST['limit']) && $_POST['limit'])
		{
			$params['orderby'] = 'entry_date';
			$params['sort'] = ($this->EE->input->post('limitby') == 'newest') ? 'DESC' : 'ASC';
			$params['limit'] = $this->EE->input->post('limit');
		}
		else
		{
			if (isset($_POST['orderby'])) $params['orderby'] = $this->EE->input->post('orderby');
			if (isset($_POST['sort'])) $params['sort'] = $this->EE->input->post('sort');
		}

		// -------------------------------------------
		//  Get the entries
		// -------------------------------------------

		$entries = $this->helper->entries_query($params);

		if ($entries)
		{
			// -------------------------------------------
			//  post-query ordering
			// -------------------------------------------

			if (isset($_POST['limit']))
			{
				$this->helper->sort_entries($entries, $this->EE->input->post('sort'), $this->EE->input->post('orderby'));
			}

			// -------------------------------------------
			//  Create the list and return
			// -------------------------------------------

			$field_id = $_POST['field_id'];
			$field_name = $_POST['field_name'];

			$selected_entry_ids = isset($_POST['selected_entry_ids']) && is_array($_POST['selected_entry_ids'])
				? $_POST['selected_entry_ids']
				: array();

			$r = $this->EE->load->view('droppanes_options_list', array(
				'field_id'           => $field_id,
				'field_name'         => $field_name,
				'entries'            => $entries,
				'selected_entry_ids' => $selected_entry_ids
			), TRUE);
		}
		else
		{
			$r = '';
		}

		exit($this->helper->strip_whitespace($r));
	}

}
