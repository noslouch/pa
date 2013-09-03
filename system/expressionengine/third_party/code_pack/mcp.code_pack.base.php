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
 * Code Pack - Control Panel
 *
 * The Control Panel master class that handles all of the CP Requests and Displaying
 *
 * @package 	Solspace:Code pack
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/code_pack/mcp.code_pack.php
 */

require_once 'addon_builder/module_builder.php';

class Code_pack_cp_base extends Module_builder_code_pack
{
    var $theme_path	= '';
    var $theme_url	= '';

    // --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	bool		Enable calling of methods based on URI string
	 * @return	string
	 */

	function __construct( $switch = TRUE )
    {
        parent::Module_builder_code_pack('code_pack');

         if ((bool) $switch === FALSE) return; // Install or Uninstall Request

        /** --------------------------------------------
        /**  Automatically Loaded Class Vars
        /** --------------------------------------------*/

        //$this->base = BASE.'&C=modules&M=code_pack';

        /** --------------------------------------------
        /**  Automatically Loaded Class Vars
        /** --------------------------------------------*/

		$this->theme_path	= $this->sc->addon_theme_path;
        $this->theme_url	= $this->sc->addon_theme_url;

		/** --------------------------------------------
        /**	Set EE1 nav
        /** --------------------------------------------*/

        $menu	= array(
        	'module_home'	=> array(
        		'name'	=> 'home',
        		'link'  => $this->base,
        		'title' => ee()->lang->line('code_packs')
			),
			'module_documentation'	=> array(
        		'name'	=> 'documentation',
				'link'  => CODE_PACK_DOCS_URL,
				'title' => ee()->lang->line('documentation') . ((APP_VER < 2.0) ? ' (' . CODE_PACK_VERSION . ')' : '')
			),
		);

		$this->cached_vars['lang_module_version'] 	= ee()->lang->line('code_pack_module_version');
		$this->cached_vars['module_version'] 		= CODE_PACK_VERSION;
	    $this->cached_vars['module_menu_highlight'] = 'module_home';
	    $this->cached_vars['module_menu'] 			= $menu;

        /** --------------------------------------------
        /**  Sites
        /** --------------------------------------------*/

        $this->cached_vars['sites']	= array();

        foreach($this->data->get_sites() as $site_id => $site_label)
        {
        	$this->cached_vars['sites'][$site_id] = $site_label;
        }

		/** -------------------------------------
		/**  Module Installed and What Version?
		/** -------------------------------------*/

		if ( $this->database_version() == FALSE )
		{
			return;
		}
		elseif( $this->version_compare($this->database_version(), '<', CODE_PACK_VERSION) )
		{
			if ( APP_VER < 2.0 )
			{
				if ( $this->code_pack_module_update() === FALSE )
				{
					return;
				}
			}
			else
			{
				// For EE 2.x, we need to redirect the request to Update Routine
				$_GET['method'] = 'code_pack_module_update';
			}
		}

        /** -------------------------------------
		/**  Request and View Builder
		/** -------------------------------------*/

        if (APP_VER < 2.0 AND $switch !== FALSE)
        {
        	if (ee()->input->get('method') === FALSE)
        	{
        		$this->index();
        	}
        	elseif( ! method_exists($this, ee()->input->get('method')))
        	{
        		$this->add_crumb(ee()->lang->line('invalid_request'));
        		$this->cached_vars['error_message'] = ee()->lang->line('invalid_request');

        		return $this->ee_cp_view('error_page.html');
        	}
        	else
        	{
        		$this->{ee()->input->get('method')}();
        	}
        }
    }
    /* END */

	// --------------------------------------------------------------------

	/**
	 * Code Pack

	 * @access	public
	 * @param	string
	 * @return	string
	 */

