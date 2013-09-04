<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// When people are updating EE with Updater sometimes the process will fail after copying the installer file.
// People tend to refresh the page which will turn up a 404 page error.
// This is because EE is overriding the config file with $assign_to_config, so our 404 route will fail
// Since this file is inclused just before that error is displayed, we can override config stuff here.
$CFG->set_item('enable_query_strings', FALSE);
$CFG->set_item('controller_trigger', '');
$CFG->set_item('function_trigger', '');
$CFG->set_item('directory_trigger', '');
$RTR->directory = '';

/*
class Installer_Controller extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

}
*/
