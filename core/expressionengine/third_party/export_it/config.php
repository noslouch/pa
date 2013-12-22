<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 /**
 * mithra62 - Export It
 *
 * @package		mithra62:Export_it
 * @author		Eric Lamb
 * @copyright	Copyright (c) 2012, mithra62, Eric Lamb.
 * @link		http://mithra62.com/projects/view/export-it/
 * @since		1.1
 * @filesource 	./system/expressionengine/third_party/export-it/
 */
$config['name'] = 'Export It'; 
$config['class_name'] = 'Export_it'; 
$config['settings_table'] = 'export_it_settings'; 
$config['description'] = 'Allows for the ExpressionEngine data to be exported into various formats.';

$config['mod_url_name'] = strtolower($config['class_name']);
$config['ext_class_name'] = $config['class_name'].'_ext';

$config['version'] = '1.3.1';
//$config['nsm_addon_updater']['versions_xml'] = 'http://mithra62.com/export-it.xml';
$config['docs_url'] = 'http://mithra62.com/docs/export-it';