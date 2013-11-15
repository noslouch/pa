/*global Swipe*/
/* utils/touchLoader.js
 * Loads Swipe for use as a lightbox */
'use strict';

define([
    'utils/spinner',
    'jquery',
    'swipe',
    'utils/orientation'
], function( Spinner ) {

    function gallery(selector, el){

        var $thumbs = $(selector),
            $slider = $('<div />').attr({
                id : 'slider',
                class : 'swipe'
            }),
            $wrap = $('<div />').addClass('swipe-wrap'),
            $lock = $('<div />').attr({
                id : 'fancybox-lock',
                class : 'fancybox-wrap fancybox-default fancybox-type-image fancybox-open'
            }),
            $close = $('<a />').attr({
                'title' : 'Close',
                class : 'close'
            }).append('<span/>').appendTo($lock),
            $overlay = $('<div />').css({
                'background-color' : 'white',
                'opacity' : 1
            }).addClass('fancybox-overlay fancybox-overlay-fixed fancybox-default-overlay')

        $slider.append($wrap).appendTo($lock)

        $thumbs.each(function(idx, el){
            var img = document.createElement('img'),
                slide = document.createElement('div'),
                innerWrap = document.createElement('div')

            img.src = el.href
            $(innerWrap).append(img).addClass('inner-wrap').appendTo(slide)
            $wrap.append(slide)
        })

        $('html').addClass('fancybox-margin fancybox-lock')
        $('body').append($lock).append($overlay)
        $wrap.find('img').imagesLoaded(function(){
            var slides = $('.swipe-wrap > div'),
                index = $('.fancybox').index(el),
                first = $(slides)[index]

            $(first).find('img').css('display', 'block')
            window.spinner.detach()

            window.s = new Swipe($slider[0], {
                beforeMove : function(nextIndex, nextEl, prevEl){
                    $(nextEl).find('img').css('display','block')
                    $(prevEl).find('img').css('display','block')
                },
                beforeLoad: function(){
                    console.log('before load')
                    if (!window.loaded) {
                        var $ul = $('<ul/>'),
                            $bullet = $('<div/>').attr('id', 'bullet-wrap').addClass('indicators'),
                            $dot = $('<div/>').attr('id','dot').addClass('dot'),
                            $logo = $('<h1/>').addClass('logo')

                        $logo.html( $('<a/>').attr('href', '/').text('Peter Arnell') )

                        $ul.appendTo($bullet)
                        $ul.append($dot)

                        for (var i = 0; i < slides.length; i++) {
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
                            $(slides[this.id]).find('img').css('display', 'block')
                            window.s.slide( this.id, 150 )
                        })
                    }
                    window.loaded = true
                },
                beforeChange : function(i, el){
                },
                transitionEnd: function(i, el){
                    // el is incoming slide
                    $(slides).not(el).find('img').css('display','none')
                },
                callback : function(i, el){
                    $('#bullet-wrap li').removeClass('active-slide')
                    var p = $('li#'+i).addClass('active-slide').position()
                    $('#dot').animate({
                        left: p.left
                    })
                },
                startSlide : index
            })

            window.s.close = function(){
                window.loaded = false
                $('#bullet-wrap').fadeOut(150)
                $('#fancybox-lock .logo').fadeOut(150)
                $('#fancybox-lock').remove()
                $('html').removeClass('fancybox-margin fancybox-lock')
                $('.fancybox-overlay-fixed').remove()
            }
        })

    }

    function handleOrientation(e){
        //window.s.setup()
        console.log('orientation change')
    }

    function init() {
        $('.fancybox').click(function(e){
            window.spinner = new Spinner()
            e.preventDefault()
            gallery('.fancybox', e.currentTarget)
        })
        $('body').on('click', '.fancybox-wrap .close', function(e){
            e.preventDefault()
            window.s.kill()
            window.s.close()
        })
    }

    window.addEventListener('orientationchange', handleOrientation, true)

    return init
})
