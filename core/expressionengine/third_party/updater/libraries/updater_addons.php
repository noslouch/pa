<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Updater Tests File
 *
 * @package         DevDemon_Updater
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com
 */
class Updater_addons
{
    private $addons = array();
    public $queries_executed = array();

    public function __construct()
    {
        // Creat EE Instance
        $this->EE =& get_instance();
        $this->EE->load->helper('file');
        $this->EE->load->helper('directory');

        // Set the EE Cache Path? (hell you can override that)
        $this->cache_path = $this->EE->config->item('cache_path') ? $this->EE->config->item('cache_path') : APPPATH.'cache/';
    }

    // ********************************************************************************* //

    public function detectFromPath($path)
    {
        $dir_contents =& $this->EE->updater->temp_dir_contents;

        //$this->EE->firephp->log($dir_contents);

        // -----------------------------------------
        // Try to detect addon
        // -----------------------------------------
        $res = $this->processAddonJson($path, $dir_contents);

        if ($res === false) {
            $this->processAddonLegacy($path, $dir_contents);
        }

        if (empty($this->addons) === true) {
            return false;
        }

        foreach ($this->addons as &$addon)
        {
            $installed = $this->isAddonInstalled($addon);
            $addon->updater_action = ($installed == true) ? 'update' : 'install';


            // -----------------------------------------
            // Send Stats
            // -----------------------------------------
            $this->EE->updater->stats->event = 'addon-processed';
            $this->EE->updater->stats->data['addon_name'] = $addon->name;
            $this->EE->updater->stats->data['addon_label'] = $addon->label;
            $this->EE->updater->stats->data['addon_version'] = ($addon->version == '(N/A)') ? 0 : (float) $addon->version;
            $this->EE->updater->stats->data['addon_types'] = $addon->types;
            $this->EE->updater->stats->data['detection_type'] = $addon->detection_type;
            $this->EE->updater->stats->data['filename'] = $addon->filename;
            $this->EE->updater->sendStats();
        }

        return $this->addons;
    }

    // ********************************************************************************* //

    private function processAddonJson($dir, $dir_contents)
    {
        $JSON_path = false;
        $JSON_filename = false;

        // -----------------------------------------
        // Find the JSON.. Wherever it is!
        // -----------------------------------------
        $json_files = array('addon.json', 'package.json');

        foreach($json_files as $json_file) {
            $length = strlen($json_file);

            if (in_array($json_file, $dir_contents) === true) {
                $JSON_path = $json_file;
                $JSON_filename = $json_file;
                continue;
            } else {
                foreach ($dir_contents as $file) {
                    if (substr($file, -$length) == $json_file) {
                        $JSON_path = $file;
                        $JSON_filename = $json_file;
                    }
                }
            }
        }

        // JSON Path found? bail
        if (!$JSON_path) {
            return false;
        }

        $JSON_path = $dir.$JSON_path;

        $contents = @read_file($JSON_path);
        if (!$contents) {
            return false;
        }

        $JSON_path_dir = str_replace($JSON_filename, '', $JSON_path);

        // Lets just be sure and add the latest slash!
        $JSON_path_dir = rtrim($JSON_path_dir, '\\/').DIRECTORY_SEPARATOR;

        // -----------------------------------------
        // Decode the JSON
        // -----------------------------------------
        $json = $this->EE->updater_helper->decodeJson($contents);
        if ($json == false) {
            return false;
        }

        // -----------------------------------------
        // Multiple Addons?
        // -----------------------------------------
        if (isset($json->addons) === true) {
            foreach ($json->addons as $addon) {
                // Lets do some checks!
                if (!isset($addon->name) || !isset($addon->paths) ) continue;

                $addon->detection_type = 'package.json';
                $addon->root_location = rtrim($JSON_path_dir, '\\/').DIRECTORY_SEPARATOR;
                $addon->filename = $this->EE->updater->temp_zip_filename;
                $this->addons[$addon->name] = $addon;
            }
        } else {
            // -----------------------------------------
            // Single Addon
            // -----------------------------------------

            // Lets do some checks!
            if (!isset($json->name) || !isset($json->paths) ) {
                return false;
            }

            $json->detection_type = 'package.json';
            $json->root_location = rtrim($JSON_path_dir, '\\/').DIRECTORY_SEPARATOR;
            $json->filename = $this->EE->updater->temp_zip_filename;
            $this->addons[$json->name] = $json;
        }

        return true;
    }

