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
class Updater_misc
{
    public function __construct()
    {
        // Creat EE Instance
        $this->EE =& get_instance();
    }

    // ********************************************************************************* //

    public function detectForumFromPath($path)
    {
        // Integrity Check
        if (file_exists($path.'forum/mod.forum.php') === false) {
            return false;
        }

        $version = '';
        $file = file_get_contents($path.'forum/upd.forum.php');

        preg_match('/\\$version.*=.*\'(.*?)\';/', $file, $matches);
        $version = end($matches);

        // Installed?
        $query = $this->EE->db->select('module_id')->from('exp_modules')->where('module_name', 'Forum')->get();
        $updater_action = ($query->num_rows() == 0) ? 'install' : 'update';

        $full_root = $path;

        $out = array(
            'version' => $version,
            'root_location' => $full_root,
            'updater_action' => $updater_action,
        );

        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        $this->EE->updater->stats->event = 'ee_forum-zip-processed';
        $this->EE->updater->stats->data['forum_version'] = $version;
        $this->EE->updater->sendStats();

        return $out;
    }

    // ********************************************************************************* //

    public function detectMsmFromPath($path)
    {
        $found = false;
        $ds = DIRECTORY_SEPARATOR;
        $detect_path = "system{$ds}expressionengine{$ds}libraries{$ds}Sites.php";

        $dir_contents =& $this->EE->updater->temp_dir_contents;

        foreach ($dir_contents as $file) {
            if (strpos($file, $detect_path) !== false) {
                $found = $file;
                break;
            }
        }

        if ($found === false) {
            return false;
        }

        $root = str_replace($detect_path, '', $found);
        $full_root = $path . $root;

        $version = '';
        $file = file_get_contents($path.$root.'system/expressionengine/controllers/cp/sites.php');

        preg_match('/\\$version.*=.*\'(.*?)\';/', $file, $matches);
        $version = end($matches);
        preg_match('/\\$build_number.*=.*\'(.*?)\';/', $file, $matches);
        $build = end($matches);

        $version_full = "v{$version} (Build: {$build})";

        if ($this->EE->config->item('multiple_sites_enabled') == 'y') {
            $updater_action = 'update';
        } else {
            $updater_action = 'install';
        }

        $out = array(
            'version' => $version,
            'build' => $build,
            'full' => $version_full,
            'root_location' => $full_root,
            'updater_action' => $updater_action,
        );

        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        $this->EE->updater->stats->event = 'ee_msm-zip-processed';
        $this->EE->updater->stats->data['msm_version'] = $version;
        $this->EE->updater->stats->data['msm_build'] = $build;
        $this->EE->updater->sendStats();

        return $out;
    }

    // ********************************************************************************* //

    public function detectForumThemeFromPath($path)
    {
        $dir_contents =& $this->EE->updater->temp_dir_contents;

        $ds = DIRECTORY_SEPARATOR;
        $dirs = array();
        $theme_path = false;
        $dirs[] = array("path" => "themes{$ds}forum_themes", 'length' => strlen("themes{$ds}forum_themes"));

        foreach ($dir_contents as $file) {
            if (strpos($file, '__MACOSX') !== false) continue;
            if (strpos($file, '.svn') !== false) continue;

            foreach ($dirs as $sdir) {
                if (substr($file, -$sdir['length']) === $sdir['path']) {
                    $theme_path = ltrim($file, $ds);
                    continue 2;
                }
            }
        }

        if ($theme_path === false) {
            return false;
        }

        // Add the last slash
        $theme_path = $path.$theme_path.$ds;

        $themes = array();
        $dirs = directory_map($theme_path, 1);

        // EE Theme Path
        $ee_theme_path = $this->EE->updater_helper->getThemePath();

        foreach ($dirs as $dir) {

            // Make sure it's a dir
            if (is_dir($theme_path.$dir.$ds) === false) {
                continue;
            }

            $theme = new stdClass();
            $theme->name = $dir;
            $theme->label = ucfirst($dir);
            $theme->version = '(N/A)';
            $theme->root_location = $theme_path.$dir.$ds;
            $theme->updater_action = 'install';

            if (file_exists($ee_theme_path.'forum_themes'.$ds.$dir)) {
                $theme->updater_action = 'update';
            }

            $themes[$dir] = $theme;
        }

        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        foreach ($themes as $theme) {
            $this->EE->updater->stats->event = 'forum_theme-zip-processed';
            $this->EE->updater->stats->data['name'] = $theme->name;
            $this->EE->updater->stats->data['label'] = $theme->label;
            $this->EE->updater->stats->data['version'] = $theme->version;
            $this->EE->updater->sendStats();
        }

        return $themes;
    }


    // ********************************************************************************* //

