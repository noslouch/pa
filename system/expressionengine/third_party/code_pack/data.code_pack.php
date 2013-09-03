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
 * Code Pack - Data Models
 *
 * @package 	Solspace:Code pack
 * @author		Solspace DevTeam
 * @filesource 	./system/expressionengine/third_party/code_pack/data.code_pack.php
 */

require_once 'addon_builder/data.addon_builder.php';

class Code_pack_data extends Addon_builder_data_code_pack
{
	var $cached = array();

	// --------------------------------------------------------------------

	/**
	 * Get code pack full description
	 *
	 * @access	public
	 * @return	array
	 */

	function get_code_pack_full_description( $path = '' )
    {
    	$description	= '';

 		/** --------------------------------------------
        /**  Prep Cache, Return if Set
        /** --------------------------------------------*/

 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());

 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}

 		$this->cached[$cache_name][$cache_hash] = '';

 		/** --------------------------------------------
        /**  Validate
        /** --------------------------------------------*/

        if ( $path == '' ) return $description;

 		/** --------------------------------------------
        /**  Get description file contents
        /** --------------------------------------------*/

        $description	= $this->get_file_contents( $path.'/meta/description.html' );

 		/** --------------------------------------------
        /**  Return
        /** --------------------------------------------*/

 		$this->cached[$cache_name][$cache_hash] = $description;

 		return $description;
    }

    /*	End get code pack full description */

	// --------------------------------------------------------------------

	/**
	 * Get code pack image
	 *
	 * @access	public
	 * @return	array
	 */

	function get_code_pack_image( $path = '', $url = '' )
    {
    	$image	= '';

 		/** --------------------------------------------
        /**  Prep Cache, Return if Set
        /** --------------------------------------------*/

 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());

 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}

 		$this->cached[$cache_name][$cache_hash] = '';

 		/** --------------------------------------------
        /**  Validate
        /** --------------------------------------------*/

        if ( $path == '' OR $url == '' ) return $image;

 		/** --------------------------------------------
        /**  Does file exist?
        /** --------------------------------------------*/

        if ( file_exists( $path.'/meta/screenshot.jpg' ) === TRUE )
        {
			$image	= $url.'/meta/screenshot.jpg';
        }
        elseif ( file_exists( $path.'/meta/screenshot.png' ) === TRUE )
        {
			$image	= $url.'/meta/screenshot.png';
        }

 		/** --------------------------------------------
        /**  Return
        /** --------------------------------------------*/

 		$this->cached[$cache_name][$cache_hash] = $image;

 		return $image;
    }

    /*	End get code pack image */

	// --------------------------------------------------------------------

	/**
	 * Get file contents
	 *
	 * @access	public
	 * @return	array
	 */

	function get_file_contents( $path = '' )
    {
 		/** --------------------------------------------
        /**  Prep Cache, Return if Set
        /** --------------------------------------------*/

 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());

 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}

 		$this->cached[$cache_name][$cache_hash] = '';

 		/** --------------------------------------------
        /**  Validate
        /** --------------------------------------------*/

        if ( $path == '' ) return '';

 		/** --------------------------------------------
        /**  Get file contents
        /** --------------------------------------------*/

        $fp = @fopen( $path, 'rb' );

        $this->cached[$cache_name][$cache_hash] = @fread( $fp, filesize( $path ) );

        @fclose($fp);

        return $this->cached[$cache_name][$cache_hash];
    }

    /*	End get file contents */

	// --------------------------------------------------------------------

	/**
	 * Get files
	 *
	 * @access	public
	 * @return	array
	 */

	function get_files( $path = '' )
    {
 		/** --------------------------------------------
        /**  Prep Cache, Return if Set
        /** --------------------------------------------*/

 		$cache_name = __FUNCTION__;
 		$cache_hash = $this->_imploder(func_get_args());

 		if (isset($this->cached[$cache_name][$cache_hash]))
 		{
 			return $this->cached[$cache_name][$cache_hash];
 		}

 		$this->cached[$cache_name][$cache_hash] = array();

 		/** --------------------------------------------
        /**  Validate
        /** --------------------------------------------*/

        if ( $path == '' ) return array();

 		/** --------------------------------------------
        /**  Get folder contents
        /** --------------------------------------------*/

		if ($fp = @opendir($path))
		{
			while (false !== ($file = readdir($fp)))
			{
				$types = array('js','json','static','css','xml','xslt','rss','atom','feed', 'html', 'txt');

				if ( in_array(substr(strrchr($file, '.'), 1), $types) )
				{
					$this->cached[$cache_name][$cache_hash][$file] = $file;
				}
			}

			closedir($fp);
		}

        return $this->cached[$cache_name][$cache_hash];
    }

    /*	End get files */
}
// END CLASS Code_pack_data