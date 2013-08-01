"use strict";

function isoLoader(id) {
    var $id = $(id)
    var $img = $('.image.showcase img')

    $id.imagesLoaded( function(){ 
        $id.isotope({
            itemSelector: '.thumb',
            layoutMode : 'masonry',
            masonry : {
                gutterWidth: 7,
                columnWidth: 164
            },
            onLayout : function() { 
                $(this).css('overflow', 'visible')
           }
        })

        $img.addClass('loaded')
    })
}

function makeTime(date) {
    var res = []
    var d = new Date(date)
    res[0] = d.getFullYear()
    res[1] = d.getMonth() + 1
    res[2] = d.getDate()
    return res.join("-")
}

function fbBulletBuilder(){
    var $ul = $('<ul/>')
}

function fbLoader(){
    $('.mockFancybox').fancybox({
        padding: 0,
        type : 'image',
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
            wrap : '<div class="fancybox-wrap" tabIndex="-1"><h1 class="logo"><a href="/">Peter Arnell</a></h1><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div></div>'
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

