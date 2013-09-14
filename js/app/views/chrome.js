/* app/views/chrome.js
 * outer most app view */
'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'app/models/page',
    'app/views/projects'
], function( $, _, Backbone, PageModel, ProjectsLanding ) {

    var header = Backbone.View.extend({
        initialize: function() {

            this.listenTo( PA.router, 'route', this.toggle )
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

    var Chrome = Backbone.View.extend({
        initialize : function() {
            _.bindAll(this, 'render', 'projects', 'showSearch' )

            this.model = new PageModel()

        /*
            this.pageView = new PA.PageView({ 
                el : '.page',
                parent : this,
                model : this.model
            })
            */

            this.search = new PA.SearchForm({ 
                el : '#searchForm',
                page : this.model
            })

            this.listenTo( PA.router, 'route', this.routeHandler )

            this.listenTo( this.model, 'change:page', this.render )
        },

        render : function( newPage ) {
        },

        home : function() {
        },

        projects : function() {
            this.model.set( 'page', new ProjectsLanding({ 
                el : '.page',
                model : this.model,
                parent : this
            }) )
        },

        singleProject : function(project, urlTitle) {
            this.model.set( 
                'project',
                PA.projects.findWhere({ url : project }),
                { url : urlTitle } 
            )
        },

        photoHomeInit : function() {
            this.model.set( 'showcase', new PA.ImageShowcase({
                cover : true,
                collection : new PA.CoverGallery( PA.albums.pluck('coverImage') ),
                path : 'photography'
            }) )
        },

        albumInit : function(urlTitle) {
            this.model.set( 'photoAlbum', PA.albums.findWhere({ url : urlTitle }) )
        },

        filmHomeInit : function() {
            this.model.set( 'showcase' , new PA.FilmThumbLayout({
                collection : PA.films
            }) )
        },

        singleFilmInit : function( urlTitle ) {
            this.model.set( 'film', PA.films.findWhere({ url : urlTitle }) )
        },

        streamInit : function() {
            this.model.set( 'showcase', new PA.Starfield({
                collection : PA.instagrams
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
            this.header.$('.open').removeClass('open')
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
})
