<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

/*
====================================================================================================
 Author: Aaron Waldon
 http://www.causingeffect.com
 license http://www.causingeffect.com/software/expressionengine/ce-string/license-agreement
====================================================================================================
 This file must be placed in the /system/expressionengine/third_party/ce_str folder in your ExpressionEngine installation.
 package 		CE String
 copyright 		Copyright (c) 2013 Causing Effect
----------------------------------------------------------------------------------------------------
 Purpose: Chainable and nestable string helper functions in one convenient place.
====================================================================================================

License:
    CE String is licensed under the Commercial License Agreement found at http://www.causingeffect.com/software/expressionengine/ce-string/license-agreement
	Here are a couple of specific points from the license to note again:
    * One license grants the right to perform one installation of CE String. Each additional installation of CE String requires an additional purchased license.
    * You may not reproduce, distribute, or transfer CE String, or portions thereof, to any third party.
	* You may not sell, rent, lease, assign, or sublet CE String or portions thereof.
	* You may not grant rights to any other person.
	* You may not use CE String in violation of any United States or international law or regulation.
*/

include( PATH_THIRD . 'ce_str/config.php' );

$plugin_info = array(
						'pi_name'			=> 'CE String',
						'pi_version'		=> CE_STRING_VERSION,
						'pi_author'			=> 'Aaron Waldon - Causing Effect',
						'pi_author_url'		=> 'http://www.causingeffect.com/',
						'pi_description'	=> 'String helper functions that make life easier.',
						'pi_usage'			=> Ce_str::usage()
					);

class Ce_str
{
	var $var_prefix = '';

	private $allowed_functions = array( 'html_entity_decode', 'htmlentities', 'htmlspecialchars_decode', 'htmlspecialchars', 'json_encode', 'levenshtein', 'ltrim', 'md5', 'nl2br', 'number_format', 'ord', 'rawurlencode', 'rawurldecode', 'rtrim', 'sha1', 'similar_text', 'str_pad', 'str_word_count', 'strlen', 'stristr', 'strrchr', 'strrev', 'strstr','strtolower', 'strtoupper', 'substr', 'substr_count', 'substr_replace', 'substr', 'trim', 'ucfirst', 'ucwords', 'urlencode', 'urldecode', 'utf8_decode', 'utf8_encode', 'wordwrap' );

	public function __construct()
	{
		//EE super global
		$this->EE =& get_instance();
	}

	/**
	 * Ensures the constructor is called.
	 * @return void
	 */
	public function Ce_str()
	{
		$this->__construct();
	}

	/**
	 * Allows any number of methods to be executed. Each parameter name is the method name to call. Each parameter value represents the values to be passed in.
	 * @return string
	 */
	public function ing()
	{
		//read in the tag data
		$data = trim( $this->EE->TMPL->tagdata );

		//if there is no tag data, let's bail
		if ( $data == '' )
		{
			return $this->EE->TMPL->no_results;
		}

		//fetch the params
		$arg_delimiter = $this->EE->TMPL->fetch_param('arg_delimiter', '|');
		$array_delimiter = $this->EE->TMPL->fetch_param('array_delimiter', ',');

		$actions = $this->get_actions( $this->EE->TMPL->tagproper, array( 'arg_delimiter', 'array_delimiter' ) );

		//get the var prefix
		$tag_parts = $this->EE->TMPL->tagparts;
		if ( is_array( $tag_parts ) && isset( $tag_parts[2] ) )
		{
			$this->var_prefix = $tag_parts[2] . ':';
		}

		foreach( $actions as $action )
		{
			//remove any EE comment tags
			$data = $this->EE->TMPL->remove_ee_comments( $data );

			if ( $data == '' )
			{
				break;
			}

			//get the method
			$method = $action['method'];

			$type = '';

			if ( method_exists( __CLASS__, $method ) )
			{
				$type = 'custom';
			}
			else if ( in_array( $method, $this->allowed_functions ) )
			{
				$type = 'native';
			}

			//see if the format method exists
			if ( $type != '' )
			{
				//get the params string
				$param = $action['params'];

				//turn the params string into params
				$params = array();
				if ( ! empty( $param ) ) //there are params
				{
					//split the param string into params
					$params = explode( $arg_delimiter, $param );

					//split each sub parameter into an array if applicable
					foreach( $params as $index => $sub_param )
					{
						if ( strpos( $sub_param, $array_delimiter ) !== FALSE )
						{
							$params[$index] = explode( $array_delimiter, $sub_param );
						}
						else if ( $type == 'native' && preg_match( '@^\:([A-Z]{3,})\:(.*)@', $sub_param, $match ) )
						{
							switch ( $match[1] )
							{
								case 'CONST':
									$params[$index] = ( defined( $match[2] ) ) ? constant( $match[2] ) : '';
									break;
								case 'BOOL':
									$params[$index] = (bool) $match[2];
									break;
								case 'INT':
									$params[$index] = (int) $match[2];
									break;
							}

						}
					}
				}

				//place the data at the beginning of the params array
				array_unshift( $params, $data );

				$data = ( $type == 'custom' ) ? @call_user_func_array( array( __CLASS__, $method ), $params ) : @call_user_func_array( $method, $params );
			}
		}

		if ( trim($data) == '' )
		{
			return $this->EE->TMPL->no_results;
		}
		else
		{
			return $data;
		}
	}

