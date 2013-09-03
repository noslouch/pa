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
class Updater_ee
{
    public function __construct()
    {
        // Creat EE Instance
        $this->EE =& get_instance();
    }

    // ********************************************************************************* //

    public function detectFromPath($path)
    {
        // Integrity Check
        if (file_exists($path.'system/expressionengine/config/config.php') === false) {
            return false;
        }

        // Check again
        if (file_exists($path.'system/expressionengine/libraries/Core.php') === false) {
            return false;
        }

        $version = '';
        $file = file_get_contents($path.'system/expressionengine/libraries/Core.php');
        $file_install = file_get_contents($path.'system/installer/controllers/wizard.php');

        preg_match('/APP_BUILD.*\'(.*?)\'/', $file, $matches);
        $build = end($matches);
        preg_match('/\$version.*\'(.*?)\'/', $file_install, $matches);
        $version = end($matches);

        $version_full = "v{$version} (Build: {$build})";

        $root_location = $path;

        $out = array(
            'version' => $version,
            'build' => $build,
            'full' => $version_full,
            'root_location' => $root_location
        );

        // -----------------------------------------
        // Send Stats
        // -----------------------------------------
        $this->EE->updater->stats->event = 'ee-zip-processed';
        $this->EE->updater->stats->data['ee_version'] = $version;
        $this->EE->updater->stats->data['ee_build'] = $build;
        $this->EE->updater->sendStats();

        return $out;
    }

    // ********************************************************************************* //

    public function copyInstaller($action_obj)
    {
        $out = array('success' => 'no', 'body'=>'');
        $root = $action_obj->info->root_location;

        try
        {
            $this->EE->updater_transfer->init();
            $this->EE->updater_transfer->mkdir('system', 'installer');
            $this->EE->updater_transfer->upload('system', PATH_THIRD.'updater/libraries/installer', 'installer', 'dir', TRUE);

            // Special Template Library.. (Since: EE 2.7.0)
            if (file_exists($root.'system/installer/libraries/Template.php')) {
                $this->EE->updater_transfer->upload('system', $root.'system/installer/libraries/Template.php', 'installer/libraries/Template.php', 'file', TRUE);
            }

            //$this->EE->updater_transfer->upload('system', $root.'system/installer/updates', 'installer/updates', 'dir', TRUE);
            //$this->EE->updater_transfer->upload('system', $root.'system/installer/core/Installer_Config.php', 'installer/core/Installer_Config.php', 'file', TRUE);


            //$this->EE->updater_transfer->upload('system', $root.'system/expressioneinge/libraries/Smartforge.php', 'installer/libraries/EE_Smartforge.php', 'file', TRUE);
            //$this->EE->updater_transfer->upload('system', $root.'system/expressioneinge/libraries/Localize.php', 'installer/libraries/EE_Localize.php', 'file', TRUE);

            $this->EE->updater_transfer->mkdir('system', 'installer/language/english/');
            $this->EE->updater_transfer->upload('system', PATH_THIRD.'updater/language/english/updater_lang.php', 'installer/language/english/updater_lang.php', 'file', TRUE);
        }
        catch (Exception $e)
        {
            $out['body'] = $e->getMessage();
            exit($this->EE->updater_helper->generateJson($out));
        }

        // -----------------------------------------
        // Scan Available Updates
        // -----------------------------------------
        $out['updates'] = array();
        $current = $this->EE->config->item('app_version');

        $files = directory_map($root.'system/installer/updates/', 1);
        sort($files);

        foreach ($files as $key => $filename)
        {
            if ($filename == '.' || $filename == '..') continue;
            if (substr($filename, 0, 3) != 'ud_') continue;
            $ver = str_replace(array('ud_', '.php'), '', $filename);
            if ($ver <= $current) continue;

            $out['updates'][] = array('label' => 'Version '.substr($ver,0,1).'.'.substr($ver,1,1).'.'.substr($ver,2,1), 'version' => $ver);
        }

        $out['ee_info']['version_from'] = APP_VER;
        $out['ee_info']['build_from'] = APP_BUILD;
        $out['ee_info']['build_to'] = APP_BUILD;
        $out['ee_info']['update_steps'] = count($out['updates']);

        if (empty($out['updates']) === TRUE)
        {
            $out['ee_info']['version_to'] = APP_VER;
        }
        else
        {
            $end = end($out['updates']);
            $out['ee_info']['version_to'] = $end['version'];
        }


        // Give the system time to come back?
        sleep(2);

        $out['success'] = 'yes';
        $out['server'] = $this->server;
        exit($this->EE->updater_helper->generateJson($out));
    }

    // ********************************************************************************* //


} // END CLASS

/* End of file updater_addons.php  */
/* Location: ./system/expressionengine/third_party/updater/libraries/updater_addons.php */
