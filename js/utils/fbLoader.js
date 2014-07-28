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
            arrows : false,
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
                closeBtn : ''
            },
            afterLoad : function() {
                var $bullets = $('#bullet-wrap'),
                    $close = $('<a title="Close" class="close" href="javascript:;" id="fb-close"><span>X</span></a>')

                if (!$bullets.length) {
                    $bullets = $('<div/>').attr('id', 'bullet-wrap').addClass('indicators')

                    var $ul = $('<ul/>'),
                        $dot = $('<div/>').attr('id','dot').addClass('dot'),
                        $logo = $('<h1/>').addClass('logo'),
                        $next = $('<a/>').addClass('fancybox-nav fancybox-next').append('<span/>').attr('id', 'next'),
                        $prev = $('<a/>').addClass('fancybox-nav fancybox-prev').append('<span/>').attr('id', 'prev')

                    $logo.html( $('<a/>').attr('href', '/').text('Peter Arnell') )

                    $bullets.append($prev).append($next).append($ul)
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

                    $('#fancybox-lock').append($logo).append($bullets).append($close)

                    $('#bullet-wrap li').click(function(){
                        $.fancybox.jumpto(this.id)
                    })
                    $('#next').click(function(){ $.fancybox.next() })
                    $('#prev').click(function(){ $.fancybox.prev() })
                    $('#fb-close').click($.fancybox.close)
                }
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
                $('#fancybox-lock .logo, #fb-close, #bullet-wrap').fadeOut(150)
            }
        })
    }

return fbLoader

})
