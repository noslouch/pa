/* app/views/page.js - Generic Page View/Controller
 * outer most appviews */
'use strict';

define([
    'exports',
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcaseviews'
], function( exports, $, Backbone, _, S ) {

    var Page = Backbone.View.extend({
        initialize: function(options) {
            // initialized as:
            //  el : .page
            //  model : app.model
            //  options.parent : app
            // shares model with app
            _.bindAll( this, 'render' )
            this.outlineTitle = $('<h2/>').addClass('visuallyhidden').attr( 'id', 'title' )
            this.listenTo( this.model, 'change:page', this.render )
        },

        semantics : function( className, outlineTitle ) {
            this.$el.addClass( className || '' )
            this.outlineTitle.html( outlineTitle || '' )
            this.$el.prepend( this.outlineTitle )
        },


        render : function(appModel, page, filtering) {
            // called in response to change:page event
            // args:
            //   app.model
            //   current page, View instance
            //   boolean - deprecated

            // what is this used for ?
            this.model.set('view')
            //

            this.$el.html( page.render() )
            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )
        }
    })
    return Page
})