    // ********************************************************************************* //

    private function processAddonLegacy($dir, $dir_contents)
    {
        $dirs = $this->getAddonDirsLegacy($dir_contents);

        if ($dirs == false) {
            return false;
        }

        $system_dir = $dir.'/'.$dirs['system'];

        // Map the directory
        $subdirs = directory_map($system_dir, 2);

        foreach ($subdirs as $addon => $sub) {

            // We only want real dirs. (not eg: index.html)
            if (is_array($sub) === false) continue;

            $this->getAddonInfoLegacy($addon, $system_dir.'/'.$addon, $dirs);
        }
    }

    // ********************************************************************************* //

    private function getAddonDirsLegacy($dir_contents)
    {
        $dir = array();
        $dir['system'] = false;
        $dir['themes'] = false;

        $slash = DIRECTORY_SEPARATOR;
        $system_dirs = array();
        $system_dirs[] = array("path" => "system{$slash}third_party", 'length' => strlen("system{$slash}third_party"));
        $system_dirs[] = array("path" => "system{$slash}expressionengine{$slash}third_party", 'length' => strlen("system{$slash}expressionengine{$slash}third_party"));
        $system_dirs[] = array("path" => "ee2{$slash}third_party", 'length' => strlen("ee2{$slash}third_party"));

        $theme_dirs = array();
        $theme_dirs[] = array("path" => "themes{$slash}third_party", 'length' => strlen("themes{$slash}third_party"));

        foreach ($dir_contents as $file) {
            if (strpos($file, '__MACOSX') !== false) continue;
            if (strpos($file, '.svn') !== false) continue;

            foreach ($system_dirs as $sdir) {
                if (substr($file, -$sdir['length']) === $sdir['path']) {
                    $dir['system'] = ltrim($file, DIRECTORY_SEPARATOR);
                    continue 2;
                }
            }

            foreach ($theme_dirs as $tdir) {
                if (substr($file, -$tdir['length']) === $tdir['path']) {
                    $dir['themes'] = ltrim($file, DIRECTORY_SEPARATOR);
                    continue 2;
                }
            }

        }

        if (isset($dir['system']) == false || $dir['system'] == false) return false;

        return $dir;
    }

    // ********************************************************************************* //

