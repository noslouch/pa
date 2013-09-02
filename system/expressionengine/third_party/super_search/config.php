<?php if ( ! defined('EXT')) exit('No direct script access allowed');

/**
 * Super Search - Config
 *
 * NSM Addon Updater config file.
 *
 * @package		Solspace:Super Search
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2009-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/super_search
 * @license		http://www.solspace.com/license_agreement
 * @version		2.0.6
 * @filesource	super_search/config.php
 */

require_once 'constants.super_search.php';

$config['name']									= 'Super Search';
$config['version']								= SUPER_SEARCH_VERSION;
$config['nsm_addon_updater']['versions_xml'] 	= 'http://www.solspace.com/software/nsm_addon_updater/super_search';
