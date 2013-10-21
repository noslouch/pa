'use strict';

define([
    'backbone',
    'jquery',
    'app/collections/films',
    'app/views/film'
],
    function( Backbone, $, filmCollection, filmHome ) {
        asyncTest( 'Valid JSON Response', function() {
            filmCollection.fetch({
                success : function(collection) {
                    start()
                    ok( collection.length, 'Collection populated' )
                }
            })
        } )
        test( 'Blank Film View created', function() {
            ok( filmHome instanceof Backbone.View, 'Valid' )
        } )
        test( 'Created Film Grid', function(){
            filmHome.setElement('#qunit-fixture')
            filmHome.render()
            stop()
            setTimeout( function(){
                start()
            }, 350 )
            ok( filmHome.collection.length, 'View Collection populated' )
            ok( $('#qunit-fixture').children().length, 'Film Grid in DOM' )
        } )
    }
)