    private function getAddonInfoLegacy($addon_class, $system_dir_path, $dirs)
    {
        $addon = false;
        $dummy_addon = new stdClass();
        $dummy_addon->name = $addon_class;
        $dummy_addon->label = ucfirst($addon_class);
        $dummy_addon->version = '(N/A)';

        // -----------------------------------------
        // Config.php?
        // -----------------------------------------
        if (file_exists($system_dir_path.'/config.php') === true) {
            $file = @file_get_contents($system_dir_path.'/config.php');

            if (strpos($file, 'PATH_THIRD') === false) {
                require_once $system_dir_path.'/config.php';

                if (isset($config['version']) === true) {
                    $addon = new stdClass();
                    $addon->name = $addon_class;
                    $addon->label = $config['name'];
                    $addon->version = $config['version'];

                    $addon->detection_type = 'config.php'; // We have to do it here since sometimes config.php is compatible
                }
            } else {
                $addon = $dummy_addon;
                $addon->detection_type = 'config.php'; // We have to do it here since sometimes config.php is compatible
            }
        }

        // -----------------------------------------
        // Module?
        // -----------------------------------------
        if ($addon == false && file_exists("{$system_dir_path}/upd.{$addon_class}.php") === true) {
            $file = file_get_contents("{$system_dir_path}/upd.{$addon_class}.php");

            if (strpos($file, 'PATH_THIRD') === false) {
                require_once "{$system_dir_path}/upd.{$addon_class}.php";
                $vars = get_class_vars( ucfirst($addon_class.'_upd') );

                $this->EE->lang->load($addon_class, $this->EE->lang->user_lang, false, true, $system_dir_path . '/');

                $addon = new stdClass();
                $addon->name = $addon_class;
                $addon->label = $this->EE->lang->line($addon_class.'_module_name');
                $addon->version = $vars['version'];
            } else {
                $addon = $dummy_addon;
            }

            $addon->detection_type = 'addon_file';
        }

        // -----------------------------------------
        // Fieldtype?
        // -----------------------------------------
        if ($addon == false && file_exists("{$system_dir_path}/ft.{$addon_class}.php") === true) {
            $file = file_get_contents("{$system_dir_path}/ft.{$addon_class}.php");
            if (strpos($file, 'PATH_THIRD') === false) {
                require_once APPPATH . 'fieldtypes/EE_Fieldtype.php';
                require_once "{$system_dir_path}/ft.{$addon_class}.php";
                $vars = get_class_vars( ucfirst($addon_class.'_ft') );

                $addon = new stdClass();
                $addon->name = $addon_class;
                $addon->label = $vars['info']['name'];
                $addon->version = $vars['info']['version'];
            } else {
                $addon = $dummy_addon;
            }

            $addon->detection_type = 'addon_file';
        }

        // -----------------------------------------
        // Extension?
        // -----------------------------------------
        if ($addon == false && file_exists("{$system_dir_path}/ext.{$addon_class}.php") === true) {
            $file = file_get_contents("{$system_dir_path}/ext.{$addon_class}.php");
            if (strpos($file, 'PATH_THIRD') === false) {
                require_once "{$system_dir_path}/ext.{$addon_class}.php";
                $vars = get_class_vars( ucfirst($addon_class.'_ext') );

                $addon = new stdClass();
                $addon->name = $addon_class;
                $addon->label = $vars['name'];
                $addon->version = $vars['version'];
            } else {
                $addon = $dummy_addon;
            }

            $addon->detection_type = 'addon_file';
        }

        // -----------------------------------------
        // Accesories
        // -----------------------------------------
        if ($addon == false && file_exists("{$system_dir_path}/acc.{$addon_class}.php") === true)
        {
            $file = file_get_contents("{$system_dir_path}/acc.{$addon_class}.php");
            if (strpos($file, 'PATH_THIRD') === false) {
                require_once "{$system_dir_path}/acc.{$addon_class}.php";
                $vars = get_class_vars( ucfirst($addon_class.'_acc') );

                $addon = new stdClass();
                $addon->name = $addon_class;
                $addon->label = $vars['name'];
                $addon->version = $vars['version'];
            } else {
                $addon = $dummy_addon;
            }

            $addon->detection_type = 'addon_file';
        }

        // -----------------------------------------
        // RTE
        // -----------------------------------------
        if ($addon == false && file_exists("{$system_dir_path}/rte.{$addon_class}.php") === true) {
            $file = file_get_contents("{$system_dir_path}/rte.{$addon_class}.php");
            if (strpos($file, 'PATH_THIRD') === false) {
                require_once "{$system_dir_path}/rte.{$addon_class}.php";
                $vars = get_class_vars( ucfirst($addon_class.'_rte') );

                $addon = new stdClass();
                $addon->name = $addon_class;
                $addon->label = $vars['info']['name'];
                $addon->version = $vars['info']['version'];
            }
            else {
                $addon = $dummy_addon;
            }

            $addon->detection_type = 'addon_file';
        }

        // -----------------------------------------
        // Plugin
        // -----------------------------------------
        if ($addon == false && file_exists("{$system_dir_path}/pi.{$addon_class}.php") === true) {
            $file = file_get_contents("{$system_dir_path}/pi.{$addon_class}.php");
            if (strpos($file, 'PATH_THIRD') === false) {
                $plugin_info = require_once "{$system_dir_path}/pi.{$addon_class}.php";

                $addon = new stdClass();
                $addon->name = $addon_class;
                $addon->label = $plugin_info['pi_name'];
                $addon->version = $plugin_info['pi_version'];
            }
            else {
                $addon = $dummy_addon;
            }

            $addon->detection_type = 'addon_file';
        }

        if ($addon == false) {
            $this->addon_zip_errors[] = $addon_class;
            return false;
        }

        // in some rare cases the version is not defined!
        if (isset($addon->version) === false) {
            $addon->version = '(N/A)';
        }

        // Double check label/verion
        $addon->version = trim($addon->version);
        $addon->label = trim($addon->label);
        if (!$addon->label) $addon->label = ucfirst($addon->name);
        if (!$addon->version) $addon->version = '(N/A)';

        $addon->schema_version = '1.0';

        $addon->root_location = rtrim($this->EE->updater->temp_dir, '\\/').DIRECTORY_SEPARATOR;;
        $addon->paths = new stdClass();
        $addon->paths->system = array();
        $addon->paths->system[] = $dirs['system'].'/'.$addon_class;

        // -----------------------------------------
        // Any Theme Dirs?
        // -----------------------------------------
        if (isset($dirs['themes']) === true && $dirs['themes'] != false) {

            // Double check to see if it really exists
            if (file_exists($addon->root_location.$dirs['themes'].'/'.$addon_class) === true) {
                $addon->paths->themes = array();
                $addon->paths->themes[] = $dirs['themes'].'/'.$addon_class;
            }
        }

        // -----------------------------------------
        // Addon Types?
        // -----------------------------------------
        $addon->types = array();
        $types = array('upd' => 'module', 'ext' => 'extension', 'ft' => 'fieldtype', 'pi' => 'plugin', 'acc' => 'accessory', 'rte' => 'rte');

        foreach ($types as $prefix => $type) {
            if (file_exists("{$system_dir_path}/{$prefix}.{$addon_class}.php") === true) {
                $addon->types[] = $type;
            }
        }



        // Last Check!
        if (empty($addon->types) === true) {
            return false;
        }

        $addon->filename = $this->EE->updater->temp_zip_filename;

        $this->addons[$addon_class] = $addon;
        return true;
    }