	/**
	 * This method is somewhat similar to the assign_parameters method of the Functions class, but I needed to be able to have multiple attributes with the same name. Additionally, methods with no parameters are added back into the actions array.
	 * @param string $tag
	 * @param array $ignore
	 * @return array An array of actions. Each action consists of an array with a method and a param string.
	 */
	private function get_actions( $tag, $ignore = array() )
	{
		//remove everything except the parameters
		$tag = preg_replace( '@^' . LD . '\S*?(.*)'  . RD . '$@Us', '$1', $tag );

		$actions = array();

		//match all attributes and their values
		preg_match_all( '@(\S+?)\s*=\s*(\042|\047)([^\\2]*?)\\2@is', $tag, $matches, PREG_SET_ORDER); //removed the 'U' modifier 12/11/2012

		//add the matched actions to the actions array
		foreach ( $matches as $match )
		{
			//remove the parameter from the original string
			$tag = preg_replace( '@' . preg_quote( $match[0], '@' ) . '@Us', '!', $tag, 1 );

			if ( ! in_array( $match[1], $ignore ) )
			{
				$actions[] = array( 'method' => $match[1], 'params' => $match[3] );
			}
		}

		//add the remaining actions to the actions array (methods with no parameters)
		if ( trim( $tag ) != '' )
		{
			//get rid of extra white space
			$tag = preg_replace( '@\s{2,}@Us', ' ', trim( $tag ) );
			$singles = explode( ' ', $tag );
			foreach ( $singles as $index => $single )
			{
				if ( $single == '!' )
				{
					continue;
				}

				array_splice( $actions, $index, 0, '' ); //just create the index
				$actions[$index] = array( 'method' => $single, 'params' => null ); //now insert the content into the index
			}
		}

		return $actions;
	}

	/**
	 *
	 * Allows EE code to be executed.
	 *
	 * Copyright (C) 2004 - 2011 EllisLab, Inc.
	 *
	 *	 Permission is hereby granted, free of charge, to any person obtaining a copy
	 *	 of this software and associated documentation files (the "Software"), to deal
	 *	 in the Software without restriction, including without limitation the rights
	 *	 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 *	 copies of the Software, and to permit persons to whom the Software is
	 *	 furnished to do so, subject to the following conditions:
	 *
	 *	 The above copyright notice and this permission notice shall be included in
	 *	 all copies or substantial portions of the Software.
	 *
	 *	 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 *	 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 *	 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
	 *	 ELLISLAB, INC. BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
	 *	 IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
	 *	 CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	 *
	 *	 Except as contained in this notice, the name of EllisLab, Inc. shall not be
	 *	 used in advertising or otherwise to promote the sale, use or other dealings
	 *	 in this Software without prior written authorization from EllisLab, Inc.
	 *
	 * @license MIT (X11) License
	 * @link https://github.com/EllisLab/Allow-Eecode
	 * @param string $string
	 * @param string $query
	 * @param string $embed
	 * @return string
	 */
	private function allow_eecode( $string, $query = 'no', $embed = 'no' )
	{
		$query = $this->ee_string_to_bool( $query );
		$embed = $this->ee_string_to_bool( $embed );

		if ( $query )
		{
			$string = preg_replace( "/&#123;exp:query(.*?)&#125;/", "TgB903He0mnv3dd098$1TgB903He0mnv3dd099", $string );
			$string = str_replace( '&#123;/exp:query&#125;', 'Mu87ddk2QPoid990iod', $string );
		}

		if ( $embed )
		{
			$string = str_replace( '&#123;embed', 'a9f83fa8b65b27e43a9db5fa4b2f62c8a23330e6', $string );
		}

		$array1 = array( '&#123;', '&#125;', '{&#47;' );
		$array2 = array( '{', '}', '{/' );

		$string = str_replace( $array1, $array2, $string );

		if ( preg_match_all( "#\{.+?}#si", $string, $matches ) )
		{
			for  ($i = 0, $total = count( $matches[0] ); $i < $total; $i++ )
			{
				$string = str_replace( $matches['0'][$i], str_replace( array('&#8220;', '&#8221;', '&#8216;','&#8217;'), array('"', '"', "'", "'"), $matches['0'][$i]), $string );
			}
		}

		if ( $query )
		{
			$string = str_replace( 'TgB903He0mnv3dd098', '&#123;exp:query', $string );
			$string = str_replace( 'TgB903He0mnv3dd099', '&#125;', $string );
			$string = str_replace( 'Mu87ddk2QPoid990iod', '&#123;/exp:query&#125;', $string );
		}

		if ( $embed )
		{
			$string = str_replace( 'a9f83fa8b65b27e43a9db5fa4b2f62c8a23330e6', '&#123;embed', $string );
		}

		return $string;
	}

	/**
	 * An alternative to the switch param that preserves whitespace and can be prefixed. Easily alternates between strings.
	 *
	 * @param string $string
	 * @return string
	 */
	private function alternate( $string )
	{
		if ( strpos( $string,  LD . $this->var_prefix . 'alternate' ) === false )
		{
			return $string;
		}

		//Parse {alternate="foo|bar"} variables in the tagdata. This code is similar to the {alternate=} variable code in EE.
		if ( preg_match_all( '~' . LD . $this->var_prefix . 'alternate\s*=(\042|\047)(.+?)\\1' . RD . '~i', $string, $matches, PREG_SET_ORDER ) ) //match the variables
		{
			foreach ($matches as $match) //loop through the matches
			{
				//explode the parameters
				$alternate_options = explode( '|', $match[2] );

				$count = count($alternate_options);

				$i = 1;
				while ( ($pos = strpos( $string, $match[0] ) ) !== false )
				{
					$string = substr_replace( $string, $alternate_options[( $i++ + $count - 1) % $count], $pos, strlen( $match[0] ) );
				}
			}
		}
		return $string;
	}

	/**
	 * Converts alphabetic characters to an integer representation. This is the opposite of the int_to_alpha method.
	 *
	 * A method derived from a function originally posted by Theriault 30-Nov-2009 10:11 http://www.php.net/manual/en/function.base-convert.php#94874
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param string $alpha The alphabetic character(s).
	 * @param string $zero_based Is A = 0 (yes) or is A = 1 (no).
	 * @return int|string
	 */
	private function alpha_to_int( $alpha, $zero_based = "no" )
	{
		$alpha = trim( $alpha );
		if ( empty( $alpha ) || ! ctype_alpha( $alpha ) )
		{
			return '';
		}

		$alpha = strtoupper( $alpha );

		//is the number zero based? In other words, is 0 = A, or is 1 = A?
		$zero_base = $this->ee_string_to_bool( $zero_based );

		$int = 0;
		$length = strlen( $alpha );
		for ( $i = 0; $i < $length; $i++ )
		{
			$int += pow( 26, $i ) * ( ord( $alpha[ $length - $i - 1 ] ) - 0x40 );
		}
		return ( $zero_base ) ? $int - 1 : $int;
	}

