/* app/router.js */
'use strict';

define([
    'require',
    'exports',
    'jquery',
    'backbone',
    'underscore',
    'app/views/chrome',
    'utils/spinner'
], function( require, exports, $, Backbone, _, Chrome, Spinner ) {

    var Router = Backbone.Router.extend({

        initialize : function() {
            _.bindAll( this, 'debug', 'payload' )
            //this.on('route', this.debug)
        },

        routes : {
            "" : "home",
            "projects" : "projects",
            "projects/:project" : "singleProject",
            "projects/:project/:showcase" : "showcaseItem",
            "photography" : "photography",
            "photography/:title" : "singleAlbum",
            "film" : "film",
            "film/:title" : "singleFilm",
            "profile" : "profile",
            "profile/:section" : "profile",
            "profile/:section/:urlTitle" : "profile",
            "contact" : "contact",
            "stream" : "stream",
            "search" : "search",
            "search/*any" : "search"
        },

        debug : function() {
            console.log('updated again')
        },

        payload : function(method) {
            try {
                //PA[method].fetch({ cache: true })
            } catch(e) {
                console.log('error caught: ', e)
            }
        },

        home : function() {
            Chrome.home()
        },

        projects : function() {
            var spinner = new Spinner()
            require(['app/collections/projects'],
            function(Projects){
                $.when( Projects.fetch() )
                .then( function() {
                    Chrome.projects(Projects)
                } ).done( function() {
                    spinner.detach()
                    Backbone.dispatcher.trigger('hashchange')
                } )
            })
        },

        singleProject : function(project) {
            var spinner = new Spinner()
            require(['app/collections/projects'],
            function(Projects){
                $.when( Projects.fetch({url : '/api/pro/' + project}) )
                .done( function() {
                    Chrome.singleProject(Projects, project)
                    spinner.detach()
                } )
            })
        },

        showcaseItem : function(project, urlTitle) {
            require(['app/collections/projects'],
            function(Projects){
                try {
                    var model = Projects.findWhere({ url : project })
                    var showcase = model.get('showcases').findWhere({ url_title : urlTitle })
                    showcase.trigger( 'swap', showcase )
                } catch(e) {
                    var spinner = new Spinner()
                    $.when( Projects.fetch() )
                    .done( function() {
                        Chrome.singleProject(Projects, project, urlTitle)
                        spinner.detach()
                    } )
                }
            })
        },

        photography : function() {
            var spinner = new Spinner()

            require(['app/collections/photography'],
            function(Albums) {
                $.when( Albums.fetch() )
                .done( function(){
                    Chrome.photoHomeInit( Albums )
                    spinner.detach()
                } )
            })
        },

        singleAlbum : function(urlTitle) {
            var spinner = new Spinner()

            require(['app/collections/photography'],
            function(Albums){
                $.when( Albums.fetch() )
                .done( function() {
                    Chrome.albumInit(Albums, urlTitle)
                    spinner.detach()
                } )
            })
        },

        film : function() {
            var spinner = new Spinner()

            require(['app/collections/films'],
            function(Films) {
                $.when( Films.fetch() )
                .done( function(){
                    Chrome.filmHomeInit(Films)
                    spinner.detach()
                } )
            })
        },

        singleFilm : function( urlTitle ) {
            var spinner = new Spinner()

            require(['app/collections/films'],
            function(Films){
                $.when( Films.fetch() )
                .done( function() {
                    Chrome.singleFilmInit( Films, urlTitle )
                    spinner.detach()
                } )
            })
        },

        profile : function( segment, urlTitle ) {
            require(['app/collections/profile', 'app/views/profileviews'],
            function( Profile, Page ) {
                try {
                    Profile[segment].findWhere({ url : urlTitle }).activate()
                    return
                } catch(e) { console.log(e) }

                var spinner = new Spinner()
                var promiseStack = []
                _.each( Profile, function( section ) {
                    promiseStack.push( section.fetch() )
                })

                $.when.apply( $, promiseStack ).done(function(){
                    Chrome.profileInit( Profile, Page )

                    Backbone.dispatcher.trigger( 'profile:swap', Profile[ segment ? segment : 'bio' ], segment ? false : true )
                    if ( urlTitle ) {
                        Profile[segment].findWhere({ url : urlTitle }).activate()
                    }

                    spinner.detach()
                })
            })
        },

        contact : function() {
        },

        stream : function() {
            var spinner = new Spinner()

            require(['app/collections/instagrams'], function(Instagrams){
                $.when( Instagrams.fetch() )
                .done( function() {
                    //var c = require('app/views/chrome')
                    Chrome.streamInit( Instagrams )
                    spinner.detach()
                } )
            })
        },

        search : function() {
            Chrome.searchInit()
        }
    })

    var router = new Router()
    exports.router = router
})

