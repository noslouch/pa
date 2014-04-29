<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include_once dirname(__FILE__).'/config.php';

/**
 * Channel Images for Editor
 *
 * @package         DevDemon_ChannelImages
 * @author          DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright       Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license         http://www.devdemon.com/license/
 * @link            http://www.devdemon.com/editor/
 */
class Channel_images_ebtn extends Editor_button
{
    /**
     * Button info - Required
     *
     * @access public
     * @var array
     */
    public $info = array(
        'name'      => 'Channel Images',
        'author'    => 'DevDemon',
        'author_url' => 'http://www.devdemon.com',
        'description'=> 'Use images uploaded through Channel Images in Editor',
        'version'   => CHANNEL_IMAGES_VERSION,
        'font_icon' => 'dicon-images',
    );

    /**
     * Constructor
     *
     * @access public
     *
     * Calls the parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    // ********************************************************************************* //
}

/* End of file editor.link.php */
/* Location: ./system/expressionengine/third_party/editor/editor.link.php */
