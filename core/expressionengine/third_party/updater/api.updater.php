<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once dirname(dirname(__FILE__)).'/updater/config.php';

/**
 * Updater API File
 *
 * @package         DevDemon_Updater
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com/updater/
 */
class Updater_API
{
    public $settings = array(); // Updater settings
    public $config = array(); // Updater config overrides
    public $server_paths = false;

    // Processing Files
    public $temp_dir;
    public $temp_key;
    public $temp_zip_filename;

    // Misc
    public $queries_executed = array();

    // ********************************************************************************* //

    public function __construct()
    {
        $this->EE =& get_instance();

        if (defined('EE_APPPATH') === true) {
            $this->EE->lang->load('updater');
        } else {
            $this->EE->lang->loadfile('updater');
        }

        $this->EE->load->config('updater_config');
        $this->EE->load->library('firephp');
        $this->EE->load->library('updater_transfer');

        $this->stats = new stdClass();
        $this->stats->event = '';
        $this->stats->data = array();

        // Grab our settings!
        $this->getSettings();

        // Server paths
        $this->getServerInfo();

        // Set the EE Cache Path? (hell you can override that)
        $this->cache_path = $this->EE->config->item('cache_path') ? $this->EE->config->item('cache_path') : APPPATH.'cache/';

        // Are we in debug mode?
        $this->EE->debug_updater = ($this->settings['debug'] == 'yes') ? true : false;
    }

    // ********************************************************************************* //

    public function areSettingsSaved()
    {
        $done = false;

        if (isset($this->settings['path_map']['system']) === true && $this->settings['path_map']['system'] != false) {
            $done = true;
        }

        return $done;
    }

    // ********************************************************************************* //

    public function getSettings()
    {
        $settings = array();

        if (isset($this->EE->session->cache['updater']['module_settings']) == true) {
            $settings = $this->EE->session->cache['updater']['module_settings'];
        } else {

            // Get the module settings from the DB
            $this->EE->db->select('settings');
            $this->EE->db->where('module_name', 'Updater');
            $query = $this->EE->db->get('exp_modules');

            if ($query->num_rows() > 0) {
                $settings = @unserialize($query->row('settings'));
            }

            // Still falsy?
            if ($settings == false) {
                $settings = array();
            }
        }

        // Module Defaults & Config Overrides
        $default = $this->EE->config->item('updater_module_defaults');
        $this->config = $this->EE->config->item('updater');

        // Just to be sure
        if (is_array($this->config) == false) {
            $this->config = array();
        }

        // Merge them
        $settings = Updater_helper::arrayExtend($default, $settings);

        // And the config overrides!
        if (!empty($this->config)) {
            $settings = Updater_helper::arrayExtend($settings, $this->config);
        }

        // Save so we don't have to do this again.
        $this->EE->session->cache['updater']['module_settings'] = $settings;
        $this->settings = $this->EE->session->cache['updater']['module_settings'];

        return $settings;
    }

    // ********************************************************************************* //

    private function getServerInfo()
    {
        if ($this->server_paths === false) {
            $this->server_paths = array();
            $this->server_paths['root'] = FCPATH;
            $this->server_paths['backup'] = FCPATH . 'site_backup/';
            $this->server_paths['system'] = str_replace('expressionengine/', '', APPPATH);
            $this->server_paths['system_third_party'] = Updater_helper::getThirdPartyPath();
            $this->server_paths['themes'] = Updater_helper::getThemePath();
            $this->server_paths['themes_third_party'] = Updater_helper::getThirdPartyThemePath();
        }
    }

    // ********************************************************************************* //

