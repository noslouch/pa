/**
 * jQuery code for extension settings
 *
 * @package			NsmLiveLook
 * @version			1.1.0
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2011 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */
jQuery(document).ready(function($) {
	$("#preview-urls").NSM_Cloneable({
		addTrigger: function(){return $(this).next().find(".add")},
		cloneTemplate: NSM_Live_Look.templates.$preview_url
	})
	.NSM_UpdateInputsOnChange();
});