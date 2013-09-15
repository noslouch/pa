/* app/views/pageviews.js - Page Views */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcaseviews'
], function( $, Backbone, _, S ) {

    var PageView = Backbone.View.extend({
        initialize: function() {
            _.bindAll( this, 'render' )

            this.outlineTitle = $('<h2/>').addClass('visuallyhidden')
            this.$el.prepend(this.outlineTitle)

            this.listenTo( this.model, 'change:showcase', this.render )
        },

        semantics : function( className, outlineTitle ) {
            this.$el.addClass( className || '' )
            this.outlineTitle.html( outlineTitle || '' )
            this.$el.prepend( this.outlineTitle )
        },

        render : function(pageModel, pageView) {

            this.$el.html( pageView.render() )
            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )

            if ( pageView instanceof S.Image ) {
                pageView.firstLoad()
                pageModel.set('filter', '*')
            } else if ( pageView instanceof S.List ) {
                pageModel.set( 'sort', 'alpha' )
            }

        }

    })

    return PageView
})
