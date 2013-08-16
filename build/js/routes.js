/*global SingleView, ImageShowcase, Header*/
"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Router = Backbone.Router.extend({

    routes : {
        "" : "homeLoader",
        "projects" : "projects",
        "projects/:title" : "singleProject",
        "photography" : "photography",
        "photography/:title" : "singleAlbum",
        "film" : "film",
        "film/:title" : "singleFilm",
        "profile" : "profile",
        "profile/:title" : "profileSection",
        "contact" : "contact",
        "stream" : "stream"
    },

    projects : function() {

        $.get('/fixtures/projectFixture.json').done(function(d) {
            PA.projects = new PA.Projects(d)

            PA.app.header.filterBar.render()

            PA.coverImages = new PA.Covers( PA.projects.pluck('coverImage') )
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

    singleProject : function(title) {

        if (PA.projects.length) {
            // Projects are loaded

            var project = PA.projects.findWhere({ url : title })

            PA.singleProject = new PA.ProjectViewer({
                model : project
            })

            PA.app.page.render({
                view : PA.singleProject,
                pageClass : 'project-single',
                section : project.get('title')
            })

        } else {
            // Projects haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            $.get('/fixtures/projectFixture.json').done(function(d) {
                var project = new PA.Project( _.findWhere(d, {url : title}) )

                PA.singleProject = new PA.ProjectViewer({
                    model : project
                })

                PA.app.page.render({
                    view : PA.singleProject,
                    pageClass : 'project-single',
                    section : project.get('title')
                })
            })
        }


    },

    photography : function() {
        $.get('/fixtures/photographyFixture.json').done(function(d) {
            PA.albums = new PA.PhotoAlbums(d)

            PA.coverImages = new PA.Covers( PA.albums.pluck('coverImage') )
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

        PA.profilePages = new Backbone.Collection()
        var add = function(d) { PA.profilePages.add(d) }
        $.when( $.get('/fixtures/awardsFixture.json'),
                $.get('/fixtures/bioFixture.json'),
                $.get('/fixtures/paAuthorFixture.json'),
                $.get('/fixtures/paPhotosFixture.json'),
                $.get('/fixtures/pressFixture.json')
        ).done( function(){
            _.each(arguments, function(el){
                PA.profilePages.add(el[0])
                PA.groupedProfilePages = PA.profilePages.groupBy('type')
            })
        })
        PA.profileView = new PA.ProfileViewer({
            el : '#profileViewer',
            collection : PA.profilePages
        })

        PA.app.page.render({
            view : PA.profileView,
            pageClass : 'profile',
            section : 'Profile Home',
        })
    },

    contact : function() {
        $('.page').append('contact')
    },

    stream : function() {
        $('.page').append('stream')
    }

})
