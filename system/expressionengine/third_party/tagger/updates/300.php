<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TaggerUpdate_300
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		// Load dbforge
		$this->EE->load->dbforge();
	}

	// ********************************************************************************* //

	public function do_update()
	{
		// Rename: order=>tag_order,
		if ($this->EE->db->field_exists('tag_order', 'tagger_links') == FALSE)
		{
			$fields = array( '`order`' => array('name' => 'tag_order', 'type' => 'SMALLINT', 'unsigned' => TRUE, 'default' => 0),
							);
			$this->EE->dbforge->modify_column('tagger_links', $fields);
		}

		// Rename: item_id=>entry_id
		if ($this->EE->db->field_exists('item_id', 'tagger_links') == TRUE)
		{
			$fields = array( 'item_id' => array('name' => 'entry_id', 'type' => 'INT', 'unsigned' => TRUE, 'default' => 0),
							);
			$this->EE->dbforge->modify_column('tagger_links', $fields);
		}

		// Add the channel_id
		if ($this->EE->db->field_exists('channel_id', 'tagger_links') == FALSE)
		{
			$fields = array( 'channel_id'	=> array('type' => 'SMALLINT',	'unsigned' => TRUE,	'default' => 0) );
			$this->EE->dbforge->add_column('tagger_links', $fields, 'entry_id');
		}

		// Add the field_id
		if ($this->EE->db->field_exists('field_id', 'tagger_links') == FALSE)
		{
			$fields = array( 'field_id'	=> array('type' => 'MEDIUMINT',	'unsigned' => TRUE,	'default' => 0) );
			$this->EE->dbforge->add_column('tagger_links', $fields, 'channel_id');
		}

		// Grab all tags (for channel_id)
		$query = $this->EE->db->select('tl.rel_id, ct.channel_id')->from('exp_tagger_links tl')->join('exp_channel_titles ct', 'ct.entry_id = tl.entry_id', 'left')->get();

		foreach ($query->result() as $row)
		{
			$this->EE->db->where('rel_id', $row->rel_id);
			$this->EE->db->update('exp_tagger_links', array('channel_id' => $row->channel_id));
		}

		$query->free_result();

		// Fill in field_id
		$query = $this->EE->db->select('group_id, field_id')->from('exp_channel_fields')->where('field_type', 'tagger')->get();

		foreach ($query->result() as $field)
		{
			// Grab Field Group data
			$q2 = $this->EE->db->select('channel_id')->from('exp_channels')->where('field_group', $field->group_id)->get();
			if ($q2->num_rows() == 0) continue;
			$channel_id = $q2->row('channel_id');

			$this->EE->db->where('channel_id', $channel_id);
			$this->EE->db->update('exp_tagger_links', array('field_id' => $field->field_id));

			$q2->free_result();
		}

		$query->free_result();
	}

	// ********************************************************************************* //

}

/* End of file 215.php */
/* Location: ./system/expressionengine/third_party/tagger/updates/215.php */