<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (isset($this->EE) == FALSE) $this->EE =& get_instance(); // For EE 2.2.0+

// Defaults
$config['tagger_defaults']['show_most_used'] = 'yes';
$config['tagger_defaults']['single_field'] = 'no';
$config['tagger_defaults']['urlsafe_seperator'] = 'plus';
$config['tagger_defaults']['lowercase_tags'] = 'yes';
$config['tagger_defaults']['auto_assign_group'] = '0';


