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
                    //console.log('Tracked: ', '/' + url)
                }
            })
        },

        routes : {
            "" : "section",
            "projects" : "section",
            "projects/:project(/:hidden)" : "detail",
            "photography" : "section",
            "photography/:title" : "detail",
            "film" : "section",
            "film/:title" : "detail",
            "books" : "section",
            "books/:title" : "detail",
            "profile" : "section",
            "profile/:section" : "section",
            "profile/:section/:urlTitle" : "section",
            "contact" : "section",
            "stream" : "section",
            "search" : "search",
            "search/results" : "results",
            "search/*any" : "search"
        },

        section : function( segment, urlTitle ) {
            var spinner = new Spinner()
            var section = Backbone.history.fragment.match(/[^\/]*/).join('') === '' ? 'home' : Backbone.history.fragment.match(/[^\/]*/).join('')

            Chrome.section( spinner, section, segment, urlTitle )
        },

        detail : function( urlTitle, hidden ) {
            var section = Backbone.history.fragment.match(/[^\/]*/).join('')
            var spinner = new Spinner()

            Chrome.detail( spinner, section, urlTitle, hidden, this.previous )
        },

        saveHistory : function() {
            this.history.push(_.clone(document.location))
            if ( this.history.length > 1 ) {
                this.history.shift()
            }
            this.previous = this.history[0]
        },

        debug : function() {
            console.log(Backbone.history.fragment.match(/^.*\//).join('').slice(0,-1))
        },

        payload : function(method) {
            try {
                //PA[method].fetch({ cache: true })
            } catch(e) {
                console.log('error caught: ', e)
            }
        },

        profile : function( segment, urlTitle ) {
            var spinner = new Spinner()
            Chrome.profile( spinner, segment, urlTitle )
        },

        search : function() {
            Chrome.search()
        },

        results : function(){
            try {
                Chrome.currentView.close()
                Chrome.setView( Chrome.searchForm.results )
            } catch(e) {
                Chrome.search()
            }
            $('nav .active').removeClass('active')
        }
    })

    var router = new Router()

    Backbone.dispatcher.on('navigate:section', function(e) {
        var l = e.target.pathname + (e.target.hash ? e.target.hash : '')

        Backbone.dispatcher.trigger('filterCheck', router)
        //$('.page').removeClass().addClass( e.target.pathname.slice(1) + ' page' ).empty()
        $(window).on('hashchange', router.saveHistory)

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

    Backbone.dispatcher.on('savehistory', router.saveHistory)

    exports.router = router
})
