<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_200
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
		// Add the Fiel_id Column
		if ($this->EE->db->field_exists('field_id', 'channel_images') == FALSE)
		{
			$fields = array( 'field_id'	=> array('type' => 'MEDIUMINT',	'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'entry_id');
		}

		// Rename: weblog_id=>channel_id,
		if ($this->EE->db->field_exists('channel_id', 'channel_images') == FALSE)
		{
			$fields = array( 'weblog_id' => array('name' => 'channel_id', 'type' => 'TINYINT',	'unsigned' => TRUE, 'default' => 0),
							);
			$this->EE->dbforge->modify_column('channel_images', $fields);
		}

		//order=>image_order
		if ($this->EE->db->field_exists('image_order', 'channel_images') == FALSE)
		{
			$fields = array('`order`' => array('name' => 'image_order', 'type' => 'SMALLINT',	'unsigned' => TRUE, 'default' => 1)
							);
			$this->EE->dbforge->modify_column('channel_images', $fields);
		}
	}

	// ********************************************************************************* //

}

/* End of file 200.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/200.php */