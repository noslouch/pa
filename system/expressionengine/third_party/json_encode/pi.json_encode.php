<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	$plugin_info = array(
		'pi_name' => 'JSON Encode',
		'pi_version' => '1.0',
		'pi_author' => 'Noble Studios',
		'pi_author_url' => 'http://noblestudios.com/',
		'pi_description' => 'Returns the input string in JSON encoded form.',
		'pi_usage' => Json_encode::usage()
	);

	class Json_encode {

		public $return_data = "";

		public function __construct() {
			$this->EE =& get_instance();
			$options = ($this->EE->TMPL->fetch_param('options')) ? $this->EE->TMPL->fetch_param('options') : 0;
			$this->return_data = json_encode($this->EE->TMPL->tagdata, $options);
		}

		function usage() {
			ob_start(); 
			include "usage.php";
			$buffer = ob_get_contents();
			ob_end_clean();
			return $buffer;
		}

	}

?>