	/**
	 * Replaces all '&' that are not entities with '&amp;'.
	 *
	 * A method derived from a function originally posted by alif at 11-Feb-2010 05:17 http://www.php.net/manual/en/function.htmlspecialchars.php#96159
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param string $string
	 * @return string
	 */
	private function amped( $string )
	{
		return preg_replace( '/&(?![A-Za-z0-9#]{1,7};)/iU', '&amp;', $string );
	}

	/**
	 * Auto-links URLs.
	 *
	 * Copyright (C) 2004 - 2011 EllisLab, Inc.
	 *
	 *     Permission is hereby granted, free of charge, to any person obtaining a copy
	 *     of this software and associated documentation files (the "Software"), to deal
	 *     in the Software without restriction, including without limitation the rights
	 *     to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 *     copies of the Software, and to permit persons to whom the Software is
	 *     furnished to do so, subject to the following conditions:
	 *
	 *     The above copyright notice and this permission notice shall be included in
	 *     all copies or substantial portions of the Software.
	 *
	 *     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 *     IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 *     FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
	 *     ELLISLAB, INC. BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
	 *     IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
	 *     CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
	 *
	 *     Except as contained in this notice, the name of EllisLab, Inc. shall not be
	 *     used in advertising or otherwise to promote the sale, use or other dealings
	 *     in this Software without prior written authorization from EllisLab, Inc.
	 *
	 * @license MIT (X11) License
	 * @link https://github.com/EllisLab/Auto-Linker
	 * @param string $string
	 * @param string $target Optionally set the target attribute to open in a new window by setting to '_blank'.
	 * @param string $type Can be 'all' (links both URLs and email addresses), 'url' (only links URLs), or 'email' (only links email addresses).
	 * @return string
	 */
	private function auto_link( $string, $target = '', $type = 'all' )
	{
		if ( ! in_array( $type, array( 'all', 'url', 'email' ) ) )
		{
			$type = 'all';
		}

		$pop = ( $target == '_blank') ? ' target="_blank" ' : '';

		//clear period from the end of URLs
		$string = preg_replace("#(^|\s|\()((http://|https://|www\.)\w+[^\s\)]+)\.([\s\)])#i", "\\1\\2{{PERIOD}}\\4", $string);

		if ( $type == 'all' || 'url')
		{
			//auto link URL
			$string = preg_replace("#(^|\s|\(|>)((http(s?)://)|(www\.))(\w+[^\s\)\<]+)#i", "\\1<a href=\"http\\4://\\5\\6\"$pop>http\\4://\\5\\6</a>", $string);
		}

		//clean up periods
		$string = preg_replace("#<a href=(.+?){{PERIOD}}(.+?){{PERIOD}}</a>#", "<a href=\\1\\2</a>.", $string);

		//clear period from the end of emails
		$string = preg_replace("#(^|\s|\(|>)([a-zA-Z0-9_\.\-]+)@([a-zA-Z0-9\-]+)\.([a-zA-Z0-9\-\.]*)\.([\s\)])#i","\\1\\2@\\3.\\4\\5{{PERIOD}}",$string);

		if ( $type == 'all' || 'email')
		{
			//auto link email
			$string = preg_replace("/(^|\s|\(|>)([a-zA-Z0-9_\.\-]+)@([a-zA-Z0-9\-]+)\.([a-zA-Z0-9\-\.]*)/i", "\\1<a href=\"mailto:\\2@\\3.\\4\">\\2@\\3.\\4</a>", $string);
		}

		//cleaned up stray periods
		$string = str_replace(" {{PERIOD}}", ". ", $string);

		return $string;
	}

	/**
	 * From the EE docs: "This function takes a string of text and returns typographically correct XHTML. It's primary modifications are:
	 * turns double spaces into paragraphs.
	 * adds line breaks where there are single spaces.
	 * turns single and double quotes into curly quotes.
	 * turns three dots into ellipsis.
	 * turns double dashes into em-dashes."
	 *
	 * @link http://expressionengine.com/user_guide/development/usage/typography.html#auto_typography
	 * @param string $string
	 * @return string
	 */
	private function auto_typography( $string )
	{
		$this->EE->load->library('typography');
		$this->EE->typography->initialize();
		return $this->EE->typography->auto_typography( $string );
	}

	/*
	 * Removes a specified number of characters from the end of a string.
	 *
	 * @param string $string
	 * @return string
	 */
	private function backspace( $string, $characters = 0 )
	{
		return substr( $string, 0, strlen( $string ) - $characters );
	}

	/**
	 * From the EE docs: "This function encodes email addresses with Javascript, to assist in prevention of email harvesting by bots."
	 *
	 * @link http://expressionengine.com/user_guide/development/usage/typography.html#encode_email_addresses
	 * @param string $email Email address.
	 * @param string $title The text to use as the title of the email link.
	 * @param string $anchor Whether or not a clickable link is created for the email address.
	 * @return string JavaScript encoded email address
	 */
	private function encode_email_script( $email, $title = '', $anchor = 'yes' )
	{
		$anchor = $this->ee_string_to_bool( $anchor );
		$this->EE->load->library( 'typography' );
		$this->EE->typography->initialize();
		return $this->EE->typography->encode_email( $email, $title, $anchor );
	}

	/**
	 * Creates a human-readable "encoded" email address, without JavaScript:
	 *
	 * @link http://expressionengine.com/user_guide/development/usage/typography.html#encode_email_addresses
	 * @param string $email
	 * @return string JavaScript encoded email address
	 */
	private function encode_email_noscript( $email )
	{
		$this->EE->load->library( 'typography' );
		$this->EE->typography->initialize();
		$this->EE->typography->encode_type = 'noscript';
		return $this->EE->typography->encode_email( $email );
	}