    public function detectCpThemeFromPath($path)
    {
        $dir_contents =& $this->EE->updater->temp_dir_contents;

        $ds = DIRECTORY_SEPARATOR;
        $dirs = array();
        $theme_path = false;
        $theme_img_path = false;
        $dirs[] = array("path" => "themes{$ds}cp_themes", 'length' => strlen("themes{$ds}cp_themes"));
        $dirs_images[] = array("path" => "themes{$ds}cp_global_images", 'length' => strlen("themes{$ds}cp_global_images"));

        foreach ($dir_contents as $file) {
            if (strpos($file, '__MACOSX') !== false) continue;
            if (strpos($file, '.svn') !== false) continue;

            foreach ($dirs as $sdir) {
                if (substr($file, -$sdir['length']) === $sdir['path']) {
                    $theme_path = ltrim($file, $ds);
                    continue 2;
                }
            }

            foreach ($dirs_images as $sdir) {
                if (substr($file, -$sdir['length']) === $sdir['path']) {
                    $theme_img_path = ltrim($file, $ds);
                    continue 2;
                }
            }
        }

        if ($theme_path === false) {
            return false;
        }

        // Add the last slash
        $theme_path = $path.$theme_path.$ds;

        // EE Theme Path
        $ee_theme_path = $this->EE->updater_helper->getThemePath();

        $themes = array();
        $dirs = directory_map($theme_path, 1);

        foreach ($dirs as $dir) {

            // Make sure it's a dir
            if (is_dir($theme_path.$dir.$ds) === false) {
                continue;
            }

            $theme = new stdClass();
            $theme->name = $dir;
            $theme->label = ucfirst($dir);
            $theme->version = '(N/A)';
            $theme->root_location = $theme_path.$dir.$ds;
            $theme->updater_action = 'install';
            $theme->cp_global_images = false;

            if ($theme_img_path !== false) {
                $theme->cp_global_images = $path.$theme_img_path.$ds;
            }

            if (file_exists($ee_theme_path.'cp_themes'.$ds.$dir)) {
                $theme->updater_action = 'update';
            }

            $themes[$dir] = $theme;
        }

        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        foreach ($themes as $theme) {
            $this->EE->updater->stats->event = 'cp_theme-zip-processed';
            $this->EE->updater->stats->data['name'] = $theme->name;
            $this->EE->updater->stats->data['label'] = $theme->label;
            $this->EE->updater->stats->data['version'] = $theme->version;
            $this->EE->updater->sendStats();
        }

        return $themes;
    }


    // ********************************************************************************* //

    public function moveMsmFiles($obj)
    {
        $root_location = $obj->info->root_location;
        $this->EE->updater_transfer->upload('system', $root_location.'system/expressionengine/', 'expressionengine/');
        //$this->EE->updater_transfer->upload('system', $root_location.'system/expressionengine/controllers/cp/', 'expressionengine/controllers/cp/');
        //$this->EE->updater_transfer->upload('system', $root_location.'system/expressionengine/language/english/', 'expressionengine/language/english/');
        //$this->EE->updater_transfer->upload('system', $root_location.'system/expressionengine/libraries/', 'expressionengine/libraries/');

    }

    // ********************************************************************************* //

    public function moveForumFiles($obj)
    {
        $root_location = $obj->info->root_location;

        $this->EE->updater_transfer->mkdir('system', 'expressionengine/modules/forum/');
        $this->EE->updater_transfer->upload('system', $root_location.'forum/', 'expressionengine/modules/forum/');

        $this->EE->updater_transfer->upload('system', $root_location.'forum_cp_lang.php', 'expressionengine/language/english/forum_cp_lang.php');
        $this->EE->updater_transfer->upload('system', $root_location.'forum_lang.php', 'expressionengine/language/english/forum_lang.php');

        $this->EE->updater_transfer->mkdir('themes', 'forum_themes/');
        $this->EE->updater_transfer->upload('themes', $root_location.'forum_themes/', 'forum_themes/');
    }

    // ********************************************************************************* //

    public function moveCpThemeFiles($obj)
    {
        // The root location includes the "theme name"!
        $root_location = $obj->info->root_location;

        $this->EE->updater_transfer->mkdir('themes', 'cp_themes/'.$obj->info->name.'/');
        $this->EE->updater_transfer->upload('themes', $root_location, 'cp_themes/'.$obj->info->name.'/');

        if (isset($obj->info->cp_global_images) === true && $obj->info->cp_global_images != false) {
            $this->EE->updater_transfer->upload('themes', $obj->info->cp_global_images, 'cp_global_images/'.$obj->info->name.'/');
        }
    }

    // ********************************************************************************* //

    public function moveForumThemeFiles($obj)
    {
        // The root location includes the "theme name"!
        $root_location = $obj->info->root_location;

        $this->EE->updater_transfer->mkdir('themes', 'forum_themes/'.$obj->info->name.'/');
        $this->EE->updater_transfer->upload('themes', $root_location, 'forum_themes/'.$obj->info->name.'/');
    }

    // ********************************************************************************* //

    public function installUpdateForum($obj)
    {
        $this->EE->load->library('addons/addons_installer');

        $version_from = '';
        $version_to = $obj->info->version;

        // Add the package path
        $this->EE->load->add_package_path(APPPATH.'modules/forum/');

        $files = directory_map(APPPATH.'modules/forum/');

        if ($files == false)
        {
            // Sometimes we are too fast.. Last give it a break
            sleep(3);
            $files = directory_map(APPPATH.'modules/forum/');

            if ($files == false)
            {
                // Still not? Lets give it 3 more seconds!
                sleep(3);
                $files = directory_map(APPPATH.'modules/forum/');
            }
        }

        // -----------------------------------------
        // Module?
        // -----------------------------------------
        if (in_array("upd.forum.php", $files) === true)
        {
            // Module Installed?
            $query = $this->EE->db->select('module_id, module_version')->from('exp_modules')->where('module_name', ucfirst('forum'))->get();

            require_once APPPATH.'modules/forum/upd.forum.php';
            $class = ucfirst('forum').'_upd';
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

            $this->EE->lang->loadfile('forum');
            $addon_label = $this->EE->lang->line('forum_module_name');

            $this->EE->updater->getQueries('end');
        }


        $this->EE->load->remove_package_path(APPPATH.'modules/forum/');

        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        $this->EE->updater->stats->data['addon_name'] = 'forum';
        $this->EE->updater->stats->data['addon_label'] = $addon_label;
        $this->EE->updater->stats->data['addon_types'] = array('module');

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
    }

    // ********************************************************************************* //


} // END CLASS

/* End of file updater_addons.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_addons.php */
