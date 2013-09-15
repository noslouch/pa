/* app/views/chromeviews.js
 * outer most appviews */
'use strict';

define([
    'exports',
    'jquery',
    'backbone',
    'underscore',
    'app/views/showcaseviews'
], function( exports, $, Backbone, _ ) {

    var PageView = Backbone.View.extend({
        initialize: function() {
            _.bindAll( this, 'render' )

            this.outlineTitle = $('<h2/>').addClass('visuallyhidden').attr( 'id', 'title' )
            this.$el.prepend(this.outlineTitle)

            //this.listenTo( this.model, 'change:project', this.render )

            this.listenTo( this.model, 'change:page', this.render )
        },

        semantics : function( className, outlineTitle ) {
            this.$el.addClass( className || '' )
            this.outlineTitle.html( outlineTitle || '' )
            this.$el.prepend( this.outlineTitle )
        },

        pageRender : function( pageModel, newPageView ) {
            this.$el.html( newPageView.render() )
            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )
        },

        render : function(pageModel, pageView, filtering) {

            this.$el.html( pageView.render() )
            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )

            require(['app/views/showcaseviews'], function(S) {
                if ( pageModel.get('page') instanceof S.Image ) {
                    console.log('instanceof S.Image: loading isotope')

                    pageModel.get('page').firstLoad()
                    if ( !filtering ) {
                        pageModel.set('filter', '*')
                    }
                } else if ( pageModel.get('page') instanceof S.List ) {
                    console.log('instanceof S.List: sorting by name')

                    //pageModel.get('showcase').set( 'sort', 'alpha' )
                    pageModel.set('sort','alpha')
                }
            })
        }

    })

    return PageView
})
