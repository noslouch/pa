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
    'bbq'
    //'app/router',
    //'app/collections/covergallery',
    //'app/views/projects',
    //'app/views/profileviews',
    //'app/views/singleviews'
], function( require, exports, $, Backbone, _, Page, Search ) {

    var App = Backbone.View.extend({
        initialize : function() {
            _.bindAll( this, 'render', 'routeHandler', 'projects', 'showSearch', 'singleProject' )

            this.model = new Backbone.Model()

            this.page = new Page({
                el : '.page',
                parent : this,
                model : this.model
            })

            this.search = new Search.Form({
                el : '#searchForm',
                page : this.model
            })
            this.listenTo( this.search, 'submit', function() {
                this.page.$el.empty()
            } )
        },

        events : {
            'click' : 'closeMenu',
            'click #searchIcon' : 'showSearch',
            'click #nav a' : 'navigate'
        },

        showSearch : function(e){
            e.preventDefault()
            this.search.render()
        },

        closeMenu : function(e) {
            $('#filter-bar .open').removeClass('open')
        },

        navigate : function(e) {
            e.preventDefault()
            Backbone.dispatcher.trigger('navigate', e)
            this.$('#nav a').removeClass( 'active' )
            $(e.target).addClass( 'active' )
        },

        home : function() {
            var self = this,
                bootstrap = !!$('#n-container').length

            require(['app/views/home'], function( home ) {
                home.setElement('.page')
                home.render()
            })
        },

        projects : function(Projects) {
            var self = this
            //this.model.set({
            //    outlineTitle : 'Projects',
            //    projects : Projects
            //})

            require([
                'app/views/showcaseviews',
                'app/collections/covergallery',
                'app/views/projects'],
            function( S, CoverGallery, ProjectLanding ) {

                self.model.cover = new S.Image({
                    cover : true,
                    collection : new CoverGallery( Projects.pluck('coverImage') ),
                    path : 'projects',
                    model : self.model
                })

                self.model.list = new S.List({
                    //collection : Projects,
                    collection : new CoverGallery( Projects.pluck('coverImage') ),
                    pageClass : 'projects',
                    path : 'projects',
                    section : 'Projects',
                    model : self.model
                })

                self.model.random = new S.Starfield({
                    collection : self.model.cover.collection
                })

                self.page.undelegateEvents()
                self.page.stopListening()

                //self.model.set( 'page', new ProjectLanding({ model : self.model }) )

                self.$('.page').append( new ProjectLanding({
                    model : self.model
                })

                if ( document.location.hash ) {
                    $(window).trigger('hashchange')
                } else {
                    $.bbq.pushState({ view : 'random' })
                }
            })
        },

        singleProject : function(Projects, project, urlTitle) {
            $('.page').removeClass('projects')
            var model = Projects.findWhere({ 'url-title' : project })

            if ( this.model.get('project') ) {
                model.get('showcases')
                    .findWhere({ url_title : urlTitle }).activate()
            } else {
                //var detailView = require('app/views/singleviews')
                var self = this
                require(['app/views/singleviews'],
                function(detailView) {
                    var view = new detailView.Project({ model : model })
                    self.model.set('page', view)

                    if (urlTitle) {
                        model.get('showcases')
                            .findWhere({ url_title : urlTitle }).activate()
                    } else {
                        model.get('showcases').first().activate(true)
                    }
                })
            }

        },

        photoHomeInit : function( Albums ) {
            var self = this

            require(['app/views/showcaseviews', 'app/collections/covergallery'],
            function( S, CoverGallery ) {
            //var CoverGallery = require('app/collections/covergallery'),
            //    S = require('app/views/showcaseviews')

                self.model.set( 'page', new S.Image({
                    cover : true,
                    collection : new CoverGallery( Albums.pluck('coverImage') ),
                    path : 'photography',
                    model : self.model
                }) )
            })
        },

        albumInit : function(Albums, urlTitle) {
            //var views = require('app/views/singleviews')
            var self = this
            require(['app/views/singleviews'],
            function( views ) {
                self.model.set( 'page', new views.Album({
                    model : Albums.findWhere({ url : urlTitle })
                }) )
            })
        },

        filmHomeInit : function( Films ) {
            //var S = require('app/views/showcaseviews')
            var self = this
            require(['app/views/showcaseviews'],
            function( S ) {
                self.model.set( 'page' , new S.FilmGrid({
                    collection : Films
                }) )
            })
        },

        singleFilmInit : function( Films, urlTitle ) {
            //var views = require('app/views/singleviews')
            var self = this
            require(['app/views/singleviews'],
            function( views ) {
                self.model.set( 'page', new views.Film({
                    model : Films.findWhere({ url : urlTitle })
                }) )
            })
        },

        profile : function( segment, urlTitle) {
            var self = this
            require(['app/views/profile'], function( Profile ) {
                self.$('.page').append( new Profile({
                    segment : segment,
                    urlTitle : urlTitle
                }).render() )
            })
        },

        streamInit : function( Instagrams ) {
            //var S = require('app/views/showcaseviews')
            var self = this
            require(['app/views/showcaseviews'],
            function( S ) {
                self.model.set( 'page', new S.Starfield({
                    collection : Instagrams
                }, true ) )
            })
        },

        searchInit : function() {
            this.pageSearch = new Search.Form({
                el : '#pageSearchForm',
                page : this.model
            })
            this.pageSearch.render()
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

    return new App({ el : document })
})
