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
    'app/views/search'
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

            /*
            this.page = new Page({
                el : '.page',
                parent : this,
                model : this.model
            })
            */

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
            Backbone.dispatcher.trigger('navigate:section', e)
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

        projects : function(spinner) {
            require(['app/views/projects'], function( projects ) {
                projects.setElement('.page')
                try {
                    projects.init(spinner)
                    projects.filter.$el.show()
                } catch (e) {
                    Backbone.dispatcher.on('projects:ready', function() {
                        projects.init(spinner)
                    })
                }
            })
        },

        singleProject : function( spinner, projectUrl, showcaseUrl, previous ) {
            require(['app/views/singleproject'], function( projectView ) {
                projectView.on('rendered', function() {
                    spinner.detach()
                })

                $('.page')
                    .html( projectView.render( projectUrl, showcaseUrl, previous ) )
                    .removeClass('projects')
            })
        },

        photography : function( spinner ) {
            require(['app/views/photography'], function( photography ) {
                photography.setElement( '.page' )
                try {
                    photography.init(spinner)
                } catch(e) {
                    Backbone.dispatcher.on('photography:ready', function() {
                        photography.init(spinner)
                    })
                }
            })
        },

        singleAlbum : function( spinner, albumUrl ) {
            require(['app/views/singlealbum'], function( albumView ) {
                albumView.on('rendered', function() {
                    spinner.detach()
                })

                $('.page').html( albumView.render( albumUrl ) )
            })
        },

        film : function( spinner ) {
            require(['app/views/film'], function( film ){
                film.setElement('.page')
                try{
                    film.init(spinner)
                } catch(e) {
                    Backbone.dispatcher.on('film:ready', function(){
                        film.init(spinner)
                    })
                }
            })
        },

        singleFilm : function( spinner, filmUrl ) {
            require(['app/views/singlefilm'], function( filmView ) {
                filmView.on('rendered', function(){
                    spinner.detach()
                })

                $('.page').html( filmView.render( filmUrl ) )
            })
        },

        profile : function( spinner, segment, urlTitle) {
            require(['app/views/profile'], function( profileView ) {
                profileView.on('rendered', function(){
                    spinner.detach()
                })
                try {
                    $('.page').html( profileView.render( segment, urlTitle ) )
                } catch(e) {
                    profileView.model.on('change:loaded', function() {
                        $('.page').html( profileView.render( segment, urlTitle ) )
                    })
                }
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