	function code_pack($message='')
    {
		/** -------------------------------------
		/**	Get code pack
		/** -------------------------------------*/

		$code_packs	= array();

		if (ee()->extensions->active_hook('code_pack_list') === TRUE)
		{
			$code_packs	= ee()->extensions->universal_call( 'code_pack_list', $code_packs );
		}

		/** -------------------------------------
		/**	Get meta data for code pack and verify
		/** -------------------------------------*/

		if ( ee()->input->get_post('code_pack_name') === FALSE OR
			 ee()->input->get_post('code_pack_name') == '' )
		{
			$this->cached_vars['error'] = ee()->lang->line('no_code_pack_specified');
		}
		elseif ( count( $code_packs ) == 0 )
		{
			$this->cached_vars['error'] = ee()->lang->line('no_code_packs');
		}
		else
		{
			$this->cached_vars['code_pack']	= array();

			foreach ( $code_packs as $val )
			{
				if ( isset( $val['code_pack_name'] ) === TRUE AND
					 $val['code_pack_name'] == ee()->input->get_post('code_pack_name') )
				{
					$this->cached_vars['code_pack']					= $val;
					$this->cached_vars['code_pack_name']			= $val['code_pack_name'];
					$this->cached_vars['code_pack_label']			= $val['code_pack_label'];
					$this->cached_vars['code_pack_description']		= $val['code_pack_description'];
					$this->cached_vars['code_pack_theme_folder']	= $val['code_pack_theme_folder'];
				}
			}

			/** -------------------------------------
			/**	Validate theme
			/** -------------------------------------*/

			$themes	= $this->fetch_themes(
				$this->theme_path . rtrim( $this->cached_vars['code_pack_theme_folder'], '/' ) . '/'
			);

			if ( count( $this->cached_vars['code_pack'] ) == 0 )
			{
				$this->cached_vars['error'] = ee()->lang->line('code_pack_not_found');
			}
			elseif ( count( $themes ) == 0 )
			{
				$this->cached_vars['error'] = str_replace(
					'%code_pack_name%',
					$this->cached_vars['code_pack_theme_folder'],
					ee()->lang->line('missing_theme')
				);
			}
			else
			{
				/** -------------------------------------
				/**	Get themes for this code pack
				/** -------------------------------------*/

				$themes_path	= rtrim( $this->theme_path, '/' ) . '/' .
								  rtrim( $this->cached_vars['code_pack_theme_folder'], '/' ) . '/';

				$themes	= $this->fetch_themes( $themes_path );

				/** -------------------------------------
				/**	Get code packs from themes folder
				/** -------------------------------------*/

				$this->cached_vars['code_packs']	= array();

				foreach ( $themes as $folder )
				{
					$this->cached_vars['code_packs'][$folder]['name']	= ucwords( str_replace( '_', ' ', $folder ) );

					$this->cached_vars['code_packs'][$folder]['description'
						]	= $this->data->get_code_pack_full_description( $themes_path.$folder );

					$this->cached_vars['code_packs'][$folder]['img_url']	= $this->data->get_code_pack_image(
						$themes_path.$folder,
						$this->theme_url .
							rtrim( $this->cached_vars['code_pack_theme_folder'], '/' ) . '/' . $folder
					);
				}
			}
		}

		// --------------------------------------------
        //  Default Prefix
        // --------------------------------------------

        $this->cached_vars['prefix_default'] = trim(str_replace('code_pack', '', ee()->input->get_post('code_pack_name')), '_').'_';

		/** -------------------------------------
		/**  Message
		/** -------------------------------------*/

        if ($message == '' AND isset($_GET['msg']))
        {
        	$message = ee()->lang->line($_GET['msg']);
        }

        $this->cached_vars['message'] = $message;

		/** -------------------------------------
		/**  Title and Crumbs
		/** -------------------------------------*/

		$this->add_crumb( $this->cached_vars['code_pack_label'] );
		$this->build_crumbs();
		$this->cached_vars['module_menu_highlight'] = 'module_home';

		/** --------------------------------------------
        /**  Load Homepage
        /** --------------------------------------------*/

		return $this->ee_cp_view('code_pack.html');
	}
	/* End code pack */

