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
 * Code Pack - Actions
 *
 * Handles All Form Submissions and Action Requests Used on both User and CP areas of EE
 *
 * @package 	Solspace:Code pack
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/code_pack/act.code_pack.php
 */

require_once 'addon_builder/addon_builder.php';

class Code_pack_actions extends Addon_builder_code_pack
{
	// --------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	null
	 */

	function Code_pack_actions()
    {
    	parent::Addon_builder_code_pack('code_pack');

    	/** -------------------------------------
		/**  Module Installed and What Version?
		/** -------------------------------------*/

		if ($this->database_version() == FALSE OR
			$this->version_compare($this->database_version(), '<', CODE_PACK_VERSION))
		{
			return;
		}
	}
	/* End constructor */

	// --------------------------------------------------------------------

	/**
	 * Code pack install
	 *
	 * This method installs sample data into EE sites.
	 * It has the facility to read arrays from a data.php file and
	 * create content in a site's database. For this purpose,
	 * $db is a reserved variable.
	 *
	 * @access	public
	 * @param	variables['code_pack_name']
	 * @param	variables['code_pack_theme']
	 * @param	variables['prefix']
	 * @param	variables['theme_path']
	 * @param	variables['theme_url']
	 * @return	string
	 */

    function code_pack_install( $variables = array() )
    {
    	$this->cached_vars['errors']			= array();
		$this->cached_vars['success']			= array();
		$this->cached_vars['global_vars']		= array();
		$this->cached_vars['template_count']	= 0;

		// -------------------------------------
		//	Set reserved names
		// -------------------------------------

		$this->cached_vars['reserved_names']	= array();

		if (ee()->config->item("use_category_name") == 'y' AND
			ee()->config->item("reserved_category_word") != '')
		{
			$this->cached_vars['reserved_names'][] = ee()->config->item("reserved_category_word");
		}

		if (ee()->config->item("forum_is_installed") == 'y' AND
			ee()->config->item("forum_trigger") != '')
		{
			$this->cached_vars['reserved_names'][] = ee()->config->item("forum_trigger");
		}

		if (ee()->config->item("profile_trigger") != '')
		{
			$this->cached_vars['reserved_names'][] = ee()->config->item("profile_trigger");
		}

		/** -------------------------------------
		/**	Check for code pack name
		/** -------------------------------------*/

		if ( empty( $variables['code_pack_name'] ) )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('missing_code_pack'),
				'description'	=> ee()->lang->line('missing_code_pack_exp')
			);