	/**
	 * Encodes all email addresses in script or human readable form. The 'fallback' option will script-encode the email address, and give a human-readable fallback version.
	 *
	 * @param string $string
	 * @param string $type Can be 'script', 'noscript', or 'fallback'
	 * @param string $at Text to use for the 'noscript' @ sign.
	 * @param string $dot Text to use for the 'noscript' . sign.
	 * @param string $open Text to use for the 'noscript' open tag.
	 * @param string $close Text to use for the 'noscript' close tag.
	 * @return string
	 */
	private function encode_email_bulk( $string, $type = 'script', $at = ' at ', $dot = ' dot ', $open = ' (', $close = ')')
	{
		if ( ! in_array( $type, array( 'fallback', 'script', 'noscript' ) ) )
		{
			$type = 'script';
		}

		//let's first autolink all email addresses
		$string = $this->auto_link( $string, '', 'email' );

		//match the email addresses
		if ( preg_match_all( '`<a[^>]+href=(\042|\047)mailto\:([^\\1]*?)\\1[^>]*>(.*?)</a>`uSi', $string, $matches, PREG_SET_ORDER ) )
		{
			//$match[0] Entire tag, $match[1] Quote ('|"), $match[2] Email address, $match[3] Element value
			//load the typography class
			if ( $type == 'script' || 'fallback' )
			{
				$this->EE->load->library( 'typography' );
				$this->EE->typography->initialize();
			}

			//loop through the matches
			foreach ( $matches as $match )
			{
				//create the human readable email address
				if ( $type == 'noscript' || $type == 'fallback' )
				{
					$replacement = str_replace( array( '@', '.' ), array( $at, $dot ), $match[2] );

					if ( ! empty( $match[3] ) && trim( $match[3] != $match[2] ) )
					{
						$replacement = $match[3] . ' ' . $open . $replacement . $close;
					}
				}

				if ( $type == 'script' )
				{
					//js encode the addresses
					$replacement = $this->EE->typography->encode_email( $match[2], $match[3], true );
				}
				else if ( $type == 'fallback' )
				{
					//js encode the address, and replace the default JavaScript message with the human-readable version
					$replacement = str_replace( '.(JavaScript must be enabled to view this email address)', $replacement, $this->EE->typography->encode_email( $match[2], $match[3], true ) );
				}

				//replace the original tag with the replacement text
				$string = str_replace( $match[0], $replacement, $string );
			}
		}

		return $string;
	}

	/**
	 * Expands unexpanded escape sequences like \n and \t.
	 *
	 * A method derived from a function originally posted by Evan K 28-Feb-2008 09:03 http://www.php.net/manual/en/language.types.string.php#81457
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param string $string
	 * @return string
	 */
	private function expand_escaped( $string )
	{
		return preg_replace_callback( '/\\\([nrtvf]|[0-7]{1,3}|[0-9A-Fa-f]{1,2})?/',
        create_function( '$matches', 'return ($matches[0] == "\\\\") ? "" : eval( sprintf(\'return "%s";\', $matches[0]) );'
        ), $string );
	}

	/**
	 * Adds target="_blank" to external links.
	 *
	 * @param string $string
	 * @param array|string $ignored_domains Domains to ignore. These will not be flagged as external domains.
	 * @param string $add_class One or more classes to assign to the link.
	 * @param bool $ignore_current_domain Whether or not to ignore the current domain. Defaults to true.
	 * @return string
	 */
	private function external_links( $string, $ignored_domains = array(), $add_class = '', $ignore_current_domain = true )
	{
		//grab all of the anchors
		if ( preg_match_all( '@<(a)([^>]*?)>@uSi', $string, $matches, PREG_SET_ORDER ) )
		{
			$ignored_domains = (array) $ignored_domains;

			foreach ( $ignored_domains as $ignored_domain )
			{
				if ( strpos( $ignored_domain, 'www.' ) !== 0 )
				{
					$ignored_domains[] = 'www.' . $ignored_domain;
				}
			}

			if ( $ignore_current_domain )
			{
				$current_domain = $this->EE->security->xss_clean( preg_replace( '@' . preg_quote( 'www.', '@' ) . '@', '', $_SERVER['SERVER_NAME'], 1 ) );
				$ignored_domains[] = $current_domain;
				$ignored_domains[] = 'www.' . $current_domain;
			}

			//loop through the anchors
			foreach ( $matches as $match )
			{
				//get the attributes
				preg_match_all( '@(\S+?)\s*=\s*(\042|\047)([^\\2]*?)\\2@is', $match[2], $attributes, PREG_SET_ORDER);

				//this will hold the anchor's attribute pairs
				$pairs = array();
				foreach ( $attributes as $attribute )
				{
					//attribute => value
					$pairs[ $attribute[1] ] = $attribute[3];
				}

				//make sure this is an external link
				if ( ! isset ( $pairs['href'] ) || ! preg_match( '`^(?://|http://|https://)(.+)`', $pairs['href'], $start )  )
				{
					continue;
				}

				$temp = 'http://' . $start[1];
				$info = parse_url( $temp );
				if ( isset( $info['host'] ) && ( in_array( $info['host'], $ignored_domains ) || in_array( 'www.' . $info['host'], $ignored_domains ) ) )
				{
					continue;
				}

				//set target="_blank"
				$pairs['target'] = '_blank';

				if ( ! empty( $add_class ) )
				{
					if ( isset( $pairs['class'] ) )
					{
						$add_class = explode( ' ', $add_class );
						$temp = array_merge( explode( ' ', $pairs['class'] ), $add_class );
						$pairs['class'] = implode( ' ', array_unique( $temp ) );
					}
					else
					{
						$pairs['class'] = implode( ' ', $add_class );
					}
				}

				//reconstruct the attributes
				$attributes = '';
				foreach ( $pairs as $param => $value )
				{
					//$attributes .= $param . '="' . $value . '" ';
					$attributes .= ( strpos( $value, '"' ) === false) ? " {$param}=\"{$value}\"" : " {$param}='{$value}'";
				}

				//create the opening anchor tag
				$anchor = '<' . $match[1] . $attributes . '>';

				//replace the tag
				$string = preg_replace( '@' . preg_quote( $match[0], '@' ) . '@', $anchor, $string, 1 );
			}
		}

		return $string;
	}

	/**
	 * From the EE docs: "This function performs the character transformation portion of the XHTML typography only, i.e. curly quotes, ellipsis, ampersand, etc."
	 *
	 * This is also known as the "light" version of auto_typography.
	 *
	 * @link http://expressionengine.com/user_guide/development/usage/typography.html#format_characters
	 * @param string $string
	 * @return string
	 */
	private function format_characters( $string = '' )
	{
		$this->EE->load->library( 'typography' );
		$this->EE->typography->initialize();
		return $this->EE->typography->format_characters( $string );
	}