	// --------------------------------------------------------------------

	/**
	 * Code pack install
	 *
	 * This method installs sample data into EE sites.
	 *
	 * @access	public
	 * @param	message
	 * @return	string
	 */

    function code_pack_install( $message = '' )
    {
    	$this->cached_vars['errors']	= array();
		$this->cached_vars['success']	= array();

		/** -------------------------------------
		/**	Auto load variables. These will be validated in actions->code_pack_install()
		/** -------------------------------------*/

		$variables	= array();

		foreach ( array( 'code_pack_name', 'code_pack_label', 'code_pack_theme_folder', 'code_pack_theme', 'prefix' ) as $val )
		{
			if ( ! empty( $_POST[ $val ] ) )
			{
				$variables[ $val ]	= ee()->security->xss_clean( $_POST[ $val ] );
			}
		}

		/** -------------------------------------
		/**	Theme
		/** -------------------------------------*/

		if ( ! empty( $variables['code_pack_name'] ) AND ! empty( $variables['code_pack_theme'] ) )
		{
			$variables['code_pack_name']	= rtrim( $variables['code_pack_name'], '/' );
			$variables['code_pack_theme']	= rtrim( $variables['code_pack_theme'], '/' );
			$variables['theme_path']		= $this->theme_path .
											   	$variables['code_pack_theme_folder'] . '/' .
											   	$variables['code_pack_theme'];
			$variables['theme_url']			= $this->theme_url .
												$variables['code_pack_theme_folder'] . '/' .
												$variables['code_pack_theme'];
		}

		/** -------------------------------------
		/**	Execute
		/** -------------------------------------*/

		$this->actions();

		$result	= $this->actions->code_pack_install( $variables );

		if ( count( $result ) == 0 )
		{
			exit( 'massive fail' );
		}

		/** -------------------------------------
		/**	Validate
		/** -------------------------------------*/

		$this->cached_vars	= array_merge( $this->cached_vars, $result );

		if ( empty( $this->cached_vars['errors'] ) AND empty( $this->cached_vars['success'] ) )
		{
			exit( 'another massive fail' );
		}

		/** -------------------------------------
		/**	Prep message
		/** -------------------------------------*/

		$this->_prep_message( $message );

		/** -------------------------------------
		/**	Title and Crumbs
		/** -------------------------------------*/

		$this->cached_vars['code_pack_label']	= ( ! empty( $variables['code_pack_label'] ) ) ?
													$variables['code_pack_label'] :
													$variables['code_pack_name'];

		$this->add_crumb( $this->cached_vars['code_pack_label'] );
		$this->cached_vars['module_menu_highlight'] = 'module_home';

		/** -------------------------------------
		/**	Load Page
		/** -------------------------------------*/

		return $this->ee_cp_view('code_pack_install.html');
    }

    /**	End code pack install */

	// --------------------------------------------------------------------

	/**
	 * Module's Main Homepage
	 *
	 * @access	public
	 * @param	string
	 * @return	null
	 */