    // ********************************************************************************* //

    public function isAddonInstalled($addon)
    {
        $installed = false;

        foreach ($addon->types as $type) {

            // Module
            if ($type == 'module') {
                $query = $this->EE->db->select('module_id, module_version')->from('exp_modules')->where('module_name', ucfirst($addon->name))->get();
            }

            // Fieldtype
            else if ($type == 'fieldtype') {
                $query = $this->EE->db->select('fieldtype_id, version')->from('exp_fieldtypes')->where('name', $addon->name)->get();
            }

            // Extension
            else if ($type == 'extension') {
                $query = $this->EE->db->select('fieldtype_id, version')->from('exp_fieldtypes')->where('name', $addon->name)->get();
            }

            // accessory
            else if ($type == 'accessory') {
                $query = $this->EE->db->select('fieldtype_id, version')->from('exp_fieldtypes')->where('name', $addon->name)->get();
            }

            // rte_tool
            else if ($type == 'rte') {
                $query = $this->EE->db->select('fieldtype_id, version')->from('exp_fieldtypes')->where('name', $addon->name)->get();
            }

            else if ($type == 'plugin') {
                return file_exists(PATH_THIRD."{$addon->name}/pi.{$addon->name}.php");
            }

            else {
                return false;
            }

            return ($query->num_rows() == 0) ? false : true;
        }

        return $installed;
    }

    // ********************************************************************************* //

