/*global Swipe*/
/* utils/touchLoader.js
 * Loads Swipe for use as a lightbox */
'use strict';

define([
    'jquery',
    'swipe'
], function() {

    var maxWidth = window.innerWidth - 100,
        maxHeight = window.innerHeight - 100

    function gallery(selector, el){

        var $thumbs = $(selector),
            $slider = $('<div />').attr({
                id : 'slider',
                class : 'swipe'
            }),
            $wrap = $('<div />').addClass('swipe-wrap'),
            $lock = $('<div />').attr({
                id : 'fancybox-lock',
                class : 'fancybox-wrap'
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
            var slides = $wrap.find('img'),
                index = $('.fancybox').index(el)

            if ( slides[index].height > maxHeight ) { $(slides[index]).height(maxHeight) }
            else if ( slides[index].width > maxWidth ) { $(slides[index]).width(maxWidth) }

            window.s = new Swipe($slider[0], {
                callback : function(i){
                    if ( slides[i+1].height > maxHeight ) { $(slides[i+1]).height(maxHeight) }
                    else if ( slides[i+1].width > maxWidth ) { $(slides[i+1]).width(maxWidth) }
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

    function handleOrientation(){
        $('body').css('background' , 'blue')

    }

    function init() {
        $('.fancybox').click(function(e){
            e.preventDefault()
            gallery('.fancybox', e.currentTarget)
        })
        $('body').on('click', '.fancybox-wrap .close', function(e){
            e.preventDefault()
            window.s.close()
        })
    }

    window.addEventListener('deviceorientation', handleOrientation, true)

    return init
})
