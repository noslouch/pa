<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH.'helpers/ee_compat.php');

class Dupdater extends CI_Controller {

    private $native_modules = array('blacklist', 'channel', 'comment', 'commerce',
        'email', 'emoticon', 'file', 'forum', 'gallery', 'ip_to_nation',
        'jquery', 'mailinglist', 'member', 'metaweblog_api', 'moblog', 'pages',
        'query', 'referrer', 'rss', 'rte', 'safecracker', 'search',
        'simple_commerce', 'stats', 'updated_sites', 'wiki');

    private $native_theme_dirs = array('wiki_themes', 'site_themes', 'profile_themes');

    /**
     * Constructor
     *
     * Sets some base values
     *
     * @access  public
     * @return  void
     */
    public function __construct()
    {
        parent::__construct();

        if (defined('IS_CORE') === false) define('IS_CORE', false);

        // -----------------------------------------
        // Increase all types of limits!
        // -----------------------------------------
        @set_time_limit(0);
        @error_reporting(E_ALL);
        @ini_set('display_errors', 1);

        @header('Access-Control-Allow-Origin: *');
        @header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Max-Age: 86400');
        @header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        @header('Access-Control-Allow-Headers: Keep-Alive, Content-Type, User-Agent, Cache-Control, X-Requested-With, X-File-Name, X-File-Size');

        try {
            // Load Config
            @include (EE_APPPATH.'config/config.php');
            @include (EE_APPPATH.'config/database.php');

            $this->config->config = array_merge($this->config->config, $config);

            // Load the DB
            $this->load->database($db[$active_group], false, true);
            $this->db->save_queries = true;

            // Lets test DB connection!
            $this->db->list_tables();

            $this->init();
        } catch (Exception $e) {
            $out = array('success' => 'no', 'body' => '');
            $out['body'] = $e->getMessage();

            if ($out['body'] == '')
            {
                $out['body'] = 'Initialization Failed';
            }

            exit($this->generateJson($out));
        }
    }

    //********************************************************************************* //

    public function index()
    {
        // If it's an AJAX request we will need to fail here!
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) === true &&  $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            // Sometimes our URL doesn't get properly detected and
            // we are always sent to the index controller, here we try to detect that
            if (isset($_SERVER['QUERY_STRING']) === true) {
                $query = $_SERVER['QUERY_STRING'];
                $real_segments = explode('/', trim($_SERVER['QUERY_STRING'], '/') );
                $segments = $this->uri->total_segments();

                // Our "real_segments" should always have more segments.
                if (count($real_segments) > $segments) {
                    $controller = $real_segments[1];

                    // We want the other controllers of course
                    if ($controller != 'index') {
                        $this->{$controller}();
                        exit();
                    }
                }

            }

