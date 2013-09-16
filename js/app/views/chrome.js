/* app/views/chrome.js
 * outer most appviews */
'use strict';

define([
    'require',
    'exports',
    'jquery',
    'backbone',
    'underscore',
    //'app/views/header',
    'app/views/page',
    'app/views/search',
    'bbq',
    //'app/router',
    //'app/collections/covergallery',
    'app/views/projects',
    'app/views/profileviews',
    'app/views/singleviews'
], function( require, exports, $, Backbone, _, PageView, Search ) {

    var App = Backbone.View.extend({
        initialize : function() {

            _.bindAll(this, 'render', 'routeHandler', 'projects', 'showSearch', 'singleProject' )

            this.model = new Backbone.Model()

        /*
            this.header = new Header({
                el : '.site-header',
                parent : this,
                model : this.model
            })
            */

            this.pageView = new PageView({
                el : '.page',
                parent : this,
                model : this.model
            })

            this.search = new Search.Form({
                el : '#searchForm',
                page : this.model
            })

            //var r = require( 'app/router' )
            //this.listenTo( r.router, 'route', this.routeHandler )

            this.listenTo( this.search, 'submit', function() {
                this.pageView.$el.empty()
            } )
        },

        home : function() {
            require(['app/views/home'], function( home ) {
                home()
            })
        },

        projects : function(Projects) {
            this.model.set({
                className : 'projects',
                outlineTitle : 'Projects'
            })

            var S = require('app/views/showcaseviews'),
                CoverGallery = require('app/collections/covergallery'),
                ProjectLanding = require('app/views/projects')

            this.model.covers = new S.Image({
                cover : true,
                collection : new CoverGallery( Projects.pluck('coverImage') ),
                path : 'projects'
            })

            this.model.titles = new S.List({
                collection : Projects,
                pageClass : 'projects',
                section : 'Projects'
            })

            this.model.random = new S.Starfield({
                collection : this.model.covers.collection
            })

            this.pageView.undelegateEvents()
            this.pageView.stopListening()

            this.model.set( 'page', new ProjectLanding({ model : this.model }) )


            if ( document.location.hash ) {
                var hashObj = $.deparam.fragment()
                if ( hashObj.filter || hashObj.view === 'covers' ) {
                    this.model.set( 'showcase' , this.model.covers )
                } else if ( hashObj.view === 'random' ) {
                    this.model.set( 'showcase', this.model.random )
                } else {
                    this.model.set( 'showcase', this.model.titles )
                }
            } else {
                $.bbq.pushState( { view : 'random' }, 2 )
            }
        },

        singleProject : function(Projects, project, urlTitle) {
            var model = Projects.findWhere({ url : project })

            if ( this.model.get('project') ) {
                model.get('showcases')
                    .findWhere({ url_title : urlTitle }).activate()
            } else {
                var detailView = require('app/views/singleviews')
                var view = new detailView.Project({ model : model })
                this.model.set('page', view)

                if (urlTitle) {
                    model.get('showcases')
                        .findWhere({ url_title : urlTitle }).activate()
                } else {
                    model.get('showcases').first().activate(true)
                }
            }

        },

        photoHomeInit : function( Albums ) {
            var CoverGallery = require('app/collections/covergallery'),
                S = require('app/views/showcaseviews')
            this.model.set( 'page', new S.Image({
                cover : true,
                collection : new CoverGallery( Albums.pluck('coverImage') ),
                path : 'photography'
            }) )
        },

        albumInit : function(Albums, urlTitle) {
            var views = require('app/views/singleviews')
            this.model.set( 'page', new views.Album({
                model : Albums.findWhere({ url : urlTitle })
            }) )
        },

        filmHomeInit : function( Films ) {
            var S = require('app/views/showcaseviews')
            this.model.set( 'page' , new S.FilmGrid({
                collection : Films
            }) )
        },

        singleFilmInit : function( Films, urlTitle ) {
            var views = require('app/views/singleviews')
            this.model.set( 'page', new views.Film({
                model : Films.findWhere({ url : urlTitle })
            }) )
        },

        profileInit : function( Profile ) {
            var Page = require('app/views/profileviews')
            this.model.set( 'page', new Page({
                el : '#profileViewer',
                sections : Profile
            }) )
        },

        streamInit : function( Instagrams ) {
            var S = require('app/views/showcaseviews')
            this.model.set( 'page', new S.Starfield({
                collection : Instagrams
            }, true ) )
        },

        events : {
            'click' : 'closeMenu',
            'click #searchIcon' : 'showSearch'
        },

        showSearch : function(e){
            e.preventDefault()
            this.search.render()
        },

        closeMenu : function(e) {
            $('#filter-bar .open').removeClass('open')
        },

        routeHandler : function(methodName, urlParam) {
            if (methodName !== 'projects'){
                try {
                    this.header.filterBar.remove()
                } catch(e) {}
            }
            try {
                this.page.$el.removeClass( this.last.pageClass )
            } catch(e) {}
        }
    })

    /*
    var app = new App({ el : document })
    exports.chrome = app
    */
    return new App({ el : document })
})
