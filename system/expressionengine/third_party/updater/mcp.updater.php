<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include_once dirname(dirname(__FILE__)).'/updater/config.php';

/**
 * Updater Module Control Panel Class
 *
 * @package         DevDemon_Updater
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2012 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com/updater/
 * @see             http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Updater_mcp
{

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Creat EE Instance
        $this->EE =& get_instance();
        $this->site_id = $this->EE->config->item('site_id');
        $this->EE->load->library('updater_helper');

        // Some Globals
        $this->initGlobals();

        //print_r($this->EE->config->config);

        $this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('updater'));
    }

    // ********************************************************************************* //

    public function index()
    {
        return $this->home();
    }

    // ********************************************************************************* //

    public function home()
    {
        // Set the page title
        if (function_exists('ee')) {
            ee()->view->cp_page_title = $this->EE->lang->line('u:dashboard');
        } else {
            $this->EE->cp->set_variable('cp_page_title', 'Updater');
        }

        $this->vdata['section'] = 'home';

        return $this->EE->load->view('home', $this->vdata, TRUE);
    }

    // ********************************************************************************* //

    public function home_new()
    {
        // Set the page title
        $data =& $this->vdata;

        if (function_exists('ee')) {
            ee()->view->cp_page_title = $this->EE->lang->line('u:home');
        } else {
            $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('u:home'));
        }

        $data['section'] = 'home';
        $this->EE->load->add_package_path(PATH_THIRD.'updater/');

        // Get native
        $native = $this->EE->config->item('updater_native_packages');

        // Get Installed
        $installed = array();
        $installed['module'] = $this->EE->addons->get_installed('modules');
        $installed['fieldtype'] = $this->EE->addons->get_installed('fieldtypes');
        $installed['extension'] = $this->EE->addons->get_installed('extensions');
        $installed['accessory'] = $this->EE->addons->get_installed('accessories');
        $installed['plugin'] = array();
        $installed['all'] = array();

        // IF RTE INSTALLED!
        if ($this->EE->db->table_exists('exp_rte_tools') !== false) {
            $installed['rte_tool'] = $this->EE->addons->get_installed('rte_tools');
        } else {
            $installed['rte_tool'] = array();
        }


        foreach ($installed as $section => $addonlist) {
            foreach ($addonlist as $add) {
                if (isset($add['package']) === false) continue;
                $installed['all'][] = $add['package'];
            }
        }

        $this->native = $native;
        $this->installed = $installed;

        $addons = $this->EE->updater->getAddonsList();
        $data['addons']['all'] = $this->format_addons($addons, 'all');
        $data['addons']['modules'] = $this->format_addons($addons, 'module');
        $data['addons']['fieldtypes'] = $this->format_addons($addons, 'fieldtype');
        $data['addons']['extensions'] = $this->format_addons($addons, 'extension');
        $data['addons']['accessories'] = $this->format_addons($addons, 'accessory');
        $data['addons']['plugins'] = $this->format_addons($addons, 'plugin');
        $data['addons']['rte_tools'] = $this->format_addons($addons, 'rte_tool');

        $this->EE->load->add_package_path(PATH_THIRD.'updater/');
        return $this->EE->load->view('home', $data, TRUE);
    }

    // ********************************************************************************* //

    private function format_addons($addons, $section)
    {
        $data = array();

        foreach ($addons as $package => $items) {
            $addon = array();

            // Version
            $addon['label'] = '';
            $addon['version'] = '';
            $addon['icon'] = $this->EE->updater_helper->getThemeUrl() . 'img/addon.png';

            if (isset($items['icons']) === true && $items['icons'][32] !== false) {
                $addon['icon'] = str_replace('/updater/', '/'.$package.'/', $this->EE->updater_helper->getThemeUrl()) . $items['icons']['32'];
            }

            // What Section?
            if ($section == 'all') {
                foreach ($items as $sub => $info) {
                    if ($sub == 'icons') continue;
                    if ($info['version'] > $addon['version']) {
                        $addon['version'] = $info['version'];
                        $addon['label'] = $info['label'];
                    }
                }
            } else {
                if (isset($items[$section]) === false) continue;

                $addon['label'] = $items[$section]['label'];
                $addon['version'] = $items[$section]['version'];
            }

            $addon['classes'] = array();
            $addon['classes'][] = 'filter-section_' . $section;

            // Is Native?
            if (in_array($package, $this->native) === true) {
                $addon['classes'][] = 'filter-native';
            } else {
                $addon['classes'][] = 'filter-thirdparty';
            }

            // Is Installed?
            if (isset($this->installed[$section][$package]) === true) {
                $addon['installed'] = true;
                $addon['classes'][] = 'filter-installed';
            } else {
                $addon['installed'] = false;
                $addon['classes'][] = 'filter-notinstalled';
            }

            // Plugins are always installed
            if ($section == 'plugin') {
                $addon['installed'] = true;
                $addon['classes'][] = 'filter-installed';
            }

            $data[] = $addon;
        }

        return $data;
    }

    // ********************************************************************************* //

    public function settings()
    {
        // Set the page title
        if (function_exists('ee')) {
            ee()->view->cp_page_title = $this->EE->lang->line('u:settings');
        } else {
            $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('u:settings'));
        }

        $this->vdata['section'] = 'settings';
        $this->vdata['settings'] = $this->EE->updater->settings;
        $this->vdata['override_settings'] = $this->EE->config->item('updater');

        if (isset($this->vdata['settings']['action_url']['actionGeneralRouter']) === true && $this->vdata['settings']['action_url']['actionGeneralRouter'] == false) {
            $this->vdata['settings']['action_url']['actionGeneralRouter'] = $this->vdata['act_url'];
        }


        //$this->EE->cp->add_to_head('<script src="' . UPGRADER_THEME_URL . 'mcp_settings.js?v='.UPDATER_VERSION.'" type="text/javascript"></script>');

        return $this->EE->load->view('settings', $this->vdata, TRUE);
    }

    // ********************************************************************************* //

    public function update_settings()
    {
        $settings = $this->EE->input->post('settings');

        if (isset($settings['action_url']['actionGeneralRouter']) === true) {

            // Trim it just in case
            $settings['action_url']['actionGeneralRouter'] = trim($settings['action_url']['actionGeneralRouter']);

            if ($this->vdata['act_url'] == $settings['action_url']['actionGeneralRouter']) {
                $settings['action_url']['actionGeneralRouter'] = '';
            }
        }

        // Put it Back
        $this->EE->db->set('settings', serialize($settings));
        $this->EE->db->where('module_name', 'Updater');
        $this->EE->db->update('exp_modules');

        $this->EE->functions->redirect($this->base . '&method=index');
    }

    // ********************************************************************************* //

    public function ajaxRouter()
    {
        include PATH_THIRD . 'updater/mod.updater.php';
        $MOD = new Updater();
        $MOD->actionGeneralRouter($this->EE->input->get('task'));
    }

    // ********************************************************************************* //

    private function initGlobals()
    {
        // Some Globals
        $this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=updater';
        $this->base_short = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=updater';
        $this->site_id = $this->EE->config->item('site_id');

        // Page Title & BreadCumbs
        $this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('updater'));

        if (function_exists('ee')) {
            ee()->view->cp_page_title = $this->EE->lang->line('updater');
        } else {
            $this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('updater'));
        }

        // -------------------------------------------------------
        //  Global Views Data
        // -------------------------------------------------------
        $this->vdata['base_url'] = $this->base;
        $this->vdata['base_url_short'] = $this->base_short;
        $this->vdata['act_url'] = $this->EE->updater_helper->getRouterUrl('url');
        $this->vdata['method'] = $this->EE->input->get('method');
        $this->vdata['post_max_size'] = ini_get('post_max_size');
        $this->vdata['upload_max_filesize'] = ini_get('upload_max_filesize');
        $this->vdata['settings_done'] = $this->EE->updater->areSettingsSaved();
        $this->vdata['post_5mb'] = ($this->return_bytes($this->vdata['post_max_size']) > 5242880) ? TRUE : FALSE;
        $this->vdata['upload_5mb'] = ($this->return_bytes($this->vdata['upload_max_filesize']) > 5242880) ? TRUE : FALSE;

        $this->vdata['disable_btn'] = FALSE;
        if (!$this->vdata['post_5mb'] || !$this->vdata['upload_5mb'] || !$this->vdata['settings_done']) $this->vdata['disable_btn'] = TRUE;

        // -------------------------------------------------------
        //  CSS/JS
        // -------------------------------------------------------
        $this->EE->updater_helper->addMcpAssets('gjs');
        $this->EE->updater_helper->addMcpAssets('css', 'css/mcp.css?v='.UPDATER_VERSION, 'updater', 'mcp');
        //$this->EE->updater_helper->addMcpAssets('js', 'js/jquery.isotope.min.js', 'jquery', 'isotope');
        $this->EE->updater_helper->addMcpAssets('js', 'js/bootstrap.modal.js', 'jquery', 'bootstrap.modal');
        $this->EE->updater_helper->addMcpAssets('js', 'js/handlebars-runtime.1.0.0-rc.3.js', 'handlebars', 'runtime');
        $this->EE->updater_helper->addMcpAssets('js', 'js/hbs-templates.js', 'updater', 'hbs-templates');

        if ($this->EE->debug_updater === true) {

            $this->EE->updater_helper->addMcpAssets('custom', "
                <script type='text/javascript' src='{theme_url}js/base64.js?v=".UPDATER_VERSION."'></script>
                <script type='text/javascript' src='{theme_url}js/swfupload.min.js?v=".UPDATER_VERSION."'></script>
                <script type='text/javascript' src='{theme_url}js/swfupload.queue.min.js?v=".UPDATER_VERSION."'></script>
                <script type='text/javascript' src='{theme_url}js/swfupload.speed.min.js?v=".UPDATER_VERSION."'></script>
            ");

             $this->EE->updater_helper->addMcpAssets('js', 'js/mcp.js?v='.UPDATER_VERSION, 'updater', 'mcp');

        } else {

            $this->EE->updater_helper->addMcpAssets('custom', "
                <!--[if IE]>
                <script type='text/javascript' src='{theme_url}js/base64.js?v=".UPDATER_VERSION."'></script>
                <script type='text/javascript' src='{theme_url}js/json3.min.js?v=".UPDATER_VERSION."'></script>
                <script type='text/javascript' src='{theme_url}js/swfupload.min.js?v=".UPDATER_VERSION."'></script>
                <script type='text/javascript' src='{theme_url}js/swfupload.queue.min.js?v=".UPDATER_VERSION."'></script>
                <script type='text/javascript' src='{theme_url}js/swfupload.speed.min.js?v=".UPDATER_VERSION."'></script>
                <![endif]-->
            ");

             $this->EE->updater_helper->addMcpAssets('js', 'js/mcp.min.js?v='.UPDATER_VERSION, 'updater', 'mcp');
        }

        $this->EE->cp->add_js_script(array(
                'ui'        => array('sortable')
            )
        );

    }

     // ********************************************************************************* //

    /**
     * Return Bytes
     * @param  string $val
     * @return int - bytes
     */
    private function return_bytes($val) {
        $val = trim($val);

        $last = strtolower($val[strlen($val)-1]);

        switch($last)
        {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    // ********************************************************************************* //


} // END CLASS

/* End of file mcp.updater.php */
/* Location: ./system/expressionengine/third_party/updater/mcp.updater.php */
