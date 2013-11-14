/*global Swipe*/
/* utils/touchLoader.js
 * Loads Swipe for use as a lightbox */
'use strict';

define([
    'utils/spinner',
    'jquery',
    'swipe'
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
                },
                beforeChange : function(i, el){
                },
                transitionEnd: function(i, el){
                    // el is incoming slide
                    $(slides).not(el).find('img').css('display','none')
                },
                callback : function(i, el){
                },
                startSlide : index
            })

            window.s.close = function(){
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
