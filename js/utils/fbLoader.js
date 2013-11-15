/* utils/fbLoader.js
 * fancybox init script. sets up special bullet animation and other call backs */
'use strict';

define([
    'jquery',
    'fancybox'
], function( $ ) {

    function fbLoader(){
        $('.fancybox').fancybox({
            margin: 75,
            padding: 0,
            scrolling: 'no',
            type : 'image',
            closeClick: true,
            openEffect : 'fade',
            closeEffect : 'fade',
            nextEffect : 'fade',
            prevEffect : 'fade',
            overlay : {
                css : {
                    'background' : 'white',
                    'opacity' : 1
                }
            },
            caption : {
                type : "outside"
            },
            tpl : {
                wrap : '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-inner"></div></div>',
                closeBtn : '<a title="Close" class="close" href="javascript:;" id="fb-close"><span></span></a>'
            },
            afterLoad : function() {
                if (!window.loaded) {
                    var $ul = $('<ul/>'),
                        $bullet = $('<div/>').attr('id', 'bullet-wrap').addClass('indicators'),
                        $dot = $('<div/>').attr('id','dot').addClass('dot'),
                        $logo = $('<h1/>').addClass('logo')

                    $logo.html( $('<a/>').attr('href', '/').text('Peter Arnell') )

                    $ul.appendTo($bullet)
                    $ul.append($dot)

                    for (var i = 0; i < this.group.length; i++) {
                        var $li = $('<li/>').attr('id', i)
                        if (i === 0) {
                            $li.addClass('active-slide')
                        }
                        var $a = $('<a/>')
                        $li.append($a)
                        $ul.append($li)
                    }

                    $('#fancybox-lock').append($logo).append($bullet)

                    $('#bullet-wrap li').click(function(){
                        $.fancybox.jumpto(this.id)
                    })
                }
                window.loaded = true
            },
            beforeShow : function() {
                $('#bullet-wrap li').removeClass('active-slide')
                var p = $('li#'+this.index).addClass('active-slide').position()
                $('#dot').animate({
                    left: p.left
                })
            },
            afterShow : function() {
                if (!this.count) {
                    // only run once
                    $('#bullet-wrap').prepend($('.fancybox-nav'))
                    this.count = 1
                }
            },
            beforeClose : function(){
                window.loaded = false
                $('#bullet-wrap').fadeOut(150)
                $('#fancybox-lock .logo').fadeOut(150)
            }
        })
    }

return fbLoader

})
