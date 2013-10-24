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
            _.bindAll( this, 'debug', 'payload', 'saveHistory' )
            this.on('route', this.saveHistory)
            $(window).on('hashchange', this.saveHistory)
            //this.on('route', this.debug)
            //this.on('route', this.semantics)
            this.history = [document.location]
            this.bind('all', this._trackPageview)
        },

        _trackPageview : function() {
            var url;
            url = Backbone.history.getFragment();
            window.ga('send', 'pageview', {
                'page' : '/' + url,
                'hitCallback' : function() {
                    console.log('Tracked: ', '/' + url)
                }
            })
        },

        routes : {
            "" : "home",
            "projects" : "projects",
            "projects/:project" : "singleProject",
            "projects/:project/:showcase" : "singleProject",
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

        saveHistory : function() {
            this.history.push(_.clone(document.location))
            if ( this.history.length > 1 ) {
                this.history.shift()
            }
            this.previous = this.history[0]
        },

        debug : function() {
            console.log('navigated to ', arguments[0])
        },

        payload : function(method) {
            try {
                //PA[method].fetch({ cache: true })
            } catch(e) {
                console.log('error caught: ', e)
            }
        },

        home : function() {
            var spinner = new Spinner()
            Chrome.home(spinner)
        },

        projects : function() {
            var spinner = new Spinner()
            Chrome.projects(spinner)
        },

        singleProject : function(projectUrl, showcaseUrl) {
            var spinner = new Spinner()
            Chrome.singleProject( spinner, projectUrl, showcaseUrl, this.previous )
        },

        photography : function() {
            var spinner = new Spinner()
            Chrome.photography( spinner )
        },

        singleAlbum : function( albumUrl ) {
            var spinner = new Spinner()
            Chrome.singleAlbum( spinner, albumUrl )
        },

        film : function() {
            var spinner = new Spinner()
            Chrome.film( spinner )
        },

        singleFilm : function( filmUrl ) {
            var spinner = new Spinner()
            Chrome.singleFilm( spinner, filmUrl )
        },

        profile : function( segment, urlTitle ) {
            var spinner = new Spinner()
            Chrome.profile( spinner, segment, urlTitle )
        },

        contact : function() {
            var spinner = new Spinner()
            Chrome.contact( spinner )
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

    Backbone.dispatcher.on('navigate:section', function(e) {
        Backbone.dispatcher.trigger('filterCheck', router)
        //$('.page').removeClass().addClass( e.target.pathname.slice(1) + ' page' ).empty()
        var l = e.target.pathname + (e.target.hash ? e.target.hash : '')
        router.navigate( l )
        router._trackPageview()
    })

    Backbone.dispatcher.on('navigate:detail', function(e, currentView) {
        Backbone.dispatcher.trigger('filterCheck', router)
        currentView.close()
        router.navigate(e.currentTarget.pathname, { trigger: true })
    })

    Backbone.dispatcher.on('navigate:showcase', function(ops) {
        Backbone.dispatcher.trigger('filterCheck', router)
        router.navigate( ops.url, { replace : ops.replace })
        router._trackPageview()
    })

    Backbone.dispatcher.on('profile:navigate', function( path ){
        router.navigate( path )
        router._trackPageview()
    })

    exports.router = router
})
