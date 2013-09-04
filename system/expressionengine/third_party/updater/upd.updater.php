<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include_once dirname(dirname(__FILE__)).'/updater/config.php';

/**
 * Install / Uninstall and updates the modules
 *
 * @package         DevDemon_Updater
 * @version         2.0
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2012 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com/updater/
 * @see             http://expressionengine.com/user_guide/development/module_tutorial.html#update_file
 */
if (class_exists('Updater_upd') == false) {

class Updater_upd
{
    /**
     * Module version
     *
     * @var string
     * @access public
     */
    public $version = UPDATER_VERSION;

    /**
     * Module Short Name
     *
     * @var string
     * @access private
     */
    private $module_name = UPDATER_CLASS_NAME;

    /**
     * Has Control Panel Backend?
     *
     * @var string
     * @access private
     */
    private $has_cp_backend = 'y';

    /**
     * Has Publish Fields?
     *
     * @var string
     * @access private
     */
    private $has_publish_fields = 'n';


    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->EE =& get_instance();
    }

    // ********************************************************************************* //

    /**
     * Installs the module
     *
     * Installs the module, adding a record to the exp_modules table,
     * creates and populates and necessary database tables,
     * adds any necessary records to the exp_actions table,
     * and if custom tabs are to be used, adds those fields to any saved publish layouts
     *
     * @access public
     * @return boolean
     **/
    public function install()
    {
        // Load dbforge
        $this->EE->load->dbforge();

        //----------------------------------------
        // EXP_MODULES
        //----------------------------------------
        $this->EE->db->set('module_name', ucfirst($this->module_name));
        $this->EE->db->set('module_version', $this->version);
        $this->EE->db->set('has_cp_backend', $this->has_cp_backend);
        $this->EE->db->set('has_publish_fields', $this->has_publish_fields);
        $this->EE->db->insert('modules');

        //----------------------------------------
        // Actions
        //----------------------------------------
        $this->EE->db->set('class', ucfirst($this->module_name));
        $this->EE->db->set('method', 'actionGeneralRouter');
        $this->EE->db->insert('actions');

        //----------------------------------------
        // EXP_MODULES
        // The settings column, Ellislab should have put this one in long ago.
        // No need for a seperate preferences table for each module.
        //----------------------------------------
        if ($this->EE->db->field_exists('settings', 'modules') == false) {
            $this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
        }

        //----------------------------------------
        // EXP_UPDATER_ADDONS
        //----------------------------------------
        $ci = array(
            'id'            => array('type' => 'INT',       'unsigned' => true, 'auto_increment' => true),
            'package'       => array('type' => 'VARCHAR',   'constraint' => '250', 'default' => ''),
            'addon'         => array('type' => 'VARCHAR',   'constraint' => '250', 'default' => ''),
            'addon_type'    => array('type' => 'VARCHAR',   'constraint' => '250', 'default' => ''),
            'label'         => array('type' => 'VARCHAR',   'constraint' => '250', 'default' => ''),
            'version'       => array('type' => 'VARCHAR',   'constraint' => '250', 'default' => ''),
            'installed'     => array('type' => 'TINYINT',   'constraint' => '1', 'default' => '0'),
            'updated_at'    => array('type' => 'DATETIME'),
            'created_at'    => array('type' => 'DATETIME'),
        );

        $this->EE->dbforge->add_field($ci);
        $this->EE->dbforge->add_key('id', true);
        $this->EE->dbforge->add_key('addon');
        $this->EE->dbforge->add_key('package');
        $this->EE->dbforge->create_table('updater_addons', true);

        return true;
    }

    // ********************************************************************************* //

    /**
     * Uninstalls the module
     *
     * @access public
     * @return Boolean false if uninstall failed, true if it was successful
     **/
    public function uninstall()
    {
        // Load dbforge
        $this->EE->load->dbforge();

        $this->EE->dbforge->drop_table('updater_addons');

        $this->EE->db->where('module_name', ucfirst($this->module_name));
        $this->EE->db->delete('modules');
        $this->EE->db->where('class', ucfirst($this->module_name));
        $this->EE->db->delete('actions');

        return true;
    }

    // ********************************************************************************* //

    /**
     * Updates the module
     *
     * This function is checked on any visit to the module's control panel,
     * and compares the current version number in the file to
     * the recorded version in the database.
     * This allows you to easily make database or
     * other changes as new versions of the module come out.
     *
     * @access public
     * @return Boolean false if no update is necessary, true if it is.
     **/
    public function update($current = '')
    {
        // Are they the same?
        if ($current >= $this->version) {
            return false;
        }

        $current = str_replace('.', '', $current);

        // Two Digits? (needs to be 3)
        if (strlen($current) == 2) {
            $current .= '0';
        }

        $update_dir = PATH_THIRD.strtolower($this->module_name).'/updates/';

        // Does our folder exist?
        if (@is_dir($update_dir) === true) {
            // Loop over all files
            $files = @scandir($update_dir);

            if (is_array($files) == true) {

                foreach ($files as $file) {

                    if ($file == '.' OR $file == '..' OR strtolower($file) == '.ds_store') continue;

                    // Get the version number
                    $ver = substr($file, 0, -4);

                    // We only want greater ones
                    if ($current >= $ver) continue;

                    require $update_dir . $file;
                    $class = 'UpdaterUpdate_' . $ver;
                    $UPD = new $class();
                    $UPD->do_update();
                }
            }
        }

        // Upgrade The Module
        $this->EE->db->set('module_version', $this->version);
        $this->EE->db->where('module_name', ucfirst($this->module_name));
        $this->EE->db->update('exp_modules');

        return true;
    }

    // ********************************************************************************* //

} // END CLASS

} // END IF

/* End of file upd.updater.php */
/* Location: ./system/expressionengine/third_party/updater/upd.updater.php */
