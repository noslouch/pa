<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Updater Module
 *
 * @package         DevDemon_Updater
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2012 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com
 * @see             http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Updater
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
        $this->EE->load->library('updater_helper');
    }

    // ********************************************************************************* //

    // LEGACY
    public function ACT_general_router()
    {
        $this->actionGeneralRouter();
    }

    // ********************************************************************************* //

    public function actionGeneralRouter($mcp_task=false)
    {
        @header('Access-Control-Allow-Origin: *');
        @header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Max-Age: 86400');
        @header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        @header('Access-Control-Allow-Headers: Keep-Alive, Content-Type, User-Agent, Cache-Control, X-Requested-With, X-File-Name, X-File-Size');

        // -----------------------------------------
        // Increase all types of limits!
        // -----------------------------------------
        set_time_limit(0);

        if ($this->EE->updater->settings['infinite_memory'] == 'yes') {
            ini_set('memory_limit', -1);
        }

        @error_reporting(E_ALL);
        @ini_set('display_errors', 1);

        // Task
        $task = $this->EE->input->get('task');

        // Only Super Admins can do this.
        if ($this->EE->session->userdata['group_id'] != 1 && $task != 'upload_file') {
            header("HTTP/1.0 403 Forbidden");
            exit();
        }

        // Logged in user? But no AJAX call?
        if (!AJAX_REQUEST && $task != 'upload_file') {
            show_error('This is the Updater action URL<br>
                This message is only displayed because you are logged in as a Super Admin.<br>
                Note: All further code execution has halted.
            ');
            exit();
        }

        if ($mcp_task !== false) {
            $task = $mcp_task;
        }

        if (method_exists('Updater', $task) == false) {
            header("HTTP/1.0 403 Forbidden");
            exit();
        }

        $this->path_root = FCPATH;
        $this->path_system = str_replace('expressionengine/', '', APPPATH);
        $this->path_themes = $this->EE->config->item('theme_folder_path');

        $this->{$task}();

        exit();
    }

    // ********************************************************************************* //

    private function test_ajax_call()
    {
         exit($this->EE->updater_helper->generateJson(array('success' => 'yes')));
    }

    // ********************************************************************************* //

    private function get_server_info()
    {
        $out = array('server'=>array());
        $out['server'] = $this->EE->updater->server_paths;
        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function test_login()
    {
        $out = array('success'=>'no');

        // Did the AJAX request come with settings?
        $settings = false;
        if (isset($_POST['settings']) === true) {
            $settings = $this->EE->input->post('settings');
        }

        // Check it!
        $ret = $this->EE->updater->settingsLoginCheck($settings);

        if ($ret) $out['success'] = 'yes';

        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function browse_server()
    {
        // Did the AJAX request come with settings?
        $settings = false;
        if (isset($_POST['settings']) === true) {
            $settings = $this->EE->input->post('settings');
        }

        if ($settings != false) {
            $this->EE->updater->settings = Updater_helper::arrayExtend($this->EE->updater->settings, $settings);
        }

        try {
            $this->EE->updater_transfer->init();
        } catch (Exception $e) {
            exit();
        }

        $action = $this->EE->input->post('action');
        $path = $this->EE->input->post('path');

        if ($action == 'chdir') {
            $list = $this->EE->updater_transfer->dir_list($path);

        } else {
            $path = dirname($path);
            try {
                $list = $this->EE->updater_transfer->chdir($path, true);
            } catch (Exception $e) {
                $out = array();
                $out['success'] = 'no';
                $out['error'] = $e->getMessage();
                exit($this->EE->updater_helper->generateJson($out));
            }

        }

        $path = $this->EE->updater_transfer->current_path;

        $out = array();
        $out['success'] = 'yes';
        $out['items'] = $list;
        $out['path'] = $path;

        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function test_transfer_method()
    {
        $this->EE->load->library('updater_tests');
        $settings = $this->EE->updater->settings;

        $post_settings = $this->EE->input->post('settings');
        if (is_array($post_settings) == false) $post_settings = array();
        $settings = $this->EE->updater_helper->arrayExtend($settings, $post_settings);

        if (isset($settings['file_transfer_method']) == false) $settings['file_transfer_method'] = 'local';

        $this->EE->updater_tests->test_transfer_method($settings);

        exit();
    }

    // ********************************************************************************* //

    private function upload_file()
    {
        $key = time().'_'.mt_rand(5, 598652);
        $temp_dir = $this->EE->updater->cache_path.'updater/'.$key.DIRECTORY_SEPARATOR;

        $this->EE->updater->temp_key = $key;
        $this->EE->updater->temp_dir = $temp_dir;

        // -----------------------------------------
        // Temp Dir
        // -----------------------------------------
        if (@is_dir($temp_dir) === false) {
            @mkdir($temp_dir, 0777, true);
            @chmod($temp_dir, 0777);
        }

        // Last check, does the target dir exist, and is writable
        if (is_really_writable($temp_dir) !== true) {
            $out['error_msg'] = $this->EE->lang->line('error:temp_dir_write');
            exit($this->EE->updater_helper->generateJson($out));
        }

        // -----------------------------------------
        // Uploaded Files?
        // -----------------------------------------
        if (isset($_FILES['updater_file']) === true) {

            $filename = $_FILES['updater_file']['name'];
            $zip_path = "{$temp_dir}{$key}.zip";

             // Any Errors?
            if ($_FILES['updater_file']['error'] > 0) {
                $out['error_msg'] = $this->EE->lang->line('error:upload_err') . ' CODE: ' . $_FILES['updater_file']['error'];
                exit($this->EE->updater_helper->generateJson($out));
            }

            // Move it!
            if (@move_uploaded_file($_FILES['updater_file']['tmp_name'], $zip_path) === false) {
                // No file was uploaded..
                $out['error_msg'] = $this->EE->lang->line('error:move_upload');
                exit($this->EE->updater_helper->generateJson($out));
            }

        } else {

            // No file was uploaded..
            $out['error_msg'] = $this->EE->lang->line('error:no_files_up');
            exit($this->EE->updater_helper->generateJson($out));
        }

        // Safe is for later?
        $this->EE->updater->temp_zip_filename = $filename;

        // -----------------------------------------
        // Process the file
        // -----------------------------------------
        $out = $this->EE->updater->processZipFile($zip_path, $temp_dir);

        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function addon_move_files()
    {
        $this->EE->load->library('updater_misc');
        $this->EE->load->library('updater_addons');
        $this->EE->updater_transfer->init();

        $out = array('success' => 'no', 'body' => '');

        // Be sure our key is there
        if (isset($_POST['addon']) === false) {
            $out['body'] = 'Missing addon POST key';
            exit($this->EE->updater_helper->generateJson($out));
        }

        // Decode the json
        $addon = $this->EE->updater_helper->decodeJson($_POST['addon']);

        // Is it correct?
        if (isset($addon->type) === false) {
            $out['body'] = 'Failed to decode Addon JSON';
            exit($this->EE->updater_helper->generateJson($out));
        }

        try {
            switch ($addon->type) {
                case 'addon':
                    $this->EE->updater_addons->moveFiles($addon);
                    break;
                case 'ee_msm':
                    $this->EE->updater_misc->moveMsmFiles($addon);
                    break;
                case 'ee_forum':
                    $this->EE->updater_misc->moveForumFiles($addon);
                    break;
                case 'cp_theme':
                    $this->EE->updater_misc->moveCpThemeFiles($addon);
                    break;
                case 'forum_theme':
                    $this->EE->updater_misc->moveForumThemeFiles($addon);
                    break;
            }
        } catch (Exception $e) {
            $out['body'] = $e->getMessage();
            exit($this->EE->updater_helper->generateJson($out));
        }


        $out['success'] = 'yes';
        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function addon_install()
    {
        $this->EE->load->library('updater_misc');
        $this->EE->load->library('updater_addons');

        $out = array('success' => 'no', 'body' => '');

        // Be sure our key is there
        if (isset($_POST['addon']) === false) {
            $out['body'] = 'Missing addon POST key';
            exit($this->EE->updater_helper->generateJson($out));
        }

        // Decode tje json
        $addon = $this->EE->updater_helper->decodeJson($_POST['addon']);

        // Is it correct?
        if (isset($addon->type) === false) {
            $out['body'] = 'Failed to decode Addon JSON';
            exit($this->EE->updater_helper->generateJson($out));
        }

        // For those addons that need smartforge!
        if (file_exists(APPPATH.'libraries/Smartforge.php') === true) {
            $this->EE->load->library('smartforge');
        }

        try {
            switch ($addon->type) {
                case 'addon':
                    $this->EE->updater_addons->installUpdateAddon($addon);
                    break;
                case 'ee_forum':
                    $this->EE->updater_misc->installUpdateForum($addon);
                    break;
            }
        } catch (Exception $e) {
            $out['body'] = $e->getMessage();
            exit($this->EE->updater_helper->generateJson($out));
        }

        $out['queries'] = $this->EE->updater->queries_executed;
        $out['success'] = 'yes';

        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function clean_temp_dirs()
    {
        $this->EE->load->helper('file');
        $out = array('success' => 'no', 'body' => '');

        //delete_files($this->EE->updater->cache_path.'updater/');
        $this->EE->updater_helper->deleteFiles($this->EE->updater->cache_path.'updater', true);

        $out['success'] = 'yes';
        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function backup_database_prepare()
    {
        $out = array('success' => 'no', 'body' => '');

        $out['success'] = 'yes';
        $out['tables'] = $this->EE->db->list_tables();
        $out['tables'][] = $this->EE->lang->line('upload_final_dest');

        //$out['tables'] = array($this->EE->lang->line('upload_final_dest'));
        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function backup_database()
    {
        $out = array('success' => 'no', 'body' => '');

        $this->EE->load->dbutil();
        $time = $this->EE->input->post('time');
        $table = $this->EE->input->post('table');
        $date_time_dir = date('Y_m_d-Hi',$time);

        // Does the temp directory exist?
        $path = $this->EE->updater->cache_path.'updater/';

        if (@is_dir($path) === false) {
            @mkdir($path, 0777, true);
            @chmod($path, 0777);
        }

        // -----------------------------------------
        // Do we need to move the backup file
        // to it's final destination?
        // -----------------------------------------
        if ($table == $this->EE->lang->line('upload_final_dest')) {
            try {
                $this->EE->updater_transfer->init();
                $this->EE->updater_transfer->mkdir('backup', $date_time_dir.'/mysql');
                $this->EE->updater_transfer->upload('backup', PATH_THIRD.'updater/libraries/htaccess', $date_time_dir.'/.htaccess', 'file');
                $this->EE->updater_transfer->upload('backup', $path.'backup.sql', $date_time_dir.'/mysql/backup.sql', 'file');
            } catch (Exception $e) {
                $out['body'] = $e->getMessage();
                exit($this->EE->updater_helper->generateJson($out));
            }

            $out['success'] = 'yes';
            exit($this->EE->updater_helper->generateJson($out));
        }

        // -----------------------------------------
        // Write the table to the temp backup file
        // -----------------------------------------
        if ($this->EE->db->table_exists($table) == true) {
            //$out['success'] = 'yes';
            //exit($this->EE->updater_helper->generateJson($out));

            $options = array();
            $options['tables'][] = $table;
            $options['format'] = 'txt';
            $backup =& $this->EE->dbutil->backup($options);
            write_file($path.'backup.sql', $backup, FOPEN_READ_WRITE_CREATE);
        }

        $out['success'] = 'yes';
        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function backup_files_prepare()
    {
        $out = array('success' => 'no', 'body' => '');

        $this->EE->load->helper('directory');
        $server_dirs = $this->EE->updater->server_paths;

        $codeigniter_map = directory_map(BASEPATH, 2);
        $ee_map = directory_map(APPPATH, 2);
        $third_party_map = directory_map($server_dirs['system_third_party'], 2);
        $themes_map = directory_map($server_dirs['themes'], 2);

        // Basedir
        $time = $this->EE->input->post('time');
        $date_time_dir = date('Y_m_d-Hi', $time);

        // Loop over all directories in the Codeigniter DIR
        foreach ($codeigniter_map as $dir => $arr) {
            if (is_array($arr) === false) continue;
            $out['dirs'][] = 'system/codeigniter/'.$dir;
        }

        // Loop over all directories in the EE system dir
        foreach ($ee_map as $dir => $arr) {
            if (is_array($arr) === false) continue;
            if ($dir == 'cache' || $dir == 'third_party') continue;
            $out['dirs'][] = 'system/expressionengine/'.$dir;
        }

        foreach ($third_party_map as $dir => $arr) {
            if (is_array($arr) === false) continue;
            $out['dirs'][] = 'system/expressionengine/third_party/'.$dir;
        }

        // Loop over all directories in the Themes dir
        foreach ($themes_map as $dir => $arr) {
            if (is_array($arr) === false) continue;
            $out['dirs'][] = 'themes/'.$dir;
        }

        try {
            $this->EE->updater_transfer->init();
            $this->EE->updater_transfer->mkdir('backup', $date_time_dir.'/files');
            $this->EE->updater_transfer->upload('backup', PATH_THIRD.'updater/libraries/htaccess', $date_time_dir.'/.htaccess', 'file');
        } catch (Exception $e) {
            $out['body'] = $e->getMessage();
            exit($this->EE->updater_helper->generateJson($out));
        }

        $out['success'] = 'yes';

        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function backup_files()
    {
        $out = array('success' => 'no', 'body' => '');
        $server_dirs = $this->EE->updater->server_paths;
        $dir = $this->EE->input->post('dir');
        $time = $this->EE->input->post('time');

        // -----------------------------------------
        // Parse the Source Dir
        // -----------------------------------------
        $source_dir = '';

        if (strpos($dir, 'system/codeigniter') === 0) {
            $source_dir = BASEPATH . str_replace('system/codeigniter/', '', $dir) . '/';
        } elseif (strpos($dir, 'system/expressionengine/third_party') === 0) {
            $source_dir = PATH_THIRD . str_replace('system/expressionengine/third_party', '', $dir) . '/';
        } elseif (strpos($dir, 'system/expressionengine') === 0) {
            $source_dir = APPPATH . str_replace('system/expressionengine/', '', $dir) . '/';
        } elseif (strpos($dir, 'themes/') === 0) {
            $source_dir = $server_dirs['themes'] . str_replace('themes/', '', $dir) . '/';
        } else {
            $out['body'] = 'Path not recognized!';
            exit($this->EE->updater_helper->generateJson($out));
        }

        // Basedir
        $basedir = date('Y_m_d-Hi',$time).'/files/'.$dir;

        // -----------------------------------------
        // Copy Files!
        // -----------------------------------------
        try {
            //$out['success'] = 'yes';
            //exit($this->EE->updater_helper->generateJson($out));

            $dirname = basename($source_dir);
            $this->EE->updater_transfer->init();
            $this->EE->updater_transfer->mkdir('backup', $basedir);
            $this->EE->updater_transfer->upload('backup', $source_dir, $basedir, 'dir', true);
        } catch (Exception $e) {
            $out['body'] = $e->getMessage();
            exit($this->EE->updater_helper->generateJson($out));
        }

        $out['success'] = 'yes';
        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //

    private function ee_update_init()
    {
        $out = array('success' => 'no', 'body' => '');

        $action = $this->EE->input->get_post('action');
        $this->EE->load->library('updater_ee');

        // Decode the json
        $action_obj = $this->EE->updater_helper->decodeJson($_POST['action_obj']);

        // -----------------------------------------
        // Put site Offline
        // -----------------------------------------
        if ($action == 'site_offline') {
            $this->EE->config->_update_config(array('is_system_on' => 'n') );
            $out['success'] = 'yes';
            exit($this->EE->updater_helper->generateJson($out));
        }

        // -----------------------------------------
        // Copy installer
        // -----------------------------------------
        if ($action == 'copy_installer') {
            $this->EE->updater_ee->server = $this->EE->updater->server_paths;
            $this->EE->updater_ee->copyInstaller($action_obj);
        }

        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //


} // END CLASS

/* End of file mod.updater.php */
/* Location: ./system/expressionengine/third_party/updater/mod.updater.php */