            $out = array('success' => 'no', 'body' => '');
            exit ( $this->generateJson($out) );
        }

        $data = array();
        $data['dupdater'] = $this;

        // Else lets show our stuff
        $this->load->view('index', $data);
    }

    //********************************************************************************* //

    private function init()
    {
        // Parse Server Paths
        $this->action_obj = $this->decodeJson($this->input->post('action_obj'));
        $this->server = $this->input->post('server');

        // -----------------------------------------
        // EE_PATH_THIRD
        // We can't rely on "$this->server->system_third_party" to be available.
        // -----------------------------------------
        if ($this->config->item('third_party_path')) {
            if (defined('EE_PATH_THIRD') === false) define('EE_PATH_THIRD', rtrim(realpath($this->config->item('third_party_path')), '/').'/');
        } else {
            if (defined('EE_PATH_THIRD') === false) define('EE_PATH_THIRD', EE_APPPATH.'third_party/');
        }

        // -----------------------------------------
        // PATH THIRD CONSTANT
        // -----------------------------------------
        if ($this->config->item('third_party_path')) {
            define('PATH_THIRD',    rtrim($this->config->item('third_party_path'), '/').'/');
        } else {
            define('PATH_THIRD',    EE_APPPATH.'third_party/');
        }

        if (defined('AJAX_REQUEST') === false) define('AJAX_REQUEST', true);

        // EE 2.6+
        if (file_exists(EE_APPPATH.'libraries/Logger.php') === true) {
            $this->load->library('logger');
        }

        $this->load->helper(array('form', 'url', 'html', 'directory', 'file', 'email', 'security', 'date', 'string'));
        $this->load->library('localize'); // This call can't be AFTER add_package_path

        // Load EE Libraries
        $this->load->add_package_path(EE_APPPATH);
        $this->load->library('cp');

        // EE 2.6+
        if (file_exists(EE_APPPATH.'libraries/Smartforge.php') === true) {
            $this->load->library('smartforge');
        }

        // Load Updater Libraries
        $this->load->add_package_path(EE_PATH_THIRD . 'updater/');

        $this->EE =& get_instance();
        $this->EE->load->config('updater_config');
        $this->EE->debug_updater = ($this->EE->config->item('updater_debug') == 'yes') ? true : false ;

        $this->EE->load->library('firephp');
        $this->EE->load->library('updater_helper');
        $this->EE->load->library('updater_transfer');

        $this->EE->updater_transfer->init();

        // Store the path maps
        $this->maps = $this->EE->updater_transfer->map;

        // Settings
        $this->settings = $this->EE->updater->settings;

        if ($this->settings['infinite_memory'] == 'yes') {
            ini_set('memory_limit', -1);
        }
    }

    //********************************************************************************* //

    public function copy_files_prepare()
    {
        //$this->EE->db->query("SELECT * FROM foo_table"); // Trigger DB error
        $out = array('success' => 'yes', 'body' => '');
        $temp_dir = $this->action_obj->info->root_location;

        // -----------------------------------------
        // We don't need these..
        // -----------------------------------------
        @unlink($temp_dir.'system/expressionengine/config/config.php');
        @unlink($temp_dir.'system/expressionengine/config/database.php');

        copy(EE_APPPATH.'config/config.php', $temp_dir.'system/expressionengine/config/config.php');
        copy(EE_APPPATH.'config/database.php', $temp_dir.'system/expressionengine/config/database.php');

        $codeigniter_map = directory_map($temp_dir.'system/codeigniter/system/', 2);
        $ee_map = directory_map($temp_dir.'system/expressionengine/', 2);
        $themes_map = directory_map($temp_dir.'themes/', 2);

        // Loop over all directories in the Codeigniter DIR
        foreach ($codeigniter_map as $dir => $arr) {
            if (is_array($arr) === false) continue;
            if ($dir == 'cache' || $dir == 'logs') continue;
            if (strpos($dir, '_OLD') !== false) continue;
            $out['dirs'][] = 'system/codeigniter/system/'.$dir;
        }

        // Loop over all directories in the EE system dir
        foreach ($ee_map as $dir => $arr) {
            if (is_array($arr) === false) continue;

            if ($dir == 'modules' OR $dir == 'third_party') {
                $modules_map = directory_map($temp_dir.'system/expressionengine/'.$dir.'/', 2);

                $modules_found = array();
                foreach ($this->native_modules as $mod) {
                    if (file_exists(EE_APPPATH.'modules/'.$mod) === true)
                    {
                        $modules_found[] = $mod;
                    }
                }

                foreach ($modules_map as $module_dir => $module_arr) {
                    if (is_array($module_arr) === false) continue;
                    if (in_array($module_dir, $modules_found) === false) continue;
                    $out['dirs'][] = 'system/expressionengine/'.$dir.'/'.$module_dir;
                }

                continue;
            }

            if ($dir == 'cache' || $dir == 'templates') continue;
            if (strpos($dir, '_OLD') !== false) continue;
            $out['dirs'][] = 'system/expressionengine/'.$dir;
        }

        // Loop over native ones to see which ones we found
        $native_themes_found = array();
        foreach ($this->native_theme_dirs as $val) {
            $native_theme_dirs[$val] = file_exists($this->maps['themes'].$val);
        }

        // Loop over all directories in the Themes dir
        foreach ($themes_map as $dir => $arr) {
            if (is_array($arr) === false) continue;
            if ($dir == 'third_party') continue;
            if (strpos($dir, '_OLD') !== false) continue;

            if (isset($native_theme_dirs[$dir]) === true) {
                if ($native_theme_dirs[$dir] === false) continue;
            }

            $out['dirs'][] = 'themes/'.$dir;
        }


        exit($this->generateJson($out));

        $out['success'] = 'yes';
        return $out;
    }

    //********************************************************************************* //

    public function copy_files()
    {
        $out = array('success' => 'no', 'body' => '');
        $dir = $this->EE->input->post('dir');
        $temp_dir = $this->action_obj->info->root_location;

        // -----------------------------------------
        // Parse the Source Dir
        // -----------------------------------------
        $source_dir = '';
        $basedir = '';
        $location = 'system';

        if (strpos($dir, 'system/codeigniter') === 0) {
            $source_dir = $temp_dir.$dir;
            $basedir = 'codeigniter/system/';
            $location = 'system';
        } elseif (strpos($dir, 'system/expressionengine') === 0) {
            $source_dir = $temp_dir.$dir;
            $basedir = 'expressionengine/';
            if (strpos($dir, '/third_party/') !== false) $basedir = 'expressionengine/third_party/';
            if (strpos($dir, '/modules/') !== false) $basedir = 'expressionengine/modules/';

            $location = 'system';
        } elseif (strpos($dir, 'themes/') === 0) {
            $source_dir = $temp_dir.$dir;
            $basedir = '';
            $location = 'themes';
        } else {
            $out['body'] = 'Path not recognized!';
            exit($this->EE->updater_helper->generateJson($out));
        }

        // -----------------------------------------
        // Copy Files!
        // -----------------------------------------
        try {
            $dirname = basename($source_dir);

            /*
            $exists = $this->EE->updater_transfer->dir_exists($location, $basedir.$dirname);

            $exists_old = $this->EE->updater_transfer->dir_exists($location, $basedir.$dirname.'_OLD');
            if ($exists_old) $this->EE->updater_transfer->delete($location, $basedir.$dirname.'_OLD', 'dir');

            if ($exists) $this->EE->updater_transfer->rename($location, $basedir.$dirname, $basedir.$dirname.'_OLD');
             */
            $this->EE->updater_transfer->mkdir($location, $basedir.$dirname);
            $this->EE->updater_transfer->upload($location, $source_dir, $basedir.$dirname, 'dir', true);

            /*
            $this->EE->updater_transfer->delete($location, $basedir.$dirname.'_OLD', 'dir');
             */
        } catch (Exception $e) {
            $out['body'] = $e->getMessage();
            exit($this->generateJson($out));
        }

        $out = array('success' => 'yes', 'body' => '');
        exit($this->generateJson($out));
    }

    //********************************************************************************* //

    public function update_ee()
    {
        //$this->EE->db->query("SELECT * FROM foo_table"); // Trigger DB error

        $out = array('success' => 'no', 'body' => '');
        $version = $this->input->post('version');
        $temp_dir = $this->action_obj->info->root_location;

        // -----------------------------------------
        // Upgrade!
        // -----------------------------------------
        if (file_exists($temp_dir.'system/installer/updates/ud_'.$version.'.php') === false) {
            $out['body'] = 'Install file not found!';
            exit($this->generateJson($out));
        }

        $this->load->add_package_path($temp_dir.'system/installer/');
        $this->load->library('progress');
        $this->load->library('layout');

        $file = $temp_dir.'system/installer/updates/ud_'.$version.'.php';
        require($file);

        $this->db->queries = array();
        $UP = new Updater();
        $res = $UP->do_update();

        //$this->EE->firephp->log($res);

        if ($res == false) {
            $error_msg = $this->lang->line('update_error');

            if (! empty($UP->errors)) {
                $error_msg .= "</p>\n\n<ul>\n\t<li>" . implode("</li>\n\t<li>", $UP->errors) . "</li>\n</ul>\n\n<p>";
            }

            $out['body'] = $error_msg;
            exit ($this->generateJson($out));
        }

        // Update the APP Version!
        $this->config->_update_config(array('app_version' => $version) );

        $out['queries'] = $this->db->queries;
        $out['success'] = 'yes';
        exit ( $this->generateJson($out) );
    }

    //********************************************************************************* //

    public function update_modules()
    {
        $out = array('success' => 'no', 'body' => '');


        $this->EE->db->select('module_name, module_version');
        $query = $this->EE->db->get('modules');

        // Clean it up
        $this->db->queries = array();

        foreach ($query->result() as $row) {
            $module = strtolower($row->module_name);

            /*
             * - Send version to update class and let it do any required work
             */
            if (in_array($module, $this->native_modules)) {
                $path = EE_APPPATH.'/modules/'.$module.'/';
            } else {
                continue; // FOR NOW, SKIP THIRD PARTY MODULES!
                $path = EE_PATH_THIRD.$module.'/';
            }

            // Just in case lets define it!
            if (defined('PATH_THIRD') === false) define('PATH_THIRD', EE_PATH_THIRD);

            if (file_exists($path.'upd.'.$module.EXT)) {
                $class = ucfirst($module).'_upd';

                if ( ! class_exists($class)) {
                    require $path.'upd.'.$module.EXT;
                }

                $UPD = new $class;
                $UPD->_ee_path = EE_APPPATH;

                if ($UPD->version > $row->module_version && method_exists($UPD, 'update') && $UPD->update($row->module_version) !== false) {
                    $this->EE->db->update('modules', array('module_version' => $UPD->version), array('module_name' => ucfirst($module)));
                }
            }
        }

        $out['queries'] = $this->db->queries;
        $out['success'] = 'yes';
        exit ( $this->generateJson($out) );
    }

    //********************************************************************************* //

    public function cleanup($manual=false)
    {
        $this->config->_update_config(array('is_system_on' => 'y') );

        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        $ee_info = $this->EE->input->get_post('ee_info');
        $this->EE->updater->stats->event = 'ee-update';
        $this->EE->updater->stats->data['ee_version_from'] = $ee_info['version_from'];
        $this->EE->updater->stats->data['ee_build_from'] = $ee_info['build_from'];
        $this->EE->updater->stats->data['ee_version_to'] = $ee_info['version_to'];
        $this->EE->updater->stats->data['ee_build_to'] = '';
        $this->EE->updater->stats->data['update_steps'] = (int) $ee_info['update_steps'];

        if ($this->EE->updater->stats->data['ee_build_to'] == false) {
            $file = file_get_contents(EE_APPPATH.'libraries/Core.php');

            preg_match('/APP_BUILD.*\'(.*?)\'/', $file, $matches);
            $build = end($matches);

            $this->EE->updater->stats->data['ee_build_to'] = $build;
        }

        $this->EE->updater->sendStats();

        // -----------------------------------------
        // Rename Installer
        // -----------------------------------------
        try {
            $exists = $this->EE->updater_transfer->dir_exists('system', 'installer');
            if ($exists) $this->EE->updater_transfer->rename('system', 'installer', 'installer_OLD');

            //$this->EE->updater_transfer->delete('system', 'expressionengine/cache/updater/', 'dir');
            $this->delete_files($this->EE->updater->cache_path.'updater/', true);

            if ($this->EE->updater_transfer->dir_exists('system', 'installer_OLD') == true) {
                $this->EE->updater_transfer->delete('system', 'installer_OLD', 'dir');
            }

        } catch (Exception $e) {
            if ($manual == true) return $e->getMessage();

            $out['body'] = $e->getMessage();
            exit($this->generateJson($out));
        }

        if ($manual == true) {
            return 'Success...';
        }

        $out = array('success' => 'yes', 'body' => '');
        exit($this->generateJson($out));
    }

    //********************************************************************************* //

    public function generateJson($obj)
    {
        if (function_exists('json_encode') === false) {

            if (class_exists('Services_JSON_CUSTOM') === false) {
                include_once APPPATH.'libraries/JSON.php';
            }

            $JSON = new Services_JSON_CUSTOM();
            return $JSON->encode($obj);

        } else {
            return json_encode($obj);
        }
    }

    // ********************************************************************************* //

    public function decodeJson($obj, $return_array=false)
    {
        if (function_exists('json_decode') === false) {

            if (class_exists('Services_JSON_CUSTOM') === false) {
                include_once APPPATH.'libraries/JSON.php';
            }

            $JSON = new Services_JSON_CUSTOM();
            $obj = $return_array ? (array) $JSON->decode($obj) : $JSON->decode($obj);
            return $obj;
        }
        else {
            return json_decode($obj, $return_array);
        }
    }

    // ********************************************************************************* //

    private function delete_files($path, $del_dir = false, $level = 0)
    {
        // Trim the trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if ( ! $current_dir = @opendir($path)) {
            return false;
        }

        while(false !== ($filename = @readdir($current_dir))) {
            if ($filename != "." and $filename != "..") {
                if (is_dir($path.DIRECTORY_SEPARATOR.$filename)) {
                    // Ignore empty folders
                    if (substr($filename, 0, 1) != '.') {
                        $this->delete_files($path.DIRECTORY_SEPARATOR.$filename, $del_dir, $level + 1);
                    }
                } else {
                    unlink($path.DIRECTORY_SEPARATOR.$filename);
                }
            }
        }
        @closedir($current_dir);

        if ($del_dir == true AND $level > 0) {
            return @rmdir($path);
        }

        return true;
    }

    // ********************************************************************************* //
}