    public function moveFiles($obj)
    {
        $addon = $obj->info;

        // Transfer all system
        foreach ($addon->paths->system as $path)
        {
            $dirname = basename($path);
            $update = $this->EE->updater_transfer->dir_exists('system_third_party', $dirname);

            $this->EE->updater_transfer->mkdir('system_third_party', $dirname);
            $this->EE->updater_transfer->upload('system_third_party', $addon->root_location.$path, $dirname);
        }

        // Transfer all themes
        if (isset($addon->paths->themes) === TRUE && is_array($addon->paths->themes) === TRUE)
        {
            foreach ($addon->paths->themes as $path)
            {
                // Sometimes these just don't exists!
                if (file_exists($addon->root_location.$path) === FALSE) continue;
                $dirname = basename($path);

                $this->EE->updater_transfer->mkdir('themes_third_party', $dirname);
                $this->EE->updater_transfer->upload('themes_third_party', $addon->root_location.$path, $dirname);
            }
        }

        // Do we need to put something in root?
        if (isset($addon->paths->root) === TRUE && is_array($addon->paths->root) === TRUE)
        {
            foreach ($addon->paths->root as $path)
            {
                // Sometimes these just don't exists!
                if (file_exists($addon->root_location.$path) === FALSE) continue;

                $dirname = basename($path);
                $exists = $this->EE->updater_transfer->dir_exists('root', $dirname);

                // Why do we do this? Example:
                // BrilliantRetail uses /media dir to store product images
                if (!$exists)
                {
                    $this->EE->updater_transfer->mkdir('root', $dirname);
                    $this->EE->updater_transfer->upload('root', $addon->root_location.$path, $dirname);
                }
                else
                {
                    $this->EE->updater_transfer->upload('root', $addon->root_location.$path, $dirname);
                }
            }
        }

/*
        // -----------------------------------------
        // Force Database Backup First?
        // -----------------------------------------
        if (file_exists(PATH_THIRD."{$addon->name}/upd.{$addon->name}.php") === TRUE)
        {
            // Module installed?
            $query = $this->EE->db->select('module_version')->from('exp_modules')->where('module_name', ucfirst($addon->name) )->get();

            if ($query->num_rows() > 0)
            {
                $version = $query->row('module_version');

                require PATH_THIRD."{$addon->name}/upd.{$addon->name}.php";
                $class = ucfirst($addon->name.'_upd');
                $UPD = new $class();

                if (method_exists($UPD, 'database_backup_required') === TRUE)
                {
                    $ret = $UPD->database_backup_required($version);

                    if ($ret == TRUE)
                    {
                        $out['force_db_backup'] = 'yes';
                    }
                }

                // -----------------------------------------
                // Update Notes?
                // -----------------------------------------
                if (isset($addon->update_notes) === TRUE && is_array($addon->update_notes) === TRUE)
                {
                    $out['update_notes'] = array();

                    foreach ($addon->update_notes as $note)
                    {
                        if (isset($note->version) === FALSE) continue;
                        if (version_compare($note->version, $version, '>=') === TRUE)
                        {
                            $out['update_notes'][] = $note;
                        }
                    }
                }
            }
        }
    */
    }

    // ********************************************************************************* //

