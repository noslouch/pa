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

            PA.app.page.render({
                view : PA.coverShowcase,
                pageClass : 'projects',
                section : 'Projects'
            })

            if ($.param.fragment()) {
                PA.coverShowcase.firstLoad()
                $(window).trigger('hashchange')
            } else {
                PA.starInit()
            }

            spinner.detach()
        })
    },

    singleProject : function(project, showcase) {
        var spinner = new Spinner()
        var showcases

        try {
            // Projects are loaded

            PA.currentModel = PA.projects.findWhere({ url : project })
            showcases = PA.currentModel.get('showcases')

            PA.router.navigate(showcases.models[0].url(), {trigger: true})

            spinner.detach()

        } catch(err) {
            // Projects haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            //$.get('/fixtures/projectFixture.json').done(function(d) {
            $.when( PA.projects.fetch() ).done( function() {
                //PA.currentModel = new PA.Project( PA.projects.findWhere({url : project}) )
                PA.currentModel = PA.projects.findWhere({ url : project})
                showcases = PA.currentModel.get('showcases')

                PA.router.navigate(showcases.models[0].url(), {trigger: true})

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
            //$.get('/fixtures/projectFixture.json').done(function(d) {
            $.when( PA.projects.fetch() ).done( function() {
                //PA.currentModel = new PA.Project( PA.projects.findWhere({ url : project }) )

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
        $.get('/fixtures/photographyFixture.json').done(function(d) {
            PA.albums = new PA.PhotoAlbums(d)

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
        })
    },

    singleAlbum : function(title) {

        if (PA.albums.length) {
            // Photo Galleries are loaded

            var photoAlbumModel = PA.albums.findWhere({ url : title })

            PA.singleAlbumView = new PA.SingleAlbumView({
                model : photoAlbumModel
            })

            PA.app.page.render({
                view : PA.singleAlbumView,
                pageClass : 'photography',
                section : photoAlbumModel.get('title')
            })

        } else {
            // Photo Galleries haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            $.get('/fixtures/photographyFixture.json').done(function(d) {
                var photoAlbumModel = new PA.PhotoAlbum( _.findWhere(d, {url : title}) )

                PA.singleAlbumView = new PA.SingleAlbumView({
                    model : photoAlbumModel
                })

                PA.app.page.render({
                    view : PA.singleAlbumView,
                    pageClass : 'photography',
                    section : photoAlbumModel.get('title')
                })
            })
        }

    },

    film : function() {
        $.get('/fixtures/filmFixture.json').done( function(d) {
            PA.films = new PA.Films(d)
            PA.filmLanding = new PA.FilmThumbLayout({
                collection : PA.films
            })

            PA.app.page.render({
                view : PA.filmLanding,
                pageClass : 'film',
                section : 'Film Home'
            })

        } )
    },

    singleFilm : function(title) {

        if (PA.films.length) {
            // Films are loaded

            var filmModel = PA.films.findWhere({ url : title })

            PA.singleFilmView = new PA.SingleFilmView({
                model : filmModel
            })

            PA.app.page.render({
                view : PA.singleFilmView,
                pageClass : 'film',
                section : filmModel.get('title')
            })
        } else {
            // Films haven't loaded yet

            $.get('/fixtures/filmFixture.json').done( function(d) {
                var filmModel = new PA.PhotoAlbum( _.findWhere(d, { url : title }) )
                PA.singleFilmView = new PA.SingleFilmView({
                    model : filmModel
                })

                PA.app.page.render({
                    view : PA.singleFilmView,
                    pageClass : 'film',
                    section : filmModel.get('title')
                })
            })
        }
    },

    profile : function() {

        PA.profilePage = new PA.ProfileViewer({
            el : '#profileViewer'
        })

        var deferreds = []

        _.each(PA.profilePage.sections, function(el){
            deferreds.push(el.fetch())
        })

        $.when.apply($, deferreds).done(function(){
            PA.profilePage.render()
            PA.dispatcher.trigger( 'profile:swap', PA.profilePage.bio )
        })
    },

    profileSection : function(section) {

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
            })
        }
    },

    profileItem : function(section, urlTitle) {
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
