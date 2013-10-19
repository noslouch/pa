/* app/models/showcase.js - Showcase model */
'use strict';

define([
    'jquery',
    'backbone',
    'app/collections/covergallery'
], function( $, Backbone, CoverGallery ) {

    var Showcase = Backbone.Model.extend({
        defaults : {
            active : false
        },

        initialize: function(showcase, options){
            if ( showcase.type === 'gallery' ) {
                this.set({
                    gallery : new CoverGallery(showcase.images)
                })
            }

            this.url = function() {
                return options.path + '/' + this.get('url_title')
            }
        },

        activate : function(first){
            this.set('active', true)
            //var r = require( 'app/router' )
            //r.router.navigate(this.url(),  {replace : first })
            Backbone.dispatcher.trigger('navigate:showcase', { url : this.url(), replace : first })
        },

        deactivate : function(){
            this.set('active', false)
        }

    })

    return Showcase
})