    public function installUpdateAddon($obj)
    {
        $this->EE->load->library('addons/addons_installer');
        $addon_info = $obj->info;
        $addon = $addon_info->name;

        $event = false;
        $addon_label = '';
        $version_from = '';
        $version_to = ($addon_info->version == '(N/A)') ? false : $addon_info->version;

        // Add the package path
        $this->EE->load->add_package_path(PATH_THIRD.$addon.'/');

        $files = directory_map(PATH_THIRD . $addon.'/');

        if ($files == false)
        {
            // Sometimes we are too fast.. Last give it a break
            sleep(3);
            $files = directory_map(PATH_THIRD . $addon.'/');

            if ($files == false)
            {
                // Still not? Lets give it 3 more seconds!
                sleep(3);
                $files = directory_map(PATH_THIRD . $addon.'/');
            }
        }


        // -----------------------------------------
        // Module?
        // -----------------------------------------
        if (in_array("upd.{$addon}.php", $files) === true)
        {
            // Module Installed?
            $query = $this->EE->db->select('module_id, module_version')->from('exp_modules')->where('module_name', ucfirst($addon))->get();

            require_once PATH_THIRD.$addon.'/upd.'.$addon.'.php';
            $class = ucfirst($addon).'_upd';
            $UPD = new $class;
            $UPD->_ee_path = APPPATH;



            $this->EE->updater->getQueries('start');

            if ($query->num_rows() == 0)
            {
                // Install Module
                // We need to do it manually since EE's installer is heavily dependent on the CP Class
                if ($UPD->install() !== true)
                {
                    $out['addons']['success'] = 'no';
                    exit($this->EE->updater_helper->generate_json($out));
                }

                $event = 'install';
            }
            else
            {
                // Update Module
                $version_from = $query->row('module_version');
                $module_id = $query->row('module_id');

                if (version_compare($UPD->version, $version_from, '>'))
                {
                    if (method_exists($UPD, 'update') === true)
                    {
                        $UPD->update($version_from);
                    }

                    $this->EE->db->set('module_version', $UPD->version);
                    $this->EE->db->where('module_id', $module_id);
                    $this->EE->db->update('exp_modules');

                    $event = 'update';
                }

            }

            $version_to = $UPD->version;

            $this->EE->lang->loadfile($addon);
            $addon_label = $this->EE->lang->line($addon.'_module_name');

            $this->EE->updater->getQueries('end');
        }

        // -----------------------------------------
        // Fieldtype?
        // -----------------------------------------
        if (in_array("ft.{$addon}.php", $files) === true)
        {
            // Fieldtype installed?
            $query = $this->EE->db->select('fieldtype_id, version')->from('exp_fieldtypes')->where('name', $addon)->get();

            // Include the addon files
            require_once APPPATH . 'fieldtypes/EE_Fieldtype.php';
            require_once PATH_THIRD.$addon.'/ft.'.$addon.'.php';
            $class = ucfirst($addon).'_ft';
            $FT = new $class;

            $this->EE->updater->getQueries('start');

            if ($query->num_rows() == 0)
            {
                // Install the fieldtype
                if (!$this->EE->addons_installer->install($addon, 'fieldtype', false))
                {
                    $out['addons']['success'] = 'no';
                    exit($this->EE->updater_helper->generate_json($out));
                }

                if ($event == false) $event = 'install';
            }
            else
            {
                // Update fieldtype
                $version_from = $query->row('version');
                $fieldtype_id = $query->row('fieldtype_id');

                if (version_compare($FT->info['version'], $version_from, '>'))
                {
                    if (method_exists($FT, 'update') === true)
                    {
                        $FT->update($version_from);
                    }

                    $this->EE->db->set('version', $FT->info['version']);
                    $this->EE->db->where('fieldtype_id', $fieldtype_id);
                    $this->EE->db->update('exp_fieldtypes');

                    if ($event == false) $event = 'update';
                }
            }

            if ($version_to == false) $version_to = $FT->info['version'];
            if ($addon_label == false) $addon_label = $FT->info['name'];

            $this->EE->updater->getQueries('end');
        }

        // -----------------------------------------
        // Extension?
        // -----------------------------------------
        if (in_array("ext.{$addon}.php", $files) === true)
        {
            require_once PATH_THIRD.$addon.'/ext.'.$addon.'.php';
            $class = ucfirst($addon).'_ext';
            $EXT = new $class();

            // Extension installed?
            $query = $this->EE->db->select('extension_id, version, enabled')->from('exp_extensions')->where('class', $class)->get();

            $this->EE->updater->getQueries('start');

            if ($query->num_rows() == 0)
            {
                // Install Extension
                // We need to do it manually since EE's installer is heavily dependent on the CP Class
                if (method_exists($EXT, 'activate_extension') === true)
                {
                    $activate = $EXT->activate_extension();
                }

                if ($event == false) $event = 'install';
            }
            else
            {
                // Update Extension
                $version_from = $query->row('version');
                $extension_id = $query->row('extension_id');

                if (version_compare($EXT->version, $version_from, '>'))
                {
                    if (method_exists($EXT, 'update_extension') === true)
                    {
                        $EXT->update_extension($version_from);
                    }

                    $this->EE->db->set('version', $EXT->version);
                    $this->EE->db->where('extension_id', $extension_id);
                    $this->EE->db->update('exp_extensions');

                    if ($event == false) $event = 'update';
                }
            }

            if ($version_to == false) $version_to = $EXT->version;
            if ($addon_label == false) $addon_label = $EXT->name;
            $this->EE->updater->getQueries('end');
        }

        // -----------------------------------------
        // Accessory
        // -----------------------------------------
        if (in_array("acc.{$addon}.php", $files) === true)
        {
            $this->EE->load->library('accessories');

            require_once PATH_THIRD.$addon.'/acc.'.$addon.'.php';
            $class = ucfirst($addon).'_acc';
            $ACC = new $class();

            // Accessory installed?
            $query = $this->EE->db->select('accessory_id, accessory_version')->from('exp_accessories')->where('class', $class)->get();

            $this->EE->updater->getQueries('start');

            if ($query->num_rows() == 0)
            {
                // Install Extension
                // We need to do it manually since EE's installer is heavily dependent on the CP Class
                if (method_exists($ACC, 'install'))
                {
                    $ACC->install();
                }

                $this->EE->db->set('class', $class);
                $this->EE->db->set('accessory_version', $ACC->version);
                $this->EE->db->insert('exp_accessories');

                $this->EE->accessories->update_placement($class);

                if ($event == false) $event = 'install';
            }
            else
            {
                // Update Extension
                $version_from = $query->row('accessory_version');
                $accessory_id = $query->row('accessory_id');

                if (version_compare($ACC->version, $version_from, '>'))
                {
                    if (method_exists($ACC, 'update') === true)
                    {
                        $ACC->update($version_from);
                    }

                    $this->EE->db->set('accessory_version', $ACC->version);
                    $this->EE->db->where('accessory_id', $accessory_id);
                    $this->EE->db->update('exp_accessories');

                    if ($event == false) $event = 'update';
                }
            }

            if ($version_to == false) $version_to = $ACC->version;
            if ($addon_label == false) $addon_label = $ACC->name;
            $this->EE->updater->getQueries('end');
        }

        // -----------------------------------------
        // RTE
        // -----------------------------------------
        if (in_array("rte.{$addon}.php", $files) === true && $this->EE->db->table_exists('rte_tools') === true)
        {
            require_once PATH_THIRD.$addon.'/rte.'.$addon.'.php';
            $class = ucfirst($addon).'_rte';
            $RTE = new $class();

            // Extension installed?
            $query = $this->EE->db->select('tool_id')->from('exp_rte_tools')->where('class', $class)->get();

            $this->EE->updater->getQueries('start');

            if ($query->num_rows() == 0)
            {
                if (!$this->EE->addons_installer->install($addon, 'rte_tool', false))
                {
                    $out['addons']['success'] = 'no';
                    exit($this->EE->updater_helper->generate_json($out));
                }

                if ($event == false) $event = 'install';
            }
            else
            {

                if ($event == false) $event = 'update';
            }

            if ($version_to == false) $version_to = $RTE->info['version'];
            if ($addon_label == false) $RTE->info['name'];
            $this->EE->updater->getQueries('end');
        }

        $this->EE->load->remove_package_path(PATH_THIRD.$addon.'/');


        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        $this->EE->updater->stats->data['addon_name'] = $addon_info->name;
        $this->EE->updater->stats->data['addon_label'] = $addon_label;
        $this->EE->updater->stats->data['addon_types'] = $addon_info->types;

        if ($event == 'update') {
            $this->EE->updater->stats->event = 'addon-update';
            $this->EE->updater->stats->data['addon_version_from'] = $version_from;
            $this->EE->updater->stats->data['addon_version_to'] = $version_to;
        } else if ($event == 'install') {
            $this->EE->updater->stats->event = 'addon-install';
            $this->EE->updater->stats->data['addon_version'] = $version_to;
        } else {
            $this->EE->updater->stats->event = 'addon-reupload';
            $this->EE->updater->stats->data['addon_version'] = $version_to;
        }

        $this->EE->updater->sendStats();

        //$this->EE->firephp->log($this->EE->updater->stats);
    }

    // ********************************************************************************* //


} // END CLASS

/* End of file updater_addons.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_addons.php */
