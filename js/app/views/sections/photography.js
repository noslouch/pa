/* app/views/sections/photography.js - Main Photography page */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/collections/covergallery',
    'app/collections/photography',
    'app/views/showcases/gallery'
], function( $, Backbone, _, CoverGallery, photoCollection, G ) {

    var Photography = Backbone.View.extend({
        initialize : function(){
            _.bindAll( this, 'render', 'navigate' )
            var self = this

            this.collection.fetch({
                success : function(collection) {
                    self.covers = new G({
                        cover : true,
                        collection : new CoverGallery( collection.pluck( 'coverImage' ) ),
                        path : 'photography',
                        model : new Backbone.Model()
                    })

                    Backbone.dispatcher.trigger('photography:ready')
                }
            })
        },

        events : {
            'click .showcase a' : 'navigate'
        },

        render : function() {
            this.$el.html( this.covers.render() )
        },

        onClose : function(){
            $('.page').removeClass('photography')
        },

        init : function(spinner) {
            if ( !this.collection.length ) {
                throw {
                    message : 'Photos aren\'t loaded.',
                    type : 'EmptyCollection'
                }
            }

            if (spinner){spinner.detach()}
            this.render()
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate:detail', e, this)
        }
    })

    return new Photography({
        model : new Backbone.Model(),
        collection : photoCollection
    })
})
