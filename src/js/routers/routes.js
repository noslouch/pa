/*global SingleView, ImageShowcase, Header, Spinner*/
"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Router = Backbone.Router.extend({

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

    projects : function() {

        var spinner = new Spinner()

        $.when( PA.projects.fetch() ).done(function(){
            PA.app.header.filterBar.render()

            PA.coverImages = new PA.CoverGallery( PA.projects.pluck('coverImage') )
            PA.coverShowcase = new PA.ImageShowcase({
                cover : true,
                collection : PA.coverImages,
                path : 'projects'
            })

            PA.listShowcase = new PA.ListShowcase({
                    groupedCollection : PA.projects.groupBy('date'),
                    pageClass : 'projects',
                    section : 'Projects' 
            })

            PA.randomShowcase = new PA.Starfield()


            if ($.param.fragment()) {

                PA.app.page.render({
                    view : PA.coverShowcase,
                    pageClass : 'projects',
                    section : 'Projects'
                })
                PA.coverShowcase.firstLoad()
                $(window).trigger('hashchange')

            } else {

                PA.app.page.render({
                    view : PA.randomShowcase,
                    pageClass : 'projects',
                    section : 'Projects'
                })

            }

            spinner.detach()
        })
    },

    singleProject : function(project) {
        var spinner = new Spinner()
        var showcases

        try {
            // Projects are loaded

            PA.currentModel = PA.projects.findWhere({ url : project })
            showcases = PA.currentModel.get('showcases')

            PA.router.navigate(showcases.models[0].url(), {trigger: true, replace: true})

            spinner.detach()

        } catch(err) {
            // Projects haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            $.when( PA.projects.fetch() ).done( function() {
                PA.currentModel = PA.projects.findWhere({ url : project})
                showcases = PA.currentModel.get('showcases')

                PA.router.navigate(showcases.models[0].url(), {trigger: true, replace: true})

                spinner.detach()
            })
        }
    },

    showcaseItem : function(project, urlTitle) {
        var spinner = new Spinner()
        var showcases

        try {
            showcases = PA.currentModel.get('showcases')

            PA.singleView = new PA.ProjectViewer({
                model : PA.currentModel
            })

            PA.app.page.render({
                view : PA.singleView,
                pageClass : 'project-single',
                section : PA.currentModel.get('title')
            })

            showcases.findWhere({ url_title : urlTitle }).activate()

            spinner.detach()

        } catch(err) {

            $.when( PA.projects.fetch() ).done( function() {

                PA.currentModel = PA.projects.findWhere({ url : project})
                showcases = PA.currentModel.get('showcases')
                PA.singleView = new PA.ProjectViewer({
                    model : PA.currentModel
                })

                PA.app.page.render({
                    view : PA.singleView,
                    pageClass : 'project-single',
                    section : PA.currentModel.get('title')
                })

                showcases.findWhere({ url_title : urlTitle }).activate()

                spinner.detach()
            })
        }
    },

    photography : function() {
        var spinner = new Spinner()

        $.when( PA.albums.fetch() ).done( function(){

            PA.coverImages = new PA.CoverGallery( PA.albums.pluck('coverImage') )
            PA.coverShowcase = new PA.ImageShowcase({
                cover : true,
                collection : PA.coverImages,
                path : 'photography'
            })

            PA.app.page.render({
                view : PA.coverShowcase,
                pageClass : 'photography',
                section : 'Photography'
            })

            PA.coverShowcase.firstLoad()

            spinner.detach()
        })
    },

    singleAlbum : function(urlTitle) {
        var spinner = new Spinner()

        if (PA.albums.length) {
            // Photo Galleries are loaded

            var photoAlbumModel = PA.albums.findWhere({ url : urlTitle })

            PA.singleAlbumView = new PA.SingleAlbumView({
                model : photoAlbumModel
            })

            PA.app.page.render({
                view : PA.singleAlbumView,
                pageClass : 'photography',
                section : photoAlbumModel.get('title')
            })

            spinner.detach()

        } else {
            // Photo Galleries haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            $.when( PA.albums.fetch() ).done( function(){
                var photoAlbumModel = PA.albums.findWhere({ url : urlTitle })

                PA.singleAlbumView = new PA.SingleAlbumView({
                    model : photoAlbumModel
                })

                PA.app.page.render({
                    view : PA.singleAlbumView,
                    pageClass : 'photography',
                    section : photoAlbumModel.get('title')
                })

                spinner.detach()
            })
        }

    },

    film : function() {
        var spinner = new Spinner()

        $.when( PA.films.fetch() ).done( function(){

            PA.filmLanding = new PA.FilmThumbLayout({
                collection : PA.films
            })

            PA.app.page.render({
                view : PA.filmLanding,
                pageClass : 'film',
                section : 'Film Home'
            })

            spinner.detach()
        } )
    },

    singleFilm : function(urlTitle) {

        var spinner = new Spinner()

        if (PA.films.length) {
            // Films are loaded

            var filmModel = PA.films.findWhere({ url : urlTitle })

            PA.singleFilmView = new PA.SingleFilmView({
                model : filmModel
            })

            PA.app.page.render({
                view : PA.singleFilmView,
                pageClass : 'film',
                section : filmModel.get('title')
            })

            spinner.detach()

        } else {
            // Films haven't loaded yet

            $.when( PA.films.fetch() ).done( function(){
                var filmModel = PA.films.findWhere({ url : urlTitle })
                PA.singleFilmView = new PA.SingleFilmView({
                    model : filmModel
                })

                PA.app.page.render({
                    view : PA.singleFilmView,
                    pageClass : 'film',
                    section : filmModel.get('title')
                })

                spinner.detach()
            })
        }
    },

    profile : function() {

        var spinner = new Spinner()

        PA.profilePage = new PA.ProfileViewer({
            el : '#profileViewer'
        })

        var deferreds = []

        _.each(PA.profilePage.sections, function(el){
            deferreds.push(el.fetch())
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
        $('.page').append('stream')
    }

})