	/**
	 * The following slightly modified method is from the CakePHP text helper. This method is bound to the terms of the MIT license.
	 *
	 * Highlights a given phrase in a text. You can specify any expression in highlighter that
	 * may include the \1 expression to include the $phrase found.
	 *
	 * ### Options:
	 *
	 * - `format` The piece of html with that the phrase will be highlighted
	 * - `html` If true, will ignore any HTML tags, ensuring that only the correct text is highlighted
	 * @param string $text Text to search the phrase in
	 * @param string $phrase The phrase that will be searched
	 * @param string $format
	 * @param string $html
	 *
	 * @internal param array $options An array of html attributes and options.
	 * @return string The highlighted text
	 * @access public
	 * @link http://book.cakephp.org/view/1469/Text#highlight-1622
	 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	 */
	private function highlight( $text, $phrase, $format = '<span class="highlight">\1</span>', $html = 'yes' )
	{

		$html = $this->ee_string_to_bool( $html );

		if (is_array($phrase))
		{
			$replace = array();
			$with = array();

			foreach ($phrase as $key => $segment)
			{
				$segment = "($segment)";
				if ($html)
				{
					$segment = "(?![^<]+>)$segment(?![^<]+>)";
				}

				$with[] = (is_array($format)) ? $format[$key] : $format;
				$replace[] = "|$segment|iu";
			}

			return preg_replace($replace, $with, $text);
		}
		else
		{
			$phrase = "($phrase)";
			if ($html)
			{
				$phrase = "(?![^<]+>)$phrase(?![^<]+>)";
			}

			return preg_replace("|$phrase|iu", $format, $text);
		}
	}

	/**
	 * Converts an integer to an alphabetic representation, like A-Z, AA-ZZ, etc. This is the opposite of the alpha_to_int method.
	 *
	 * A method derived from a function originally posted by Theriault 30-Nov-2009 10:11 http://www.php.net/manual/en/function.base-convert.php#94874
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param int|string $int The number to convert.
	 * @param string $zero_based Is A = 0 (yes) or is A = 1 (no).
	 * @return int|string
	 */
	private function int_to_alpha( $int, $zero_based = "no" )
	{
		//make sure we have a number
		$int = trim( $int );
		if ( ! is_numeric( $int ) )
		{
			return '';
		}

		//is the number zero based? In other words, is 0 = A, or is 1 = A?
		$zero_base = $this->ee_string_to_bool( $zero_based );

		if ( $zero_base && $int < 0 ) //The number should not be less than 0
		{
			return '';
		}
		else if ( ! $zero_base ) //The number should not be less than 1
		{
			if ( $int < 1 )
			{
				return '';
			}

			$int -= 1;
		}

		//convert the number to alphabetic characters
		$alpha = '';
		for ($i = 1; $int >= 0 && $i < 10; $i++)
		{
			$alpha = chr( 0x41 + ( $int % pow( 26, $i ) / pow( 26, $i - 1 ) ) ) . $alpha;
			$int -= pow( 26, $i );
		}

		return $alpha;
	}

