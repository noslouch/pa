'use strict';

define([
    'backbone',
    'jquery',
    'app/collections/covergallery',
    'app/collections/photography',
    'app/views/showcaseviews'
],
    function( Backbone, $, CoverGallery, photoCollection, S ) {
        asyncTest( 'Valid JSON Response', function() {
            photoCollection.fetch({
                success : function( collection ) {
                    start()
                    ok( collection.length, 'Collection populated' )
                }
            })
        } )
        asyncTest( 'Created image showcase', function() {
            photoCollection.fetch({
                success : function(collection) {
                    start()
                    var photoView = new Backbone.View({ el : '#qunit-fixture' })
                    photoView.$el.html( new S.Image({
                        cover : true,
                        collection : new CoverGallery( collection.pluck( 'coverImage' ) ),
                        path : 'photography',
                        model : new Backbone.Model()
                    }).render() )
                    ok( $('#qunit-fixture').find('img').length, 'Showcase in Dom' )
            } })
        })

    }
)
