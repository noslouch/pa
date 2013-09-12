<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_320
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
		// Add the link_channel_id Column
		if ($this->EE->db->field_exists('link_channel_id', 'channel_images') == FALSE)
		{
			$fields = array( 'link_channel_id'	=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'link_entry_id');
		}

		// Add the link_field_id Column
		if ($this->EE->db->field_exists('link_field_id', 'channel_images') == FALSE)
		{
			$fields = array( 'link_field_id'	=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'link_channel_id');
		}
	}

	// ********************************************************************************* //

}

/* End of file 320.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/320.php */