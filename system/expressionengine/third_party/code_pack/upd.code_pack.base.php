<?php if ( ! defined('EXT')) exit('No direct script access allowed');

 /**
 * Solspace - Code Pack
 *
 * @package		Solspace:Code Pack
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2009-2012, Solspace, Inc.
 * @link		http://www.solspace.com/docs/addon/c/Code_Pack/
 * @version		1.2.2
 * @filesource 	./system/expressionengine/third_party/code_pack/
 */

 /**
 * Code Pack - Updater
 *
 * In charge of the install, uninstall, and updating of the module
 *
 * @package 	Solspace:Code pack
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/code_pack/upd.code_pack.php
 */

if ( ! defined('APP_VER')) define('APP_VER', '2.0'); // EE 2.0's Wizard doesn't like CONSTANTs

require_once 'addon_builder/module_builder.php';

class Code_pack_updater_base extends Module_builder_code_pack
{
    var $actions			= array();
    var $hooks				= array();

	// --------------------------------------------------------------------

	/**
	 * Contructor
	 *
	 * @access	public
	 * @return	null
	 */

	function Code_pack_updater( )
    {
    	parent::Module_builder_code_pack('code_pack');

		/** --------------------------------------------
        /**  Module Actions
        /** --------------------------------------------*/

        $this->actions = array();

		/** --------------------------------------------
        /**  Extension Hooks
        /** --------------------------------------------*/

        $this->default_settings = array();

        $default = array(	'class'        => $this->class_name.'_extension',
							'settings'     => '', 								// NEVER!
							'priority'     => 10,
							'version'      => CODE_PACK_VERSION,
							'enabled'      => 'y'
							);

        $this->hooks = array();
    }
    /* END*/

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */

    function install()
    {
        // Already installed, let's not install again.
        if ($this->database_version() !== FALSE)
        {
        	return FALSE;
        }

        /** --------------------------------------------
        /**  Our Default Install
        /** --------------------------------------------*/

        if ($this->default_module_install() == FALSE)
        {
        	return FALSE;
        }

		/** --------------------------------------------
        /**  Module Install
        /** --------------------------------------------*/

        $sql[] = ee()->db->insert_string(
        	'exp_modules', array(
        		'module_name'		=> $this->class_name,
				'module_version'	=> CODE_PACK_VERSION,
				'has_cp_backend'	=> 'y'
			)
		);

        foreach ($sql as $query)
        {
            ee()->db->query($query);
        }

        return TRUE;
    }
	/* END install() */

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller

	 * @access	public
	 * @return	bool
	 */

    function uninstall()
    {
        // Cannot uninstall what does not exist, right?
        if ($this->database_version() === FALSE)
        {
        	return FALSE;
        }

		/** --------------------------------------------
        /**  Default Module Uninstall
        /** --------------------------------------------*/

        if ($this->default_module_uninstall() == FALSE)
        {
        	return FALSE;
        }

        return TRUE;
    }

    /* END */


	// --------------------------------------------------------------------

	/**
	 * Module Updater

	 * @access	public
	 * @return	bool
	 */

    function update()
    {
    	/** --------------------------------------------
        /**  Default Module Update
        /** --------------------------------------------*/

    	$this->default_module_update();

    	$this->actions();

    	/** --------------------------------------------
        /**  Database Change
        /**  - Added: 1.0.0.d2
        /** --------------------------------------------*/

        if ($this->version_compare($this->database_version(), '<', '1.0.0.d2'))
        {

        }

        /** --------------------------------------------
        /**  Version Number Update - LAST!
        /** --------------------------------------------*/

    	ee()->db->query(ee()->db->update_string(	'exp_modules',
    									array('module_version'	=> CODE_PACK_VERSION),
    									array('module_name'		=> $this->class_name)));


    	return TRUE;
    }
    /* END update() */
}
/* END Class */
?>