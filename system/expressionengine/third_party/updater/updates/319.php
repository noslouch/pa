<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class UpdaterUpdate_319 extends Updater_upd
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
        parent::__construct();

        // Load dbforge
        $this->EE->load->dbforge();
    }

    // ********************************************************************************* //

    public function do_update()
    {
        //----------------------------------------
        // Change Actions
        //----------------------------------------
        $this->EE->db->set('method', 'actionGeneralRouter');
        $this->EE->db->where('class', 'Updater');
        $this->EE->db->where('method', 'ACT_general_router');
        $this->EE->db->update('exp_actions');

        return true;
    }

    // ********************************************************************************* //

}

/* End of file 250.php */
/* Location: ./system/expressionengine/third_party/credits/updates/250.php */
