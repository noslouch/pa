<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Super Search - Constants
 *
 * Central location for various values we need throughout the module.
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/super_search
 * @license		http://www.solspace.com/license_agreement
 * @version		2.0.6
 * @filesource	super_search/constants.super_search.php
 */
 
if ( ! defined('SUPER_SEARCH_VERSION'))
{
	define('SUPER_SEARCH_VERSION',	'2.0.6');
	define('SUPER_SEARCH_DOCS_URL',	'http://www.solspace.com/docs/super_search');
	define('SUPER_SEARCH_ACTIONS',	'save_search');
	define('SUPER_SEARCH_PREFERENCES',	'use_ignore_word_list|ignore_word_list|enable_search_log|enable_smart_excerpt|enable_fuzzy_searching|enable_fuzzy_searching_plurals|enable_fuzzy_searching_phonetics|enable_fuzzy_searching_spelling|third_party_search_indexes');
}

//	End file
