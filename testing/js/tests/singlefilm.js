'use strict';

define([
    'backbone',
    'jquery',
    'app/views/showcaseviews',
    'app/views/singlefilm',
    'app/models/film'
],
    function( Backbone, $, S, filmView, FilmModel ) {
        test( 'Valid Film Model', function() {
            var f = new FilmModel()
            ok( f instanceof FilmModel, 'Valid' )
        })

        asyncTest( 'Valid JSON response', function() {
            $.get( '/api/film/peapod-on-the-today-show' ).done( function(d) {
                start()
                ok( d, 'Valid' )
            })
        })

        asyncTest( 'Valid Film Model ', function() {
            var f = new FilmModel()
            f.fetch({
                url : '/api/film/peapod-on-the-today-show',
                success : function(model) {
                    start()
                    ok( model.get('title'), 'Model populated' )
                    equal( typeof model.get('date'), 'object', 'Date object created' )
                }
            })
        })

        test( 'Video rendered into DOM', function() {
            stop()
            $('#qunit-fixture').html( filmView.render('peapod-on-the-today-show') )
            setTimeout( function() {
                ok( filmView.model.get('title'), 'Film view fetches data' )
                ok( $('#qunit-fixture').find('iframe').length, 'Video el renderd into DOM' )
                start()
            }, 350 )
        } )
    }
)