	function index($message='')
    {
		/** -------------------------------------
		/**	Get code packs
		/** -------------------------------------*/

		$this->cached_vars['code_packs']	= array();

		if ( ee()->extensions->active_hook('code_pack_list') === TRUE )
		{
			$this->cached_vars['code_packs']	= ee()->extensions->universal_call( 'code_pack_list', $this->cached_vars['code_packs'] );
		}

		ksort( $this->cached_vars['code_packs'] );

		/** -------------------------------------
		/**	Get meta data for code packs and verify
		/** -------------------------------------*/

		$themes	= $this->fetch_themes( $this->theme_path );

		foreach ( $this->cached_vars['code_packs'] as $key => $val )
		{
			/** -------------------------------------
			/**	Is there a theme for this code pack?
			/** -------------------------------------*/

			if ( isset( $val['code_pack_theme_folder'] ) === FALSE OR in_array( $val['code_pack_theme_folder'], $themes ) === FALSE )
			{
				unset( $this->cached_vars['code_packs'][ $key ] );
			}
		}

		/** -------------------------------------
		/**  Message
		/** -------------------------------------*/

        if ( $message == '' AND isset( $_GET['msg'] ) )
        {
        	$message = ee()->lang->line( $_GET['msg'] );
        }

        $this->cached_vars['message'] = $message;

		/** -------------------------------------
		/**  Title and Crumbs
		/** -------------------------------------*/

		$this->add_crumb(ee()->lang->line('code_packs'));
		$this->build_crumbs();

		/** --------------------------------------------
        /**  Load Homepage
        /** --------------------------------------------*/

		return $this->ee_cp_view('index.html');
	}
	/* END home() */

	// --------------------------------------------------------------------

	/**
	 * Prep message

	 * @access	private
	 * @param	message
	 * @return	boolean
	 */

	function _prep_message( $message = '' )
	{
        if ( $message == '' AND isset( $_GET['msg'] ) )
        {
        	$message = ee()->lang->line( $_GET['msg'] );
        }

		$this->cached_vars['message']	= $message;

		return TRUE;
	}

	/*	End prep message */

	// --------------------------------------------------------------------

	/**
	 * Module Installation
	 *
	 * Due to the nature of the 1.x branch of ExpressionEngine, this function is always required.
	 * However, because of the large size of the module the actual code for installing, uninstalling,
	 * and upgrading is located in a separate file to make coding easier
	 *
	 * @access	public
	 * @return	bool
	 */

    function code_pack_module_install()
    {
    	require_once 'upd.code_pack.base.php';

    	$U = new Code_pack_updater_base();

    	return $U->install();
    }
	/* END code_pack_module_install() */

	// --------------------------------------------------------------------

	/**
	 * Module Uninstallation
	 *
	 * Due to the nature of the 1.x branch of ExpressionEngine, this function is always required.
	 * However, because of the large size of the module the actual code for installing, uninstalling,
	 * and upgrading is located in a separate file to make coding easier
	 *
	 * @access	public
	 * @return	bool
	 */

    function code_pack_module_deinstall()
    {
    	require_once 'upd.code_pack.base.php';

    	$U = new Code_pack_updater_base();

    	return $U->uninstall();
    }
    /* END code_pack_module_deinstall() */


	// --------------------------------------------------------------------

	/**
	 * Module Upgrading
	 *
	 * This function is not required by the 1.x branch of ExpressionEngine by default.  However,
	 * as the install and deinstall ones are, we are just going to keep the habit and include it
	 * anyhow.
	 *		- Originally, the $current variable was going to be passed via parameter, but as there might
	 *		  be a further use for such a variable throughout the module at a later date we made it
	 *		  a class variable.
	 *
	 *
	 * @access	public
	 * @return	bool
	 */

    function code_pack_module_update()
    {
    	if ( ! isset($_POST['run_update']) OR $_POST['run_update'] != 'y')
    	{
    		$this->add_crumb(ee()->lang->line('update_code_pack'));
    		$this->build_crumbs();
			$this->cached_vars['form_url'] = $this->base.'&msg=update_successful';
			return $this->ee_cp_view('update_module.html');
		}

    	require_once $this->addon_path.'upd.code_pack.base.php';

    	$U = new Code_pack_updater_base();

    	if ($U->update() !== TRUE)
    	{
    		return $this->index(ee()->lang->line('update_failure'));
    	}
    	else
    	{
    		return $this->index(ee()->lang->line('update_successful'));
    	}
    }
    /* END code_pack_module_update() */

	// --------------------------------------------------------------------


	function get_theme_path()
	{

	}
}
// END CLASS Code_pack