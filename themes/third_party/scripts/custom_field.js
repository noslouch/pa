/**
 * Changes height of iFrame and stores the value in a cookie
 *
 * @package			NsmLiveLook
 * @version			1.2.4
 * @author			Leevi Graham <http://leevigraham.com> - Technical Director, Newism
 * @link			http://github.com/newism/nsm.live_look.ee-addon
 * @copyright 		Copyright (c) 2007-2013 Newism <http://newism.com.au>
 * @license 		Commercial - please see LICENSE file included with this distribution
 */

(function($) {

	// plugin definition
	$.fn.NSM_Live_Look_Preview = function(options) {
		var opts = $.extend({}, $.fn.NSM_Live_Look_Preview.defaults, options);
		return this.each(function() {
			var $self = $(this);
			$.NSM_Live_Look_Preview.init($self);
		});
	};

	$.NSM_Live_Look_Preview = {
		init: function($iframe_wrap) {
			var $iframe = $('iframe', $iframe_wrap);
			var h = parseInt( $iframe.css('height') );
			var id = String($iframe.attr('id')).substring(14);
			
			var heights = $.NSM_Live_Look_Preview.getCookieHeights();
			if(heights[ id ] > 0){
				$iframe.height( heights[ id ] );
			}
			
			$('.enlarge-iframe', $iframe_wrap).bind('click', function() {
				h = parseInt(h) + 100;
				$iframe.height(h);
				$.NSM_Live_Look_Preview.updateCookie(id, h);
				return false;
			});

			$('.shrink-iframe', $iframe_wrap).bind('click', function() {
				h = parseInt(h);
				if(h > 200) {
					h = h - 100;
					$iframe.height(h);
					$.NSM_Live_Look_Preview.updateCookie(id, h);
				}
				return false;
			});
		},
		getCookieHeights: function() {
			var cookie = String($.cookie('nsm_live_look_heights'));
			var heights = $.NSM_Live_Look_Preview.stringToObject(cookie);
			return heights;
		},
		updateCookie: function(id, h) {
			var heights = $.NSM_Live_Look_Preview.getCookieHeights();
			heights[ id ] = h;
			var new_cookie = $.NSM_Live_Look_Preview.objectToString(heights);
			$.cookie('nsm_live_look_heights', new_cookie, { expires: 7 });
		},
		stringToObject: function(str) {
			var output = {};
			if(str == ""){
				return output;
			}
			var split_str = String( decodeURIComponent(str) ).split('|');
			for(var i=0,m=split_str.length; i<m; i+=1){
				var kv = String(split_str[i]).split(':');
				if(kv[0] !== ''){
					output[ kv[0] ] = kv[1];
				}
			}
			return output;
		},
		objectToString: function(object_str) {
			var output = "";
			var count = 0;
			for(var i in object_str){
				if(i !== 'null'){
					if(count > 0){
						output += '|';
					}
					output += (i+":"+object_str[i]);
					count += 1;
				}
			}
			return encodeURIComponent(output);
		}
	};

	$.fn.NSM_Live_Look_Preview.defaults = {

	};

})(jQuery);

$(function(){
	$("div.iframe-wrap").NSM_Live_Look_Preview();
});
