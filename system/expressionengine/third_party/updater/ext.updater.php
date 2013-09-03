<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
require_once dirname(dirname(__FILE__)).'/updater/config.php';

/**
 * Updater Module Extension File
 *
 * @package         DevDemon_Updater
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2012 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com
 * @see             http://expressionengine.com/user_guide/development/extensions.html
 */
class Updater_ext
{
    public $version         = UPDATER_VERSION;
    public $name            = 'Updater Extension';
    public $description     = 'Supports the Updater Module in various functions.';
    public $docs_url        = 'http://www.devdemon.com';
    public $settings_exist  = false;
    public $settings        = array();
    public $hooks           = array('cp_menu_array');

    // ********************************************************************************* //

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->EE =& get_instance();
        $this->site_id = $this->EE->config->item('site_id');
    }

    // ********************************************************************************* //

    /**
     * cp_menu_array
     *
     * @param array $menu
     * @access public
     * @see N/A
     * @return array
     */
    public function cp_menu_array($menu)
    {
        $this->EE->load->library('updater_helper');

        if ($this->EE->extensions->last_call !== false) {
            $menu = $this->EE->extensions->last_call;
        }

        $this->EE->lang->loadfile('updater');

        if ($this->EE->updater->settings['menu_link']['root'] == 'yes') {
            $menu['updater'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=updater';
        }


        if ($this->EE->updater->settings['menu_link']['tools'] == 'yes') {
            if (isset($menu['tools']) === true && is_array($menu['tools']) === true) {
                $menu['tools']['updater'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=updater';
            }
        }

        if ($this->EE->updater->settings['menu_link']['admin'] == 'yes') {
            if (isset($menu['admin']) === true && is_array($menu['admin']) === true) {
                $menu['admin']['updater'] = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=updater';
            }
        }

        return $menu;
    }

    // ********************************************************************************* //

    /**
     * Called by ExpressionEngine when the user activates the extension.
     *
     * @access      public
     * @return      void
     **/
    public function activate_extension()
    {
        foreach ($this->hooks as $hook) {

            $data = array(
                'class'     =>  __CLASS__,
                'method'    =>  $hook,
                'hook'      =>  $hook,
                'settings'  =>  serialize($this->settings),
                'priority'  =>  100,
                'version'   =>  $this->version,
                'enabled'   =>  'y'
            );

            // insert in database
            $this->EE->db->insert('exp_extensions', $data);
        }
    }

    // ********************************************************************************* //

    /**
     * Called by ExpressionEngine when the user disables the extension.
     *
     * @access      public
     * @return      void
     **/
    public function disable_extension()
    {
        $this->EE->db->where('class', __CLASS__);
        $this->EE->db->delete('exp_extensions');
    }

    // ********************************************************************************* //

    /**
     * Called by ExpressionEngine updates the extension
     *
     * @access public
     * @return void
     **/
    public function update_extension($current=false)
    {
        if($current == $this->version) return false;

        // Update the extension
        $this->EE->db
            ->where('class', __CLASS__)
            ->update('extensions', array('version' => $this->version));

    }

    // ********************************************************************************* //

} // END CLASS

/* End of file ext.updater.php */
/* Location: ./system/expressionengine/third_party/updater/ext.updater.php */
