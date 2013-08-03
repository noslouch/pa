"use strict";

function checkScroll(){

    function inspect(){
        $('.header-wrapper').toggleClass('shadow', window.scrollY > 0)
        setTimeout(inspect, 150) 
    }

    setTimeout(inspect, 150) 
}

function checkQuoteHeight(){

    function inspect() {
        $('#quotes').toggleClass('small', $('#quotes').height() < 370)
        setTimeout(inspect, 150)
    }

    inspect()
}

function isoLoader(id) {
    var $id = $(id),
        $img = $('.image.showcase img'),
        showcase = document.getElementById('showcaseContainer'),
        rtl = $(showcase).hasClass('rtl'),
        fixed = $(showcase).hasClass('fixed')

    $id.imagesLoaded( function(){ 
        $id.isotope({
            transformsEnabled: !rtl,
            itemSelector: '.thumb',
            layoutMode : fixed ? 'masonry' : 'fitRows',
            masonry : {
                gutterWidth: 7,
                columnWidth: rtl ? 164*1.5 : 164
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
        margin: [75, 0, 75, 0],
        padding: 0,
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

function Spinner(){
    var self = this,
        $loader = $('<div/>').addClass('loader').attr('id', 'loader'),
        $t = $('<div/>').addClass('table'),
        $tc = $('<div/>').addClass('table-cell')

    $tc.append('<span/>')
    $t.append($tc)
    $loader.append($t)

    this.detach = function() {
        $loader.detach()
    }

    this.append = function() {
        $('body').append($loader)
    }

    this.append()

}
