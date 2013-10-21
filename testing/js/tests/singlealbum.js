'use strict';

define([
    'backbone',
    'jquery',
    'app/views/showcaseviews',
    'app/views/singlealbum',
    'app/models/album',
],
    function( Backbone, $, S, albumView, AlbumModel ) {
        asyncTest( 'Valid JSON response', function() {
            var a = new AlbumModel()
            a.fetch({
                url : '/api/photography/thailand-2',
                success : function(model) {
                    start()
                    ok(model.get('title'), 'Model populated')
                    ok(model.get('gallery').length, 'Gallery populated')
                }
            })
        })
        test( 'Gallery Rendered into dom with photos', function() {
            stop()
            $('#qunit-fixture').html( albumView.render('thailand-2') )
            setTimeout( function() {
                ok( albumView.model.get('title'), 'Album view fetches data' )
                ok( $('#qunit-fixture').children('.viewer').length, 'Photo gallery rendered into dom' )
                ok( $('#showcaseContainer').children().length, 'Rendered Showcase' )
                start()
            }, 350 )
        })
    }
)
