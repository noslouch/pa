<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelImagesUpdate_520
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
		// -----------------------------------------
		// Add sizes_metadata Column
		// -----------------------------------------
		if ($this->EE->db->field_exists('is_draft', 'channel_images') == FALSE)
		{
			$fields = array( 'is_draft'	=> array('type' => 'TINYINT',		'unsigned' => TRUE, 'default' => 0) );
			$this->EE->dbforge->add_column('channel_images', $fields, 'member_id');
		}

		//exit();
	}

	// ********************************************************************************* //

}

/* End of file 500.php */
/* Location: ./system/expressionengine/third_party/channel_images/updates/520.php */