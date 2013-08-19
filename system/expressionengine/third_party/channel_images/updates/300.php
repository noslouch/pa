<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_300
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
		// Add the link_image_id Column
		if ($this->EE->db->field_exists('link_image_id', 'channel_images') == FALSE)
		{
			$fields = array( 'link_image_id'	=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'channel_id');
		}

		// Add the link_entry_id Column
		if ($this->EE->db->field_exists('link_entry_id', 'channel_images') == FALSE)
		{
			$fields = array( 'link_entry_id'	=> array('type' => 'INT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'link_image_id');
		}

		// Add the mime Column
		if ($this->EE->db->field_exists('mime', 'channel_images') == FALSE)
		{
			$fields = array( 'mime'	=> array('type' => 'VARCHAR',	'constraint' => '20', 'default' => '') );
			$this->EE->dbforge->add_column('channel_images', $fields, 'extension');
		}
	}

	// ********************************************************************************* //

}

/* End of file 300.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/300.php */