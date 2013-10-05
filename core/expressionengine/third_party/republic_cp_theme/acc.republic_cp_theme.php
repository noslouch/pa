<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Responsive CP Accessory
 *
 * @package		Republic CP Theme
 * @author		Republic Factory
 */


class Republic_cp_theme_acc
{
	var $name        = 'Republic CP Theme';
	var $id          = 'republic_cp_theme';
	var $version     = '1.0';
	var $description = 'Makes the Republic CP theme awesome';
	var $sections    = array();

	// --------------------------------------------------------------------

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	* Set Sections
	*/
	function set_sections()
	{
		// hide accessory
		$this->sections[] = '<script type="text/javascript">$("#accessoryTabs a.republic_cp_theme").parent().remove();</script>';

		// add viewport meta
		$this->EE->cp->add_to_head('<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1" />');

		// add retina js support
		$this->EE->cp->add_to_head('<script src="/themes/cp_themes/republic/js/retina.js"></script>');

	}

}

// END CLASS

/* End of file acc.republic_cp_theme.php */
/* Location: ./system/expressionengine/third_party/republic_cp_theme/republic_cp_theme.php */