	/**
	 * Converts an integer to a Roman numeral. The opposite of roman_to_int.
	 *
	 * A method derived from a function originally posted by MFTM 16-Aug-2009 06:30 http://www.php.net/manual/en/function.base-convert.php#92960
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param $int The integer.
	 * @return string The Roman numeral.
	 */
	private function int_to_roman( $int )
	{
		//make sure we have a number
		$int = trim( $int );
		if ( ! is_numeric( $int ) )
		{
			return '';
		}

		$table = array( 'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1 );

		$roman = '';

		while ( $int > 0 )
		{
			foreach ( $table as $rom => $arb )
			{
				if ( $int >= $arb )
				{
					$int -= $arb;
					$roman .= $rom;
					break;
				}
			}
		}

		return $roman;
	}

	/**
	 * Gets (X)HTML from a Markdown Extra markup string.
	 *
	 * @param string $string
	 * @param string $html Should the output be HTML? A value of 'yes' will produce HTML (default), a value of 'no' will produce XHTML.
	 * @return string
	 */
	private function markdown( $string, $html = 'yes' )
	{
		if ( ! function_exists( 'Markdown' ) )
		{
			require PATH_THIRD . 'ce_str/libraries/php_markdown_extra_1.2.5/markdown.php';
		}

		if ( $this->ee_string_to_bool( $html ) ) //use html
		{
			@define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX',  ">");
		}
		else //use xhtml
		{
			@define( 'MARKDOWN_EMPTY_ELEMENT_SUFFIX',  " />");
		}

		return Markdown( $string );
	}

	/**
	 * A simple matheval function. Can only handle simple numeric calculations.
	 *
	 * @param $equation
	 * @return int|string
	 */
	private function math_lite( $equation, $zero_for_empty = 'no' )
	{
		$zero_for_empty = $this->ee_string_to_bool( $zero_for_empty );

		// Remove everything except for simple math characters
		$equation = preg_replace( '@[^0-9+\-.*/()%]@', '', trim( $equation ) );

		$final = ''; //this is only here for the benefit of the IDE to not think the return is undefined

		if ( $equation === '' || ! preg_match( '@\d@', $equation ) )
		{
			return $zero_for_empty ? 0 : $this->EE->TMPL->no_results;
		}
		else
		{
			eval( '$final = ' . $equation . ';' );
		}

		if ( ! is_numeric( $final ) )
		{
			return $zero_for_empty ? 0 : $this->EE->TMPL->no_results;
		}
		return $final;
	}

	/**
	 * Evaluates the string as PHP. Please note that this method will allow anyone with coding access to your templates to play God with your server. If you want to get around using early parse order, you can use {php} to represent an opening php tag and {/php} to represent a closing php tag.
	 *
	 * @param string $string
	 * @return string The evaluated code.
	 */
	private function php( $string = '' )
	{
		//check to make sure this method is not disallowed in the config
		$setting = FALSE;
		if ( isset( $this->EE->config->_global_vars[ 'ce_str_disable_php' ] ) && $this->EE->config->_global_vars[ 'ce_str_disable_php' ] != '' ) //first check global array
		{
			$setting = $this->EE->config->_global_vars[ 'ce_str_disable_php' ];
		}
		else if ( $this->EE->config->item( 'ce_str_disable_php' ) != '' ) //then check config
		{
			$setting = $this->EE->config->item( 'ce_str_disable_php' );
		}

		//if the method is disallowed in the config, return
		if ( $this->ee_string_to_bool( $setting ) )
		{
			return $string;
		}

		//swap out pseudo php tags used to get around parsing order
		$string = str_replace(
			array( '{' . $this->var_prefix . 'php}', '{/' . $this->var_prefix . 'php}' ), //add in the prefix
			array( '<?php ', ' ?>' //adding some extra spacing here to prevent people from accidentally butting up text against the tags
		), $string );

		ob_start();
		$this->EE->functions->evaluate( $string );
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	/**
	 * Simplified preg_replace method.
	 *
	 * @param string $string
	 * @param string $find
	 * @param string $replace
	 * @param string $modifier
	 * @return string|string
	 */
	private function preg_rep( $string = '', $find = '', $replace = '', $modifier = '' )
	{
		if ( $string == '' || $find == '' )
		{
			return '';
		}

		if ( is_array($modifier) && isset( $modifier[0] ) )
		{
			$modifier = $modifier[0];
		}

		if ( ! is_array( $find ) )
		{
			$find = (array) $find;
		}


		foreach ( $find as $index => $found )
		{
			$find[$index] = '@' . $found . '@' . $modifier;
		}

		return preg_replace( $find, $replace, $string);
	}

	/**
	 * Removes EE variable tags that have no spaces in them. Ex: {blah}
	 *
	 * @param  $string
	 * @return string
	 */
	private function remove_ee_vars( $string )
	{
		return preg_replace('@\{\S*\}@', '', $string );
	}

	/**
	 * Will remove all HTML. No prisoners.
	 * @param string $string
	 * @return string
	 */
	private function remove_html( $string )
	{
		return preg_replace('/<[^>]*>/', '', $string); //strip all html
	}

	/**
	 * Will remove only the tags specified.
	 *
	 * A method derived from a function originally posted by gagomat at gmail dot com 22-Sep-2010 03:06 http://www.php.net/manual/en/function.strip-tags.php#100054
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param string $string
	 * @param string|array $tags
	 * @param string $strip_content Whether or not to remove the content within the stripped tags.
	 * @return string
	 */
	private function remove_tags( $string, $tags, $strip_content = 'no' )
	{
		$strip_content = $this->ee_string_to_bool( $strip_content );

		$content = '';
		if( ! is_array( $tags ) )
		{
			$tags = ( strpos( $string, '>' ) !== false ) ? explode( '>', str_replace( '<', '', $tags ) ) : array( $tags );
			if ( end( $tags ) == '' )
			{
				array_pop($tags);
			}
		}
		foreach( $tags as $tag )
		{
			if ( $strip_content )
			{
				$content = '(.+</' . $tag . '(>|\s[^>]*>)|)';
			}
			$string = preg_replace( '#</?' . $tag . '(>|\s[^>]*>)' . $content . '#is', '', $string );
		}
		return $string;
	}

	/**
	 * Will remove all tags except the ones specified.
	 *
	 * @param string $string
	 * @param array $tags_to_keep The tags to keep.
	 * @return string
	 */
	private function remove_tags_except( $string, $tags_to_keep )
	{
		if ( ! is_array( $tags_to_keep ) )
		{
			$tags_to_keep = (array) $tags_to_keep;
		}

		$tags = '';
		foreach ( $tags_to_keep as $keeper )
		{
			$tags .= "<$keeper>";
		}
		unset( $tags_to_keep );
		return strip_tags ( $string, $tags );
	}

	/**
	 * Find and replace.
	 *
	 * @param string $string
	 * @param string|array $search
	 * @param string|array $replace
	 * @return string
	 */
	private function replace( $string, $search = '', $replace = '')
	{
		return str_replace( $search, $replace, $string );
	}

	/**
	 * Replace the last occurrence of a string in a string.
	 *
	 * @param string $string
	 * @param string $search
	 * @param string $replace
	 * @return string
	 */
	private function replace_last( $string, $search = '', $replace = '' )
	{
		$pos = strrpos( $string, $search );

		if( $pos !== false )
		{
			$string = substr_replace( $string, $replace, $pos, strlen( $search ) );
		}

		return $string;
	}

	/**
	 * Converts a Roman numeral to an integer. The opposite of int_to_roman.
	 *
	 * A method derived from a function originally posted by Scooter <http://snipplr.com/users/Scooter/> on 05/19/08 to <http://snipplr.com/view/6314/>, which in turn was based on code from the Reusable Code blog <http://reusablecode.blogspot.com/search/label/roman%20numerals> by Scott <http://www.blogger.com/profile/00069741645360718046>.
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param string $roman The Roman numeral.
	 * @return int|string The integer.
	 */
	private function roman_to_int( $roman )
	{
		$roman = strtoupper( $roman );

		//make sure the roman numeral is valid
		if ( ! preg_match( '@[MDCLXVI]@u', $roman ) )
		{
			return '';
		}

		//remove subtractive notation.
		$roman = str_replace( array( 'CM', 'CD', 'XC', 'XL', 'IX', 'IV' ), array( 'DCCCC', 'CCCC', 'LXXXX', 'XXXX', 'VIIII', 'IIII' ), $roman );

		$numerals = array(
			'M' => 1000,
			'D' => 500,
			'C' => 100,
			'L' => 50,
			'X' => 10,
			'V' => 5,
			'I' => 1
		);

		//the integer value
		$int = 0;

		//calculate for each numeral.
		foreach ( $numerals as $numeral => $value )
		{
			$int += substr_count( $roman, $numeral ) * $value;
		}

		return $int;
	}

	/**
	 * Converts a string to sentence case.
	 *
	 * A method derived from a function originally posted by mattalexxpub at gmail dot com 10-Nov-2008 01:10 http://www.php.net/manual/en/function.ucfirst.php#86902
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param string $string
	 * @return string
	 */
	private function sentence_case( $string )
	{
		$sentences = preg_split( '/([.?!]+)/', $string, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE );
		$new_string = '';
		foreach ( $sentences as $key => $sentence )
		{
			$new_string .= ($key & 1) == 0?
			ucfirst(strtolower(trim($sentence))) :
			$sentence . ' ';
		}
		return trim($new_string);
	}

	/**
	 * Easy to implement typography helper. Alias for the format_characters method.
	 *
	 * @link http://expressionengine.com/user_guide/development/usage/typography.html#format_characters
	 * @param string $string
	 * @return string
	 */
	private function smart( $string = '' )
	{
		return $this->format_characters( $string );
	}

	/**
	 * SmartyPants Typographer easily translates plain ASCII punctuation characters into "smart" typographic punctuation HTML entities.
	 * http://michelf.com/projects/php-smartypants/typographer/
	 *
	 * @param string $string
	 * @return string
	 */
	private function smartypants( $string )
	{
		if ( ! function_exists( 'SmartyPants' ) )
		{
			require PATH_THIRD . 'ce_str/libraries/php_smartypants_typographer_1.0/smartypants.php';
		}

		return SmartyPants( $string );
	}

	/**
	 * A wrapper for the native strpos function that returns -1 instead of FALSE. EE needs this distinction as there is not way to do strict comparisons ( === as opposed to == ) in the EE conditionals.
	 *
	 * @return string
	 */
	private function strpos()
	{
		$args = func_get_args();
		$pos = call_user_func_array( 'strpos', $args );

		if ( $pos === FALSE )
		{
			$pos = -1;
		}

		return $pos;
	}
	/**
	 * A wrapper for the native strripos function that returns -1 instead of FALSE. EE needs this distinction as there is not way to do strict comparisons ( === as opposed to == ) in the EE conditionals.
	 *
	 * @return string
	 */
	private function strripos()
	{
		$args = func_get_args();
		$pos = call_user_func_array( 'strripos', $args );

		if ( $pos === FALSE )
		{
			$pos = -1;
		}

		return $pos;
	}

	/**
	 * A wrapper for the native strrpos function that returns -1 instead of FALSE. EE needs this distinction as there is not way to do strict comparisons ( === as opposed to == ) in the EE conditionals.
	 *
	 * @return string
	 */
	private function strrpos()
	{
		$args = func_get_args();
		$pos = call_user_func_array( 'strrpos', $args );

		if ( $pos === FALSE )
		{
			$pos = -1;
		}

		return $pos;
	}

	/**
	 * Gets XHTML from a Textile-markup string.
	 *
	 * @param string $string
	 * @return string
	 */
	private function textile( $string )
	{
		if ( ! class_exists( 'Textile' ) )
		{
			require PATH_THIRD . 'ce_str/libraries/textile_2.2/classTextile.php';
		}

		$textile = new Textile();
		return $textile->TextileThis( $string );
	}


	/**
	 * Converts a url_title to title case.
	 *
	 * @param string $string
	 * @param string $separator
	 * @return string
	 */
	private function to_title( $string, $separator = '' )
	{
		if ( empty( $separator ) )
		{
			$separator = $this->EE->config->item( 'word_separator' ) != 'dash' ? '_' : '-';
		}

		$string = ucwords( strtolower( str_replace( $separator, ' ', $string ) ) );

		foreach (array('-', '\'') as $delimiter)
		{
			if ( strpos( $string, $delimiter )!== FALSE )
			{
				$string = implode( $delimiter, array_map( 'ucfirst', explode( $delimiter, $string ) ) );
			}
		}
		return $string;
	}

	/**
	 * Removes duplicate line breaks.
	 *
	 * @param string $string
	 * @param int $threshold The minimum number of line breaks before replacing.
	 * @param int $replace The number of line breaks to reduce to.
	 * @return string
	 */
	private function swap_breaks( $string, $threshold = 2, $replace = 1 )
	{
		//determine the threshold
		if ( ! is_numeric( $threshold ) || $threshold < 1 )
		{
			return $string;
		}

		//make sure the replace multiplier is valid
		if ( ! is_numeric( $replace ) || $replace < 0 )
		{
			$replace = 1;
		}

		//repeat the replace string
		$replace = str_repeat( "\n", $replace );

		//replace and return
		return preg_replace( '@(\r?\n){' . $threshold . ',}@s', $replace, $string );
	}

	/**
	 * The following method is slightly modified from the CakePHP text helper. This method is bound to the terms of the MIT license.
	 * Truncates text.
	 *
	 * Cuts a string to the length of $length and replaces the last characters with the ending if the text is longer than length.
	 *
	 * @param string  $text String to truncate.
	 * @param integer $length Length of returned string, including ellipsis.
	 * @param string $ending Will be used as Ending and appended to the trimmed string.
	 * @param string $exact If 'yes', $text will not be cut mid-word.
	 * @param string $html If 'yes', HTML tags would be handled correctly.
	 *
	 * @return string Trimmed string.
	 * @access public
	 * @link http://book.cakephp.org/view/1469/Text#truncate-1625
	 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
	 */
	private function truncate( $text, $length = 500, $ending = '&hellip;', $exact = 'no', $html = 'yes' )
	{
		$exact = $this->ee_string_to_bool( $exact );
		$html = $this->ee_string_to_bool( $html );

		if ( $html )
		{
			if ( strlen( preg_replace( '/<.*?>/', '', $text )) <= $length )
			{
				return $text;
			}
			$total_length = strlen(strip_tags($ending));
			$open_tags = array();
			$truncate = '';

			preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
			foreach ($tags as $tag)
			{
				if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2]))
				{
					if (preg_match('/<[\w]+[^>]*>/s', $tag[0]))
					{
						array_unshift($open_tags, $tag[2]);
					}
					else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag))
					{
						$pos = array_search($closeTag[1], $open_tags);
						if ($pos !== false)
						{
							array_splice($open_tags, $pos, 1);
						}
					}
				}
				$truncate .= $tag[1];

