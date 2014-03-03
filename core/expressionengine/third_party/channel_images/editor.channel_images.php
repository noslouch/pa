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
        'callback'  => 'function(buttonName, buttonDOM, buttonObject){

            var redactor = this;

            if (typeof(ChannelImages) == "undefined"){
                alert("ERROR: No Channel Images field found");
                return;
            }

            ChannelImages.EditorOpenModal(redactor, buttonName, buttonDOM, buttonObject);
        }',
        'button_css'    => 'background-position:center 5px; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABWklEQVQ4T63TTSjEQRjH8V0l5DWWHCRxUC4iJXLkovbiInHzckDeypkLFw5WLpKXyFXaUjipPe6FKEUJB+WlSOLAwfdX89TYUFpTn2bm+c88/2dm/xsMJNmCSe4PWIIqElUg5BR54wJvPM141n+pJXgmuIUHvOESN7jHnYvX0/eh47sE1wTLsIsWt+Cd/gVPeEUKCnGIRxxhyipQsBkxtOEUuUhDlqNxPnKgY0X03BLsMRnDPmpx65fpxo30O5jDPM4RsgTrTNawjWKoZLUaHOMDYUSxikkcoNwS6GZPsAiVqjaABayg28VK6FVdJTZRbQnGmYwgHTpfJ1SVLk5tCaU4wxAaMIMmS9DFZANXGIR+0lS3ObHrJ3CBYbRagkwmvZiAjpDxw2aFdR86Vh7a/U+5jkD8l42Jj5YJ9PgJdP5R6A6y3RvsG1CvNyouqlJrI//2Z/pD5V+XfgL2ED0R3xu/5AAAAABJRU5ErkJggg==);',
        'button_css_hq' => 'background-position:center 0; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADUAAAAyCAYAAAD845PIAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpENkFGMDc4ODgzODBFMzExQkVGMzhCNDE2REY0MDFGQyIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo1NDc1NzU3RDgwOTYxMUUzQjhGNEJEQ0FFRjY3MEY0QiIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo1NDc1NzU3QzgwOTYxMUUzQjhGNEJEQ0FFRjY3MEY0QiIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M2IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkQ3QUYwNzg4ODM4MEUzMTFCRUYzOEI0MTZERjQwMUZDIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkQ2QUYwNzg4ODM4MEUzMTFCRUYzOEI0MTZERjQwMUZDIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+exLwZAAAAuRJREFUeNrsmd+LTVEUx+/BjBkz7txxZ8YlGtNNkx9F8WIoHlGIdAsPHkZ4kH9ESX4kiSTUXMUDRbwpD0qmFK/Ij8wdmnEvMjKu76rvyW537pw658w+GmvVp3X2nnP32d+z11pnnzNevV7PzDSblZmBpqJUlIpSUSpKRakoFaWiAm1O3AFKpZLZ9EAXyRvHPVP0PQeby+Xyr9REQcQ6uN2cVLc10XyE1R8A/eBFmit1nwJs+wE+gE9ghF4YJRWr7zO4AA6DQtqi5oIJcBC84WQ7+bdv5CsYB2Evay/pF6adU7IKC5ADQwzHA3DXGpz7nVSJL/oLhff9jepSgX1j9HL+U1xnwpWoIibRjAv+xPEW9k9yIq2ghX3zSFfImLuIbY+kiLgS5bFQvANZ9h8CV4xHRQdop6h2tlvZ7jQE54nflvAuchU3uAw/Pw9E1Hy23xrn/GYYjYUNhtUOqrC9cK8Zhk5EfaQv0PsrVY0wVj8ESD6uAtfBUYicZChHHTPSjmLEqlhxRJ0G6xmWEr77ExgzkqgKfQ+9H361CGN1N2jHGTORlQqbwF5w1rgJpp0znmXyQB5KQlQSOdXBwhCU1IPgIqullOZNfEb5dgk8A8vAY+RTJYnwiyJq1A8/JPlSjlEN2D1sB+cpSGw1uAr2GIVAqt8w3LD1W7fhh0nUuEtYBC43uKOyIjdBk9W/E5zi8UrwBDfmNmhKUlTUVw/JqxXEvvhacIcP0iA7BrYBWeVm9p2Rcm6c47z6mcUiY118OXfxuZDfFw1BYkewWsdTCz/a+wBRS8DDGDvukxC2Na1CIXaC4bUYrOF+7QHojfHGMFtKOoQNxF0pL87/pzCBfXA3Ev7E8IovmxvBDhSmu66/UWSn4btJn/Ge5bRQ+NYyzR+GammIuhdQNJK0cec5xbzyWMLNF8Icj9sYolmjnaNvm+JcGfOWDI+cqjsX9S+afnZWUSpKRakoFaWiVJSK+n9E/RFgAEhQv3oqKZfzAAAAAElFTkSuQmCC);',
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
