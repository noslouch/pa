<?php

/*
From EE 2.6 onward we need to use the ee() method
 */
if (function_exists('ee') === false)
{
    function ee()
    {
        /*
        static $EE;
        if ( ! $EE) $EE = get_instance();
        return $EE;
        */
        return get_instance();
    }
}

/*
In EE 2.1.0 CodeIgniter still used the Controller class, they switched to CI_Controller in EE 2.2.0
 */
if (class_exists('CI_Controller') === false) {

    class CI_Controller extends Controller {

    }
}

/*
In EE 2.1.0 CodeIgniter still used the instantiate_class() method in the DB.php file, this was removed in EE 2.2.0
 */
if (function_exists('instantiate_class') === false) {

    function instantiate_class($obj) {
        return $obj;
    }
}


/*
Don't remember for what this is..
 */
if (defined('EE_APPPATH') == false) define('EE_APPPATH', APPPATH);
require_once(EE_APPPATH.'/libraries/Layout'.EXT);
class EE_Layout extends Layout {
    // Nothing to see here.
}