    public function settingsLoginCheck($settings=false)
    {
        if ($settings != false) {
            $this->settings = Updater_helper::arrayExtend($this->settings, $settings);
        }

        try {
            $this->EE->updater_transfer->init();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    // ********************************************************************************* //

    public function processZipFile($zip_path=false, $temp_dir=false)
    {
        $ee_zip = false;
        $this->EE->load->library('updater_ee');
        $this->EE->load->library('updater_misc');
        $this->EE->load->library('updater_addons');

        $out = array();
        $out['success'] = 'no';
        $out['temp_dir'] = $this->temp_dir;
        $out['temp_key'] = $this->temp_key;
        $out['found'] = array();

        if ($temp_dir == false) {
            $temp_dir = $this->temp_dir;
        }

        // -----------------------------------------
        // Extract ZIP
        // -----------------------------------------
        $res = $this->EE->updater_helper->extractZip($zip_path, $temp_dir);

        if ($res == false) {
            $out['error_msg'] = $this->EE->lang->line('error:zip_extract_fail');
            return $out;
        }

        // Get the directory contents
        $this->temp_dir_contents = $this->EE->updater_helper->getDirContents($temp_dir);

        // -----------------------------------------
        // ExpressionEgine Install?
        // -----------------------------------------
        $res = $this->EE->updater_ee->detectFromPath($temp_dir);
        if ($res != false) {
            $ee_zip = true;
            $out['found'][] = array(
                'type' => 'ee',
                'info' => $res,
            );

            // Old EE? Quit!
            if ($res['build'] < APP_BUILD) {
                $out['error_msg'] = $this->EE->lang->line('error:old_ee');
                return $out;
            }
        }

        // -----------------------------------------
        // EE Forum?
        // -----------------------------------------
        $res = $this->EE->updater_misc->detectForumFromPath($temp_dir);
        if ($res != false) {
            $out['found'][] = array(
                'type' => 'ee_forum',
                'info' => $res,
            );
        }

        // -----------------------------------------
        // EE MSM?
        // -----------------------------------------
        $res = $this->EE->updater_misc->detectMsmFromPath($temp_dir);
        if ($res != false) {
            $out['found'][] = array(
                'type' => 'ee_msm',
                'info' => $res,
            );
        }

        // -----------------------------------------
        // Forum Theme
        // -----------------------------------------
        if ($ee_zip === false){
            $res = $this->EE->updater_misc->detectForumThemeFromPath($temp_dir);
            if ($res != false) {
                $out['found'][] = array(
                    'type' => 'forum_theme',
                    'info' => $res,
                );
            }
        }

        // -----------------------------------------
        // CP Theme
        // -----------------------------------------
        if ($ee_zip === false){
            $res = $this->EE->updater_misc->detectCpThemeFromPath($temp_dir);
            if ($res != false) {
                $out['found'][] = array(
                    'type' => 'cp_theme',
                    'info' => $res,
                );
            }
        }

        // -----------------------------------------
        // Addon?
        // -----------------------------------------
        if ($ee_zip === false){
            $res = $this->EE->updater_addons->detectFromPath($temp_dir);
            if ($res != false) {

                $out['found'][] = array(
                    'type' => 'addon',
                    'info' => $res,
                );
            }
        }

        if (empty($out['found']) === true) {
            $out['error_msg'] = $this->EE->lang->line('error:detect_nothing');

            // -----------------------------------------
            // Send Stats
            // -----------------------------------------
            $this->EE->updater->stats->event = 'zip-process_error';
            $this->EE->updater->stats->data['filename'] = $this->EE->updater->temp_zip_filename;
            $this->EE->updater->sendStats();
        } else {
            $out['success'] = 'yes';
        }

        return $out;
    }

    // ********************************************************************************* //

    public function getAddonsList()
    {
        // Init arrays
        $this->_addons = array();
        $this->EE->load->helper('directory');

        $dirs = array();
        $dirs[] = PATH_THIRD;
        $dirs[] = APPPATH . 'modules/';
        $dirs[] = APPPATH . 'fieldtypes/';
        $dirs[] = APPPATH . 'rte_tools/';

        $this->themeUrl = $this->EE->updater_helper->getThemeUrl(true);
        $this->themePath = $this->EE->updater_helper->getThirdPartyThemePath();

        foreach ($dirs as $dir) {
            $files = directory_map($dir, 2);

            // Loop over all pakages and store them
            foreach ($files as $package => $dirfiles) {

                // Ignore safecracker_file
                if ($package == 'safecracker_file') continue;

                // We don't want index.html etc
                if (is_array($dirfiles) === false) continue;
                $this->getAddonsListFromPackage($package, $dir.$package.'/');
            }
        }

        // -----------------------------------------
        // Native Plugins
        // -----------------------------------------
        $files = directory_map(APPPATH.'plugins/');

        foreach ($files as $file) {
            if (strpos($file, 'pi.') !== 0)  continue;
            require_once APPPATH.'plugins/'.$file;

            $addon = str_replace(array('pi.', '.php'), '', $file);
            $this->_addons[$addon]['plugin']['label'] = $plugin_info['pi_name'];
            $this->_addons[$addon]['plugin']['version'] = $plugin_info['pi_version'];
        }

        // -----------------------------------------
        // Native Accessoiris
        // -----------------------------------------
        $files = directory_map(APPPATH.'accessories/');

        foreach ($files as $file) {
            if (strpos($file, 'acc.') !== 0)  continue;
            require_once APPPATH.'accessories/'.$file;

            $addon = str_replace(array('acc.', '.php'), '', $file);
            $class = $addon.'_acc';
            $class = new $class();

            $this->_addons[$addon]['accessory']['label'] = $class->name;
            $this->_addons[$addon]['accessory']['version'] = $class->version;
        }


        return $this->_addons;
    }

    // ********************************************************************************* //

    public function getAddonsByType()
    {
        if ($this->addonsByType !== false) {
            return $this->addonsByType;
        }

        // Get all addons
        $addons = $this->getAddons();

    }

    // ********************************************************************************* //

    private function getAddonsListFromPackage($addon, $dir)
    {
        /*
        if (file_exists($dir.'addon.json') === true) {
            $info = $this->parseAddonJson($dir.'addon.json');

            if (is_object($info) === true) return $info;
        }*/

        $this->EE->load->add_package_path($dir);

        $mod_file = "{$dir}/upd.{$addon}.php";
        $ft_file = "{$dir}/ft.{$addon}.php";
        $ext_file = "{$dir}/ext.{$addon}.php";
        $acc_file = "{$dir}/acc.{$addon}.php";
        $pi_file = "{$dir}/pi.{$addon}.php";
        $rte_file = "{$dir}/rte.{$addon}.php";

        // -----------------------------------------
        // Icon!
        // -----------------------------------------
        if (file_exists($this->themePath.$addon.'/'.'icon32.png') === true) {
            $this->_addons[$addon]['icons'][32] = 'icon32.png';
        } else {
            $this->_addons[$addon]['icons'][32] = false;
        }

        // -----------------------------------------
        // Module?
        // -----------------------------------------
        if (file_exists($mod_file) === true) {
            require_once $mod_file;
            $class = $addon.'_upd';
            $class = new $class();
            $this->EE->lang->load($addon, $this->EE->lang->user_lang, false, true, $dir);

            $this->_addons[$addon]['module']['label'] = $this->EE->lang->line($addon.'_module_name');
            $this->_addons[$addon]['module']['version'] = $class->version;
        }

        // -----------------------------------------
        // Fieldtype?
        // -----------------------------------------
        if (file_exists($ft_file) === true) {
            require_once APPPATH . 'fieldtypes/EE_Fieldtype.php';
            require_once $ft_file;
            $class = $addon.'_ft';
            $class = new $class();

            $this->_addons[$addon]['fieldtype']['label'] = $class->info['name'];
            $this->_addons[$addon]['fieldtype']['version'] = $class->info['version'];
        }

        // -----------------------------------------
        // Extension
        // -----------------------------------------
        if (file_exists($ext_file) === true) {
            require_once $ext_file;
            $class = $addon.'_ext';
            $class = new $class();

            $this->_addons[$addon]['extension']['label'] = $class->name;
            $this->_addons[$addon]['extension']['version'] = $class->version;
        }

        // -----------------------------------------
        // Accesories
        // -----------------------------------------
        if (file_exists($acc_file) === true) {
            require_once $acc_file;
            $class = $addon.'_acc';
            $class = new $class();

            $this->_addons[$addon]['accessory']['label'] = $class->name;
            $this->_addons[$addon]['accessory']['version'] = $class->version;
        }

        // -----------------------------------------
        // RTE
        // -----------------------------------------
        if (file_exists($rte_file) === true) {
            require_once $rte_file;
            $class = $addon.'_rte';
            $class = new $class();

            $this->_addons[$addon]['rte_tool']['label'] = $class->info['name'];
            $this->_addons[$addon]['rte_tool']['version'] = $class->info['version'];
        }

        // -----------------------------------------
        // Plugin
        // -----------------------------------------
        if (file_exists($pi_file) === true) {
            require_once $pi_file;
            $this->_addons[$addon]['plugin']['label'] = $plugin_info['pi_name'];
            $this->_addons[$addon]['plugin']['version'] = $plugin_info['pi_version'];
        }




        $this->EE->load->remove_package_path($dir);
    }

    // ********************************************************************************* //

    public function getQueries($action)
    {
        $this->EE->db->save_queries = true;

        if ($action == 'start')
        {
            $this->EE->db->queries = array();
        }
        else
        {
            foreach ($this->EE->db->queries as $sql)
            {
                $this->queries_executed[] = $sql;
            }
        }
    }

    // ********************************************************************************* //

    public function sendStats()
    {
        if (!function_exists('curl_init')) return;

        // Are we allowed to track stats?
        if (isset($this->settings['track_stats']) === true && $this->settings['track_stats'] == 'no') {
            return;
        }

        try {
            $host = 'http://api.mixpanel.com/track/?data=';
            $token = '8258f45c0b24fafe438deaec812be1a0';

            $params = array();
            $params['event'] = $this->stats->event;
            $params['properties'] = $this->stats->data;

            if (!isset($params['properties']['token'])){
                $params['properties']['token'] = $token;
            }

            // During EE Update, this is not available
            if (defined('APP_BUILD'))
            {
                $params['properties']['app_build'] = APP_BUILD;
                $params['properties']['app_version'] = APP_VER;
            }

            $params['properties']['server_os'] = (DIRECTORY_SEPARATOR == '/') ? 'unix' : 'windows';
            $params['properties']['updater_version'] = UPDATER_VERSION;
            $params['properties']['transfer_method'] = $this->settings['file_transfer_method'];

            $php_version = explode('.', PHP_VERSION);
            $params['properties']['php_version'] = @$php_version[0].'.'.@$php_version[1].'.'.@$php_version[2];

            if (isset($this->settings['mixpanel_token']) === true)
            {
                $params['properties']['token'] = $this->settings['mixpanel_token'];
            }

            if (isset($this->settings['mixpanel_firephp']) === true && $this->settings['mixpanel_firephp'] == 'yes') {
                $this->EE->firephp->log($params);
            }

            $data = base64_encode($this->EE->updater_helper->generateJson($params));

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $host.$data);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $resp = curl_exec($ch);
            curl_close($ch);

            $this->stats->event = '';
            $this->stats->data = array();

        } catch (Exception $e) {

        }
    }

    // ********************************************************************************* //



} // END CLASS

/* End of file api.updater.php  */
/* Location: ./system/expressionengine/third_party/updater/api.updater.php */
