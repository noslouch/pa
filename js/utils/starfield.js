/* DEPRECATED
 * *********************************************************************
"use strict";

var PA = PA || {}

;(function(app){

    var SCREEN_WIDTH = window.innerWidth,
    SCREEN_HEIGHT = window.innerHeight,
    HALF_WIDTH = window.innerWidth / 2,
    HALF_HEIGHT = window.innerHeight / 2
    //imageLimit = SCREEN_WIDTH < 320 ? 12 : 48

    function randomRange(min, max) {
        return ((Math.random()*(max-min)) + min)
    }

    app.randomCovers = function() {
        var randomCoverModels = app.coverImages.shuffle()
        var coverCollection = new Backbone.Collection()

        _.each( randomCoverModels, function(cover, idx, list) {
            coverCollection.add( cover )
        } )

        return coverCollection
    }

    PA.StarThumb = Backbone.View.extend({
        tagName : "a",
        initialize : function() {
            _.bindAll( this, 'render' )

            this.$el.append( $('<img>') )
            this.$('img').css({
                left : HALF_WIDTH + randomRange(-HALF_WIDTH, HALF_WIDTH),
                top : HALF_HEIGHT + randomRange(-HALF_HEIGHT, HALF_HEIGHT)
            }).attr( 'src', this.model.get('thumb') )
        },
        render : function() {

            this.$el
                .attr( 'href', '/projects/' + this.model.get('url') )
                .addClass( 'fast' )

            return this.el
        }

    })

    var $container = $('<div>').addClass('starfield').attr('id','starfield')
    function stars(){
        app.starsRunning = true

        var images = app.randomCovers() // should return a Backbone collection of cover images

        function stagger() {
            var i = 0;

            function go() {
                $container.append( new PA.StarThumb({ model : images.models[i] }).render() )
                i++

                // CHANGE TO IMAGELIMIT WHEN PROJECTS INCREASE
                if ( i < images.length ) {
                    setTimeout(go, 550)
                }
            }

            go()
        }

        $('.outer-wrapper').after($container)

        stagger()
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
*/
