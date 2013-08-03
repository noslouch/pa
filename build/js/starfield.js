"use strict";

var SCREEN_WIDTH = window.innerWidth,
SCREEN_HEIGHT = window.innerHeight,
HALF_WIDTH = window.innerWidth / 2,
HALF_HEIGHT = window.innerHeight / 2,
imageLimit = SCREEN_WIDTH < 320 ? 12 : 48,
images = []

function randomRange(min, max)
{
    return ((Math.random()*(max-min)) + min)
}

function stars(){
    var $container = $('<div>').addClass('starfield').attr('id','starfield')

    $container.on('finished', function(e){
        stagger()
    })

    function stagger(){
        var i = 0;

        function go(){
            var $a = $('<a/>').attr('href', '/templates/project-single.php').append(images[i]).addClass('fast')
            $a.appendTo($container)
            setTimeout(go, 550)
            i++
        }

        go()
    }

    for (var i = 0; i < imageLimit; i++){
        images[i] = new Image()
        images[i].src = '/assets/img/mock-starfield/' + (i%8+1) + '.png'
        $(images[i]).css({
            left : HALF_WIDTH + randomRange(-HALF_WIDTH, HALF_WIDTH),
            top : HALF_HEIGHT + randomRange(-HALF_HEIGHT, HALF_HEIGHT)
        })

        if (i === imageLimit - 1) { $container.trigger('finished') }
    }

    $('.outer-wrapper').after($container)
}

$(function(){
    stars()
})

