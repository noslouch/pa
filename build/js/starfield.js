"use strict";

var PA = PA || {}

;(function(app){

    var SCREEN_WIDTH = window.innerWidth,
    SCREEN_HEIGHT = window.innerHeight,
    HALF_WIDTH = window.innerWidth / 2,
    HALF_HEIGHT = window.innerHeight / 2,
    imageLimit = SCREEN_WIDTH < 320 ? 12 : 48,
    // images = app.randomCovers() // should return a Backbone collection of cover images
    images = []

    function randomRange(min, max) {
        return ((Math.random()*(max-min)) + min)
    }

    var $container = $('<div>').addClass('starfield').attr('id','starfield')
    function stars(){
        app.starsRunning = true

        /*
        images.forEach( function(image) {
            image.$el.css({
                left : HALF_WIDTH + randomRange(-HALF_WIDTH, HALF_WIDTH),
                top : HALF_HEIGHT + randomRange(-HALF_HEIGHT, HALF_HEIGHT)
            })
        }

        function stagger() {
            var i = 0;

            function go() {
                var $a = $('<a/>').attr('href', images.models[i].get('url')).append(images.models[i].el).addClass('fast')
                $a.appendTo($container)
                setTimeout(go, 550)
                i++
            }

            go()
        }
        */

        $container.on('finished', function(e){
            stagger()
        })

        function stagger(){
            var i = 0;

            function go(){
                var $a = $('<a/>').attr('href', '#').append(images[i]).addClass('fast')
                $a.appendTo($container)
                i++
                if (i < images.length) {
                    setTimeout(go, 550)
                }
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

    function starDestroy() {
        $container.remove()
        app.starsRunning = false
    }

    app.starInit = stars
    app.starDeath = starDestroy
    app.starsRunning = false

    return app
}(PA))
