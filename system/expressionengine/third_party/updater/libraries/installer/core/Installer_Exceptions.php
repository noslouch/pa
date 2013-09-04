<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Installer_Exceptions Extends CI_Exceptions {

    public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        $message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

        // -----------------------------------------
        // Is this an AJAX request?
        // Lets return out own thing
        // -----------------------------------------
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) === TRUE &&  $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
        {
            $EE =& get_instance();

            $out = array();
            $out['success'] = 'no';
            $out['body'] = $message;
            $out['queries'] = $EE->db->queries;
            exit($this->generate_json(array('success' => 'no', 'body' => $message)));
        }

        // -----------------------------------------
        // Return Custom ERROR template
        // -----------------------------------------
        if (ob_get_level() > $this->ob_level + 1)
        {
            ob_end_flush();
        }
        ob_start();
        include(APPPATH.'errors/'.$template.'.php');
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    // --------------------------------------------------------------------

    private function generate_json($arr=array())
    {
        if (function_exists('json_encode') === FALSE)
        {
            if (class_exists('Services_JSON') == FALSE) include APPPATH.'libraries/JSON.php';
            $JSON = new Services_JSON();
            return $JSON->encode($arr);
        }

        return json_encode($arr);
    }

    // ********************************************************************************* //

}
// END Exceptions Class

/* End of file EE_Exceptions.php */
/* Location: ./system/expressionengine/libraries/EE_Exceptions.php */
