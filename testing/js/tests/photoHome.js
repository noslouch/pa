'use strict';

define(
    [ 'backbone', 'jquery', 'app/collections/photography', 'app/views/showcaseviews' ],
    function( photoCollection, S, $ ) {
        asyncTest( 'Valid JSON Response', function() {
            photoCollection.fetch({
                success : function( collection ) {
                    start()
                    ok( collection.length, 'Collection populated' )
                }
            })
        } )
        asyncTest( 'Created image showcase', function() {
            photoCollection.fetch({ success : function() { start () })
            var photoView = new Backbone.View({ el : '#qunit-fixture' })
            photoView.$el.html( new S.Image({
                cover : true,
                collection : new CoverGallery( photoCollection.pluck( 'coverImage' ),
                path : 'photography',
                modle : 

    }
)