			$this->cached_vars['errors'][]	= $arr;
		}

		/** -------------------------------------
		/**	Check for code pack theme
		/** -------------------------------------*/

		if ( empty( $variables['code_pack_theme'] ) )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('missing_code_pack_theme'),
				'description'	=> str_replace(
					'%code_pack_name%',
					$variables['code_pack_name'],
					ee()->lang->line('missing_code_pack_theme_exp')
				)
			);

			$this->cached_vars['errors'][]	= $arr;
		}

		/** -------------------------------------
		/**	Check for code pack theme path
		/** -------------------------------------*/

		if ( empty( $variables['theme_path'] ) )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('missing_code_pack_theme'),
				'description'	=> str_replace(
					'%code_pack_name%',
					$variables['code_pack_name'],
					ee()->lang->line('missing_code_pack_theme_exp')
				)
			);

			$this->cached_vars['errors'][]	= $arr;
		}

		/** -------------------------------------
		/**	Check for code pack theme url
		/** -------------------------------------*/

		if ( empty( $variables['theme_url'] ) )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('missing_code_pack_theme'),
				'description'	=> str_replace(
					'%code_pack_name%',
					$variables['code_pack_name'],
					ee()->lang->line('missing_code_pack_theme_exp')
				)
			);

			$this->cached_vars['errors'][]	= $arr;
		}

		/** -------------------------------------
		/**	Check for template prefix
		/** -------------------------------------*/

		if ( empty( $variables['prefix'] ) )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('missing_prefix'),
				'description'	=> ee()->lang->line('missing_prefix_exp')
			);

			$this->cached_vars['errors'][]	= $arr;
		}
        elseif ( preg_match( "/[^a-zA-Z0-9\_\-\.]/", $variables['prefix'] ) )
        {
			$arr	= array(
				'label'			=> ee()->lang->line('invalid_prefix'),
				'description'	=> ee()->lang->line('invalid_prefix_exp')
			);

			$this->cached_vars['errors'][]	= $arr;
        }

		/** --------------------------------------------
        /**	Prepare vars for later
        /** --------------------------------------------*/

		$this->cached_vars['conflicting_groups']		= array();
		$this->cached_vars['conflicting_global_vars']	= array();

		/** --------------------------------------------
        /**	Do we have errors?
        /** --------------------------------------------*/

        if ( ! empty( $this->cached_vars['errors'] ) )
        {
        	return $this->cached_vars;
        }

		/** -------------------------------------
		/**	Get list of template groups
		/** -------------------------------------*/

		$this->cached_vars['template_groups']	= array();

		$this->cached_vars['template_groups']	= $this->fetch_themes( rtrim( $variables['theme_path'], '/' ) . '/html/' );

		$this->cached_vars['template_groups_prefix']	= array();

		/** -------------------------------------
		/**	Prepare arrays
		/** -------------------------------------*/

		if ( count( $this->cached_vars['template_groups'] ) == 0 )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('missing_theme_html'),
				'description'	=> str_replace(
					'%code_pack_name%',
					$variables['code_pack_theme_folder'] . '/' .
						$variables['code_pack_theme'] ,
					ee()->lang->line('missing_theme_html_exp')
				)
			);

			$this->cached_vars['errors'][]	= $arr;
		}

		foreach ( $this->cached_vars['template_groups'] as $key => $val )
		{
			$this->cached_vars['template_groups_prefix'][]	= $variables['prefix'].$val;
		}

		/** -------------------------------------
		/**	Check for template group name conflicts
		/** -------------------------------------*/

		if ( count( $this->cached_vars['template_groups'] ) > 0 AND ! empty( $variables['prefix'] ) )
		{
			$sql	= "SELECT 	group_name
					   FROM 	exp_template_groups
					   WHERE 	site_id = ".ee()->db->escape_str( ee()->config->item('site_id') )."
					   AND 		group_name
					   IN 		('".implode( "','", $this->cached_vars['template_groups_prefix'] )."')";

			$query	= ee()->db->query( $sql );

			if ( $query->num_rows() > 0 )
			{
				$this->cached_vars['conflicting_groups']	= array();

				foreach ( $query->result_array() as $row )
				{
					$this->cached_vars['conflicting_groups'][]	= $row['group_name'];
				}

				$arr	= array(
					'label'			=> ee()->lang->line('conflicting_group_names'),
					'description'	=> ee()->lang->line('conflicting_group_names_exp')
				);

				$this->cached_vars['errors'][]	= $arr;
			}
		}

		/** -------------------------------------
		/**	Check for global variable conflicts
		/** -------------------------------------*/

		$global_vars	= array();

		$global_vars	= array(
			$variables['prefix'] . 'theme_folder_url'	=> rtrim( $variables['theme_url'], '/' ) . '/'
		);

		$sql	= "SELECT 	variable_name
				   FROM 	exp_global_variables
				   WHERE 	site_id = ".ee()->db->escape_str( ee()->config->item('site_id') )."
				   AND 		variable_name
				   IN 		('".implode( "','", array_keys( $global_vars ) )."')";

		$query	= ee()->db->query( $sql );

		if ( $query->num_rows() > 0 )
		{
			foreach ( $query->result_array() as $row )
			{
				$this->cached_vars['conflicting_global_vars'][]	= $row['variable_name'];
			}

			$arr	= array(
				'label'			=> ee()->lang->line('conflicting_global_var_names'),
				'description'	=> ee()->lang->line('conflicting_global_var_names_exp')
			);

			$this->cached_vars['errors'][]	= $arr;
		}

		/** ----------------------------------------
		/**	Additional data insert extension hook start
		/** ----------------------------------------*/

		if ( ee()->extensions->active_hook( 'code_pack_module_install_begin_' . $variables['code_pack_name'] ) === TRUE )
		{
			$this->cached_vars	= ee()->extensions->universal_call(
				'code_pack_module_install_begin_' . $variables['code_pack_name'], $this, $variables );

			if (ee()->extensions->end_script === TRUE) return;
		}

		/** --------------------------------------------
        /**	Do we have errors?
        /** --------------------------------------------*/

        if ( ! empty( $this->cached_vars['errors'] ) )
        {
        	return $this->cached_vars;
        }

		/** -------------------------------------
		/**	Create global variables
		/** -------------------------------------*/

		if ( empty( $this->cached_vars['conflicting_global_vars'] ) )
		{
			foreach ( $global_vars as $key => $val )
			{
				$sql	= ee()->db->insert_string(
					'exp_global_variables',
					array(
						'site_id'		=> ee()->config->item('site_id'),
						'variable_name'	=> $key,
						'variable_data'	=> $val
					)
				);

				ee()->db->query( $sql );

				$this->cached_vars['global_vars'][]		= $key;
			}
		}

		if ( count( $this->cached_vars['global_vars'] ) > 0 )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('global_vars_added'),
				'description'	=> ee()->lang->line('global_vars_added_exp')
			);

			$this->cached_vars['success'][]	= $arr;
		}

		/** -------------------------------------
		/**	Install templates
		/** -------------------------------------*/

		if ( empty( $this->cached_vars['errors'] ) AND ! empty( $this->cached_vars['template_groups'] ) )
		{
			//get template group number
			$tg_query = ee()->db->query('SELECT MAX(group_order) AS group_order FROM exp_template_groups');

			$group_order = 0;

			if ($tg_query->num_rows() > 0)
			{
				$group_order = $tg_query->row('group_order');
			}

			foreach ( $this->cached_vars['template_groups'] as $group )
			{
				$files	= $this->data->get_files( $variables['theme_path'] . '/html/' . $group . '/' );

				if ( in_array( 'index.txt', $files ) === FALSE AND
				 	 in_array( 'index.html', $files ) === FALSE)
				{
					$files[]	= 'index.html';
				}

				/** -------------------------------------
				/**	Install group
				/** -------------------------------------*/

				if ( in_array( $group, $this->cached_vars['reserved_names'] ) === TRUE ) continue;

				$sql	= ee()->db->insert_string(
					'exp_template_groups',
					array(
						'site_id'		=> ee()->config->item('site_id'),
						'group_name'	=> $variables['prefix'] . $group,
						'group_order'	=> ++$group_order
					)
				);

				ee()->db->query( $sql );

				$group_id	= ee()->db->insert_id();

				/** -------------------------------------
				/**	Add templates
				/** -------------------------------------*/

				foreach ( $files as $val )
				{
					//get filetype for storing properly
					$ext 			= substr(strrchr($val, '.'), 1);

					switch($ext)
					{
						case 'js':
						case 'json':
							$template_type		= 'js';
							break;
						case 'static':
							$template_type		= 'static';
							break;
						case 'css':
							$template_type		= 'css';
							break;
						case 'xml':
						case 'xslt':
							$template_type		= 'xml';
							break;
						case 'rss':
						case 'atom':
						case 'feed':
							$template_type		= (APP_VER < 2.0) ? 'rss' : 'feed';
							break;
						default:
							$template_type		= 'webpage';
							break;
					}

					//just want the name itself
					$name		= preg_replace( '/(\.' . $ext . ')$/s', '', $val );

					if ( in_array( $name, $this->cached_vars['reserved_names'] ) === TRUE ) continue;

					/** -------------------------------------
					/**	Parse prefix in template
					/** -------------------------------------*/

					$contents	= str_replace(
						'%prefix%',
						$variables['prefix'],
						$this->data->get_file_contents( $variables['theme_path'] . '/html/' . $group . '/' . $val )
					);

					//remove trailing slashes after path items if this is not 2.x
					if ( APP_VER < 2.0 )
					{
						$contents = preg_replace("#(".LD."path(=.+?)".RD.")\\/#", "$1" , $contents);
					}

					$remove = ( APP_VER >= 2.0 ) ? 1 : 2;
					$keep	= ( APP_VER >= 2.0 ) ? 2 : 1;

					//remove depending on version
					$contents = preg_replace(
						'/%ee'. $remove . '%(.*?)%\/ee' . $remove . '%/s',
						"" ,
						$contents
					);

					//remove depending on version
					$contents = preg_replace(
						'/%ee'. $keep . '%(.*?)%\/ee' . $keep . '%/s',
						"$1" ,
						$contents
					);

					/** -------------------------------------
					/**	Detect PHP
					/** -------------------------------------*/

					$php_parsing_on		= 'n';
					$php_parse_location	= 'o';

					if ( preg_match( '/<\?php/is', $contents ) )
					{
						$php_parsing_on	= 'y';
					}

					if ( preg_match( '/<\?php\s\/\/\sinput/is', $contents ) )
					{
						$php_parse_location	= 'i';
					}

					/** -------------------------------------
					/**	Prepare insert
					/** -------------------------------------*/

					$sql	= ee()->db->insert_string(
						'exp_templates',
						array(
							'site_id'				=> ee()->config->item('site_id'),
							'group_id'				=> $group_id,
							'template_type'			=> $template_type,
							'edit_date'				=> ee()->localize->now,
							'template_name'			=> $name,
							'template_data'			=> $contents,
							'allow_php'				=> $php_parsing_on,
							'php_parse_location'	=> $php_parse_location,
						)
					);

					ee()->db->query( $sql );

					$this->cached_vars['template_count']++;
				}
			}
		}

		if ( $this->cached_vars['template_count'] > 0 )
		{
			$arr	= array(
				'label'			=> ee()->lang->line('templates_added'),
				'description'	=> ee()->lang->line('templates_added_exp')
			);

			$this->cached_vars['success'][]	= $arr;
		}

		/** ----------------------------------------
		/**	Additional data insert extension hook end
		/** ----------------------------------------*/

		if (ee()->extensions->active_hook( 'code_pack_module_install_end_' . $variables['code_pack_name'] ) === TRUE)
		{
			$this->cached_vars	= ee()->extensions->universal_call( 'code_pack_module_install_end_' . $variables['code_pack_name'], $this, $variables );

			if (ee()->extensions->end_script === TRUE) return;
		}

		/** --------------------------------------------
        /**	Return
        /** --------------------------------------------*/

		return $this->cached_vars;
    }

    /**	End code pack install */
}
/* END Code_pack_actions Class */


?>