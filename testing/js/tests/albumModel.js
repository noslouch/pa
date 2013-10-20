'use strict';

define(
    [ 'app/models/album', 'jquery' ],
    function( PhotoAlbum, $ ){
        asyncTest('Valid JSON response', function() {
            $.get('/api/photography/thailand-2').done(function(d) {
                start()
                ok( d, 'Got Data' )
            })
        })
        asyncTest('Album Created', function() {
            var a = new PhotoAlbum()
            a.fetch({
                url : '/api/photography/thailand-2',
                success : function( model ) {
                    start()
                    ok( model.get('url-title'), 'Album Created' )
                    ok( model.get('gallery').length, 'Gallery populated' )
                    equal( typeof model.get('htmlDate'), 'string', 'HTML date created' )
                    equal( typeof model.get('date').year(), 'number', 'can create a year' )
                }
            })
        })
    }
)
