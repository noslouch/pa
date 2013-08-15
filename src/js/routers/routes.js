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
        "film/:title" : "viewer",
        "profile" : "profile",
        "profile/:title" : "viewer",
        "contact" : "contact",
        "stream" : "stream"
    },

    projects : function() {

        $.get('/fixtures/projectFixture').done(function(d) {
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

            $.get('/fixtures/projectFixture').done(function(d) {
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
        $.get('/fixtures/photographyFixture').done(function(d) {
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

        try {
        //if (PA.albums.length) {
            // Photo Galleries are loaded

            var photoAlbum = PA.albums.findWhere({ url : title })

            PA.singleAlbum = new PA.ProjectViewer({
                model : photoAlbum
            })

            PA.app.page.render({
                view : PA.singleAlbum,
                pageClass : 'photography',
                section : photoAlbum.get('title')
            })

        } catch(e) {
            // Photo Galleries haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            $.get('/fixtures/photographyFixture').done(function(d) {
                var photoAlbum = new PA.PhotoAlbum( _.findWhere(d, {url : title}) )

                PA.singleAlbum = new PA.ProjectViewer({
                    model : photoAlbum
                })

                PA.app.page.render({
                    view : PA.singleAlbum,
                    pageClass : 'photography',
                    section : photoAlbum.get('title')
                })
            })
        }


    },
    film : function() {
        $('.page').append('film')
    },

    profile : function() {
        $('.page').append('profile')
    },

    contact : function() {
        $('.page').append('contact')
    },

    stream : function() {
        $('.page').append('stream')
    }

})
