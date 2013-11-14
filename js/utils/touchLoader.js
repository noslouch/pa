/*global Swipe*/
/* utils/touchLoader.js
 * Loads Swipe for use as a lightbox */
'use strict';

define([
    'jquery',
    'swipe'
], function() {

    var maxWidth = window.innerWidth - 100,
        maxHeight = window.innerHeight - 125

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
            var slides = $wrap.find('img'),
                index = $('.fancybox').index(el),
                first = $(slides)[index]

            $(slides).not(first).css('display', 'none')
            $(slides).eq(index === slides.length - 1 ? 0 : index+1).css('display', 'block')
            $(slides).eq(index-1).css('display', 'block')

            //if ( $(first).height() > maxHeight ) { $(first).parent().height(maxHeight).width('auto') }
            //else if ( $(first).width() > maxWidth ) { $(first).parent().width(maxWidth).height('auto') }

            window.s = new Swipe($slider[0], {
                callback : function(i, el){
                    var ahead = i === slides.length - 1 ? slides[0] : slides[i+1],
                        behind = slides[i-1]

                    $(ahead).css('display','block')
                    $(behind).css('display','block')
                    $(slides[i-2]).css('display','none')
                    $(slides[i+2]).css('display','none')

                    //if ( $(ahead).height() > maxHeight ) { $(ahead).parent().height(maxHeight).width('auto') }
                    //else if ( $(ahead).width() > maxWidth ) { $(ahead).parent().width(maxWidth).height('auto') }
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
        maxWidth = window.innerWidth - 100
        maxHeight = window.innerHeight - 125
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