				$contentLength = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
				if ($contentLength + $total_length > $length)
				{
					$left = $length - $total_length;
					$entitiesLength = 0;
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE))
					{
						foreach ($entities[0] as $entity)
						{
							if ($entity[1] + 1 - $entitiesLength <= $left)
							{
								$left--;
								$entitiesLength += strlen($entity[0]);
							}
							else
							{
								break;
							}
						}
					}

					$truncate .= substr($tag[3], 0 , $left + $entitiesLength);
					break;
				}
				else
				{
					$truncate .= $tag[3];
					$total_length += $contentLength;
				}
				if ($total_length >= $length)
				{
					break;
				}
			}
		}
		else
		{
			if (strlen($text) <= $length)
			{
				return $text;
			}
			else
			{
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		if (!$exact)
		{
			$space_pos = strrpos($truncate, ' ');
			if (isset($space_pos))
			{
				if ( $html )
				{
					$bits = substr($truncate, $space_pos);
					preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
					if (!empty($droppedTags))
					{
						foreach ($droppedTags as $closingTag)
						{
							if (!in_array($closingTag[1], $open_tags))
							{
								array_unshift($open_tags, $closingTag[1]);
							}
						}
					}
				}
				$truncate = substr($truncate, 0, $space_pos);
			}
		}
		$truncate .= $ending;

		if ( $html )
		{
			foreach ($open_tags as $tag)
			{
				$truncate .= '</'.$tag.'>';
			}
		}

		return $truncate;
	}

	/**
	 * Auto-links Twitter content.
	 * - links @username to the user’s Twitter profile page
	 * - links regular links to wherever they should
	 * - links hashtags to a Twitter search on that hashtag
	 *
	 * A nice little group of regexs from http://www.snipe.net/2009/09/php-twitter-clickable-links/
	 * @param string $string
	 * @return string
	 */
	private function twitterfy( $string )
	{
		$string = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $string);
		$string = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $string);
		$string = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $string);
		$string = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $string);

		return $string;
	}

	/**
	 * Converts a string to an EE url_title.
	 *
	 * @param string $string
	 * @param string $separator
	 * @param bool|string $lowercase
	 * @return string
	 */
	private function url_title( $string, $separator = '', $lowercase = 'yes' )
	{
		if ( empty( $separator ) )
		{
			$separator = $this->EE->config->item( 'word_separator' ) != 'dash' ? '_' : '-';
		}

		$separator = ( $separator == '-' ) ? 'dash' : 'underscore';

		$lowercase = $this->ee_string_to_bool( $lowercase );
		$this->EE->load->helper('url_helper');
		return url_title( $string, $separator, $lowercase );
	}

	/**
	 * When using UTF-8 as a charset, htmlentities will only convert 1-byte and 2-byte characters. Use this function if you also want to convert 3-byte and 4-byte characters.
	 *
	 * A method derived from a function originally posted by silverbeat -eat- gmx -hot- at 09-Mar-2010 08:42 http://www.php.net/manual/en/function.htmlentities.php#96648
	 * Original code licensed under: http://creativecommons.org/licenses/by/3.0/legalcode
	 *
	 * @param  $utf8
	 * @param bool|string $encodeTags
	 * @return string
	 */
	private function utf8tohtml($utf8, $encodeTags = 'no' )
	{
		$encodeTags = $this->ee_string_to_bool( $encodeTags );
    	$result = '';
    	for ($i = 0; $i < strlen($utf8); $i++)
		{
			$char = $utf8[$i];
			$ascii = ord( $char );
			if ($ascii < 128)
			{
				// one-byte character
				$result .= ($encodeTags) ? htmlentities( $char ) : $char;
			}
			else if ($ascii < 192)
			{
				// non-utf8 character or not a start byte
			}
			else if ($ascii < 224)
			{
				// two-byte character
				$result .= htmlentities( substr( $utf8, $i, 2 ), ENT_QUOTES, 'UTF-8' );
				$i++;
			}
			else if ($ascii < 240)
			{
				// three-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$unicode = (15 & $ascii) * 4096 +
						   (63 & $ascii1) * 64 +
						   (63 & $ascii2);
				$result .= "&#$unicode;";
				$i += 2;
			}
			else if ( $ascii < 248 )
			{
				// four-byte character
				$ascii1 = ord($utf8[$i+1]);
				$ascii2 = ord($utf8[$i+2]);
				$ascii3 = ord($utf8[$i+3]);
				$unicode = (15 & $ascii) * 262144 +
						   (63 & $ascii1) * 4096 +
						   (63 & $ascii2) * 64 +
						   (63 & $ascii3);
				$result .= "&#$unicode;";
				$i += 3;
			}
		}
		return $result;
	}

	/**
	 * Limits text to a maximum number of words.
	 *
	 * @param string $string
	 * @param int $words
	 * @return string
	 */
	private function word_limit( $string, $words = 500 )
	{
		if ( ! is_numeric( $words ) )
		{
			$words = 500;
		}

		return $this->EE->functions->word_limiter( $string, $words );
	}

	/**
	 * The built in ExpressionEngine XSS sanitization method
	 *
	 * @param string $string
	 * @return string
	 */
	private function xss_clean( $string )
	{
		return $this->EE->security->xss_clean( $string );
	}

	/**
	 * A method to clean up the weird whitespace issues that WYSIWYG editors like Wygwam make.
	 *
	 * @param string $string
	 * @return string
	 */
	private function wysiwyg_cleanup( $string )
	{
		//TODO test this out and document
		//remove the annoying extra spacing in the p tags
		return preg_replace( '@<p>\s*?(.*)?\s*?</p>@Usi', '$1', $string );
	}

	/**
	 * Little helper method to convert parameters to a boolean value
	 *
	 * @param string $string
	 * @return bool
	 */
	private function ee_string_to_bool( $string )
	{
		return ( $string == 'y' || $string == 'yes' || $string == 'on' || $string === TRUE );
	}

	/**
	 * This function describes how the plugin is used.
	 *
	 * @return string
	 */
	public static function usage()
	{
		ob_start();
?>
Basic Example:
"{exp:ce_str:ing trim uppercase}Some text.  {/exp:ce_str:ing}"

Returns:
"SOME TEXT."

View the full documentation at http://www.causingeffect.com/software/expressionengine/ce-string for a complete list of all available methods, parameters, and examples.
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	} /* End of usage() function */

} /* End of class */