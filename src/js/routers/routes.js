/*global SingleView, ImageShowcase, Header, Spinner*/
"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Router = Backbone.Router.extend({

    initialize : function() {
        _.bindAll( this, 'debug', 'payload' )
        //this.on('route', this.payload)
    },

    routes : {
        "" : "homeLoader",
        "projects" : "projects",
        "projects/:project" : "singleProject",
        "projects/:project/:showcase" : "showcaseItem",
        "photography" : "photography",
        "photography/:title" : "singleAlbum",
        "film" : "film",
        "film/:title" : "singleFilm",
        "profile" : "profile",
        "profile/:section" : "profileSection",
        "profile/:section/:urlTitle" : "profileItem",
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
            PA[method].fetch({ cache: true })
        } catch(e) {
            console.log('error caught: ', e)
        }
    },

    projects : function() {
        var spinner = new Spinner()

        PA.projects.fetch({ cache: true })
        .then( function() {
            PA.app.projects()
            PA.app.header.filterBar.render()
        } ).done( function() {
            spinner.detach()
            $(window).trigger('hashchange')
        } )
    },

    singleProject : function(project) {
        var spinner = new Spinner()

        //$.when( PA.projects.fetch() )
        PA.projects.fetch({ cache: true })
        .done( function() {
            PA.app.singleProject(project)
            spinner.detach()
        } )

    },

    showcaseItem : function(project, urlTitle) {
        var spinner = new Spinner()

        //$.when( PA.projects.fetch() )
        PA.projects.fetch({ cache: true })
        .done( function() {
            PA.app.singleProject(project, urlTitle)
            spinner.detach()
        } )

    },

    photography : function() {
        var spinner = new Spinner()

        PA.albums.fetch({ cache : true })
        .done( function(){
            PA.app.photoHomeInit()
            spinner.detach()
        } )

    },

    singleAlbum : function(urlTitle) {
        var spinner = new Spinner()

        PA.albums.fetch({ cache : true })
        .done( function() {
            PA.app.albumInit(urlTitle)
            spinner.detach()
        } )
    },

    film : function() {
        var spinner = new Spinner()

        PA.films.fetch({ cache : true })
        .done( function(){
            PA.app.filmHomeInit()
            spinner.detach()
        } )
    },

    singleFilm : function(urlTitle) {
        var spinner = new Spinner()

        PA.films.fetch({ cache : true })
        .done( function() {
            PA.app.singleFilmInit( urlTitle )
            spinner.detach()
        } )
    },

    profile : function() {

        var spinner = new Spinner()

        PA.profilePage = new PA.ProfileViewer({
            el : '#profileViewer'
        })

        var deferreds = []

        _.each(PA.profilePage.sections, function(el){
            deferreds.push( el.fetch({ cache : true }) )
        })

        $.when.apply($, deferreds).done(function(){
            PA.profilePage.render()
            PA.dispatcher.trigger( 'profile:swap', PA.profilePage.bio, 'replace' )

            spinner.detach()
        })
    },

    profileSection : function(section) {

        var spinner = new Spinner()

        switch(section) {
            case 'photos-of-pa':
                section = 'photosOf'
                break;
            case 'articles-by-pa':
                section = 'articlesBy'
                break;
            case 'articles-about-pa':
                section = 'articlesAbout'
                break;
            default:
                break;
        }

        try {

            PA.dispatcher.trigger( 'profile:swap', PA.profilePage[section] )

            spinner.detach()

        } catch(err) {

            console.log('in catch')

            PA.profilePage = new PA.ProfileViewer({
                el : '#profileViewer'
            })

            var deferreds = []

            _.each(PA.profilePage.sections, function(el){
                deferreds.push(el.fetch({ cache : true }))
            })

            $.when.apply($, deferreds).done(function(){
                PA.profilePage.render()
                PA.dispatcher.trigger( 'profile:swap', PA.profilePage[section] )

                spinner.detach()
            })
        }
    },

    profileItem : function(section, urlTitle) {
        var spinner = new Spinner()

        switch(section) {
            case 'photos-of-pa':
                section = 'photosOf'
                break;
            case 'articles-by-pa':
                section = 'articlesBy'
                break;
            case 'articles-about-pa':
                section = 'articlesAbout'
                break;
            default:
                break;
        }

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

        PA.instagrams.fetch({ cache: true })
        .done( function() {
            PA.app.streamInit()
            spinner.detach()
        } )
    }

})
