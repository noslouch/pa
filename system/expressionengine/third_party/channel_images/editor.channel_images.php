<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_images/config'.EXT;

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
        'callback'  => 'function(obj, event, key){
            if (typeof(ChannelImages) == "undefined"){
                alert("ERROR: No Channel Images field found");
                return;
            }
            ChannelImages.EditorOpenModal(obj, event, key);
        }',
        'button_css'    => 'background-position:center 5px; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYxIDY0LjE0MDk0OSwgMjAxMC8xMi8wNy0xMDo1NzowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNS4xIFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OENFNUNFQjU5MDg2MTFFMTg3MjBBRERDNUU5RjY4QzYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OENFNUNFQjY5MDg2MTFFMTg3MjBBRERDNUU5RjY4QzYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4Q0U1Q0VCMzkwODYxMUUxODcyMEFEREM1RTlGNjhDNiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo4Q0U1Q0VCNDkwODYxMUUxODcyMEFEREM1RTlGNjhDNiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PinFsXQAAAH1SURBVHjadFPJqlpBEC21necBcYjgH2QTEDfmE/IDomA+wJU4Iq5EBF34HSLJynXA5csnXNxpECVGEMQxfSqvOzfhWVB4u7v61DmnWkun0/l5vV5D9/ud3gqLxUJ2u/2H1WpNvnUmcLnf7+sNxOPx4AQo9prNZkICPNQ+9l6BvwpVVC6XyefzUSwWI7/fTx6Ph1wuF1UqFbpcLjQajRhQgdhsNmq3258EFgiHw0HJZJIBvF4vOZ3Of6gCKBqNMjjOa7UaAwpVFA6H+bICkPRIyuMzdIvH4wwAlkIILVUDAHm9XpNhGHS73TTV+XzOF8Biu90ycDAY/GuiApAm0fF4pMFgwJ2VNLMMMJGG0mKxoGKxyPsaAEaxJtktn8+ziYlEgiKRiDa11+sxMJqZGv9ZVKtVMo8pm81yZjIZSqVS7JEarTmE7PxN0vqoRoNfmAUzYRz0ut1uzRA5nU61bDEejz/L71Aul3tXKBS+oHsoFGLqiOVySavVijabDZVKJZZYr9eZJZoLqctA4X6/tymzMKrD4UCn04nfRyAQoN1uxx2Rw+Hww6uCvTZRjs6u5otiTCKdTjPg+XzWbwLR7Xa/K++EaUwCHiBmsxk9C1WjTVQfk8nkpdVq/Wo0GkE8pGeXoRv/Ed34v5r3ZtAnsZdpqMVvAQYAZJbisOd0oHoAAAAASUVORK5CYII=);',
        'button_css_hq' => 'background-position:center 5px; background-image: background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RDkzODEwOTMyN0FCMTFFMjg3OTI5MkI2NzQ3NTU1M0IiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RDkzODEwOTQyN0FCMTFFMjg3OTI5MkI2NzQ3NTU1M0IiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpEOTM4MTA5MTI3QUIxMUUyODc5MjkyQjY3NDc1NTUzQiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpEOTM4MTA5MjI3QUIxMUUyODc5MjkyQjY3NDc1NTUzQiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PvVRHDAAAAh0SURBVHja3FpLTxRZ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);',
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
