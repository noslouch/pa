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
            //nextEffect : 'fade',
            //prevEffect : 'fade',
            helpers : {
                overlay : {
                    css : {
                        'background' : 'white'
                    }
                },
                title : {
                    type : "outside"
                }
            },
            tpl : {
                wrap : '<div class="fancybox-wrap" tabIndex="-1"><h1 class="logo"><a href="/">Peter Arnell</a></h1><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div></div>',
                closeBtn : '<a title="Close" class="close" href="javascript:;"><span></span></a>',
            },
            afterLoad : function() {
                if (!window.loaded) {
                    var $ul = $('<ul/>')
                    var $bullet = $('<div/>').attr('id', 'bullet-wrap').addClass('indicators')
                    var $dot = $('<div/>').attr('id','dot').addClass('dot')

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
                    $('.fancybox-overlay').append($bullet)

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
            beforeClose : function(){ window.loaded = false }
        })
    }

return fbLoader

})
