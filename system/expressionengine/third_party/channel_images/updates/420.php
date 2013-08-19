<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_420
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
		// Add the member_id Column
		if ($this->EE->db->field_exists('member_id', 'channel_images') == FALSE)
		{
			$fields = array( 'member_id'	=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'channel_id');
		}

		// Grab all images!
		$query = $this->EE->db->select('ci.image_id, ct.author_id')->from('exp_channel_images ci')->join('exp_channel_titles ct', 'ct.entry_id = ci.entry_id', 'left')->get();

		foreach ($query->result() as $row)
		{
			$this->EE->db->where('image_id', $row->image_id);
			$this->EE->db->update('exp_channel_images', array('member_id' => $row->author_id));
		}

		$query->free_result();
	}

	// ********************************************************************************* //

}

/* End of file 420.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/420.php */