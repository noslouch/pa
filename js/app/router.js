/* app/router.js */
'use strict';

define([
    'exports',
    'jquery',
    'backbone',
    'underscore',
    'utils/spinner'
    //'app/collections/projects'
    //'app/collections/films',
    //'app/collections/profile',
    //'app/collections/instagrams',
    //'app/collections/photography'
], function( exports, $, Backbone, _, Spinner ) {

    var Router = Backbone.Router.extend({

        initialize : function() {
            _.bindAll( this, 'debug', 'payload' )
            //this.on('route', this.payload)
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
            "stream" : "stream"
        },

        debug : function() {
            console.log('debug called')
            console.log('this: ', this)
            console.log('arguments: ', arguments)
        },

        payload : function(method) {
            try {
                //PA[method].fetch({ cache: true })
            } catch(e) {
                console.log('error caught: ', e)
            }
        },

        homeLoader : function() {
            //PA.app.home()
        },

        projects : function() {
            var spinner = new Spinner()
            require(['app/collections/projects'], function(Projects){
                $.when( Projects.fetch() )
                .then( function() {
                    var c = require('app/views/chrome')
                    c.chrome.projects(Projects)
                } ).done( function() {
                    spinner.detach()
                    Backbone.dispatcher.trigger('hashchange')
                } )
            })
        },

        singleProject : function(project) {
            var spinner = new Spinner()
            var Projects = require('app/collections/projects')

            $.when( Projects.fetch() )
            .done( function() {
                var c = require('app/views/chrome')
                c.chrome.singleProject(Projects, project)
                spinner.detach()
            } )
        },

        showcaseItem : function(project, urlTitle) {
            var c = require('app/views/chrome')
            var Projects = require('app/collections/projects')

            try {
                var model = Projects.findWhere({ url : project })
                var showcase = model.get('showcases').findWhere({ url_title : urlTitle })
                showcase.trigger( 'swap', showcase )
            } catch(e) {
                var spinner = new Spinner()
                $.when( Projects.fetch() )
                .done( function() {
                    c.chrome.singleProject(Projects, project, urlTitle)
                    spinner.detach()
                } )
            }
        },

        photography : function() {
            var spinner = new Spinner()

            require(['app/collections/photography'], function(Albums) {
                $.when( Albums.fetch() )
                .done( function(){
                    var c = require('app/views/chrome')
                    c.chrome.photoHomeInit( Albums )
                    spinner.detach()
                } )
            })
        },

        singleAlbum : function(urlTitle) {
            var spinner = new Spinner()

            require(['app/collections/photography'], function(Albums){
                $.when( Albums.fetch() )
                .done( function() {
                    var c = require('app/views/chrome')
                    c.chrome.albumInit(Albums, urlTitle)
                    spinner.detach()
                } )
            })
        },

        film : function() {
            var spinner = new Spinner()

            require(['app/collections/films'], function(Films) {
                $.when( Films.fetch() )
                .done( function(){
                    var c = require('app/views/chrome')
                    c.chrome.filmHomeInit(Films)
                    spinner.detach()
                } )
            })
        },

        singleFilm : function( urlTitle ) {
            var spinner = new Spinner()

            require(['app/collections/films'], function(Films){
                $.when( Films.fetch() )
                .done( function() {
                    var c = require('app/views/chrome')
                    c.chrome.singleFilmInit( Films, urlTitle )
                    spinner.detach()
                } )
            })
        },

        profile : function( segment, urlTitle ) {
            var spinner = new Spinner()

            require(['app/collections/profile','app/views/profileviews'], function(Profile, Page) {
                var promiseStack = []

                var profile = new Page({
                    el : '#profileViewer',
                    sections : Profile
                })

                _.each( profile.sections, function( section ) {
                    promiseStack.push( section.fetch() )
                })

                $.when.apply( $, promiseStack ).done(function(){
                    var c = require('app/views/chrome')
                    c.chrome.profileInit( profile )

                    Backbone.dispatcher.trigger( 'profile:swap', profile[ segment ? segment : 'bio' ], segment ? false : true )
                    if ( urlTitle ) {
                        profile[segment].findWhere({ url : urlTitle }).activate()
                    }

                    spinner.detach()
                })
            })
        },

        /*
        profileItem : function(section, urlTitle) {
            var spinner = new Spinner()

            try {

                PA.dispatcher.trigger( 'profile:swap', PA.profilePage[section] )
                PA.profilePage[section].findWhere({ url : urlTitle }).activate()

                spinner.detach()
            } catch(err) {

                PA.profilePage = new PA.ProfileViewer({
                    el : '#profileViewer'
                })

                var deferreds = []

                _.each(PA.profilePage.sections, function(el){
                    deferreds.push(el.fetch())
                })

                $.when.apply($, deferreds).done(function(){
                    PA.profilePage.render()
                    PA.dispatcher.trigger( 'profile:swap', PA.profilePage[section] )
                    PA.profilePage[section].findWhere({ url : urlTitle }).activate()

                    spinner.detach()
                })
            }

        },

        contact : function() {
            $('.page').append('contact')
        },

        stream : function() {
            var spinner = new Spinner()

            $.when( PA.instagrams.fetch() )
            .done( function() {
                PA.app.streamInit()
                spinner.detach()
            } )
        }

*/
    })

    //return new Router()
    var router = new Router()
    exports.router = router
})

