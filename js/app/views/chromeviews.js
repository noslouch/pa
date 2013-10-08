/* DEPRECATED
 * *********************************************
/* app/views/chromeviews.js
 * outer most appviews */
'use strict';

define([
    'exports',
    'jquery',
    'backbone',
    'underscore',
    'bbq',
], function( exports, $, Backbone, _, bbq ) {

    var Header = Backbone.View.extend({
        initialize: function() {
            var r = require('app/router')
            this.listenTo( r.router, 'route', this.toggle )
        },

        toggle : function( methodName, urlParam ){
            if ( methodName === 'home' ) {
                setTimeout( function(){
                    $('.site-header').removeClass('home')
                    $('.n-wrapper').removeClass('home')
                    $('#bullets').addClass('loaded')
                }, 2000 )
            }
        }
    })

    var PageView = Backbone.View.extend({
        initialize: function() {
            _.bindAll( this, 'render', 'singleView' )

            this.outlineTitle = $('<h2/>').addClass('visuallyhidden')
            this.$el.prepend(this.outlineTitle)

            this.listenTo( this.model, 'change:project', this.render )
            //this.listenTo( this.model, 'change:showcase', this.Viewrender )
            this.listenTo( this.model, 'change:photoAlbum', this.singleAlbum )
            this.listenTo( this.model, 'change:film', this.singleFilm )

            this.listenTo( this.model, 'change:page', this.pageRender )
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
            console.log('rendering Page')

            this.$el.html( pageView.render() )
            this.semantics( this.model.get('className'), this.model.get('outlineTitle') )

            require(['app/views/showcaseviews'], function(S) {
                if ( pageView instanceof S.Image ) {
                    console.log('instanceof S.Image: loading isotope')

                    pageView.firstLoad()
                    if ( !filtering ) {
                        pageModel.set('filter', '*')
                    }
                } else if ( pageView instanceof S.List ) {
                    console.log('instanceof S.List: sorting by name')

                    pageModel.set( 'sort', 'alpha' )
                }
            })
        },

        singleView : function( pageModel, projectModel, showcase ) {

            require (['app/views/singleviews'], function(views) {
                this.render( pageModel, new views.Project({
                    model : projectModel
                }) )

                if ( showcase.url ) {
                    projectModel.get('showcases').findWhere({
                        url_title : showcase.url
                    }).activate()
                } else {
                    projectModel.get('showcases').first().activate()
                }
            })
        },

        singleAlbum : function( pageModel, galleryModel ) {
            require (['app/views/singleviews'], function(views) {
                this.render( pageModel, new views.Album({
                    model : galleryModel
                }) )
            })
        },

        singleFilm : function( pageModel, filmModel ) {
            require (['app/views/singleviews'], function(views) {
                this.render( pageModel, new views.Film({
                    model : filmModel
                }) )
            })
        }

    })

})
