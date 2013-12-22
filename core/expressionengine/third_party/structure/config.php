<?php

// Global Contants
if ( ! defined('STRUCTURE_NAME')) define('STRUCTURE_NAME', 'Structure');
if ( ! defined('STRUCTURE_SHORT_NAME')) define('STRUCTURE_SHORT_NAME', 'structure');
if ( ! defined('STRUCTURE_DESCRIPTION')) define('STRUCTURE_DESCRIPTION', 'Create pages, generate navigation, manage content through a simple interface and build robust sites faster than ever.');
if ( ! defined('STRUCTURE_VERSION')) define('STRUCTURE_VERSION', '3.3.13');
if ( ! defined('STRUCTURE_DOCS')) define('STRUCTURE_DOCS', 'http://buildwithstructure.com/documentation');


$config['name'] = STRUCTURE_NAME;
$config['version'] = STRUCTURE_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://buildwithstructure.com/versions-ee2.xml';