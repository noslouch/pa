/*global SingleView, ImageShowcase, Header*/
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

        $.get('/fixtures/projectFixture.json').done(function(d) {
            PA.projects = new PA.Projects(d)

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

            PA.starInit()

        })
    },

    singleProject : function(project, showcase) {
        var showcases

        try {
            // Projects are loaded

            PA.currentModel = PA.projects.findWhere({ url : project })
            showcases = PA.currentModel.get('showcases')

            PA.router.navigate(showcases.models[0].url(), {trigger: true})

        } catch(err) {
            // Projects haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            $.get('/fixtures/projectFixture.json').done(function(d) {
                PA.currentModel = new PA.Project( _.findWhere(d, {url : project}) )
                showcases = PA.currentModel.get('showcases')

                PA.router.navigate(showcases.models[0].url(), {trigger: true})
            })
        }
    },

    showcaseItem : function(project, urlTitle) {
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

        } catch(err) {
            $.get('/fixtures/projectFixture.json').done(function(d) {
                PA.currentModel = new PA.Project( _.findWhere(d, {url : project }) )
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

        PA.router.navigate('/profile/bio', {trigger: true})

    },

    profileSection : function(section) {
        try {
            PA.profileView.toggleActive(section)
            PA.profileView.sectionLoader(section)
        } catch(err) {
            PA.profilePages = new Backbone.Collection()
            var add = function(d) { PA.profilePages.add(d) }
            $.when( $.get('/fixtures/awardsFixture.json'),
                    $.get('/fixtures/bioFixture.json'),
                    $.get('/fixtures/paSubjectFixture.json'),
                    $.get('/fixtures/paAuthorFixture.json'),
                    $.get('/fixtures/paPhotosFixture.json'),
                    $.get('/fixtures/pressFixture.json')
            ).done( function(){
                _.each(arguments, function(el){
                    PA.profilePages.add(el[0])
                    PA.groupedProfilePages = PA.profilePages.groupBy('type')
                })

                PA.profileView = new PA.ProfileViewer({
                    el : '#profileViewer',
                    collection : PA.profilePages
                })

                PA.profileView.toggleActive(section)

                PA.app.page.render({
                    view : PA.profileView,
                    pageClass : 'profile',
                    section : 'Profile Home',
                })

                PA.profileView.sectionLoader(section)
            })
        }
    },
    profileItem : function(section, urlTitle) {
        try {
            PA.profileView.contentLoader(section, urlTitle)
            PA.profileView.toggleActive(section)
        } catch(err) {
            PA.profilePages = new Backbone.Collection()
            var add = function(d) { PA.profilePages.add(d) }
            $.when( $.get('/fixtures/awardsFixture.json'),
                    $.get('/fixtures/bioFixture.json'),
                    $.get('/fixtures/paSubjectFixture.json'),
                    $.get('/fixtures/paAuthorFixture.json'),
                    $.get('/fixtures/paPhotosFixture.json'),
                    $.get('/fixtures/pressFixture.json')
            ).done( function(){
                _.each(arguments, function(el){
                    PA.profilePages.add(el[0])
                    PA.groupedProfilePages = PA.profilePages.groupBy('type')
                })

                PA.profileView = new PA.ProfileViewer({
                    el : '#profileViewer',
                    collection : PA.profilePages
                })

                PA.profileView.toggleActive(section)

                PA.app.page.render({
                    view : PA.profileView,
                    pageClass : 'profile',
                    section : 'Profile Home'
                })

                PA.profileView.contentLoader(section, urlTitle)
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
