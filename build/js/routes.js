/*global SingleView, ImageShowcase, Header*/
"use strict";

var PA = PA || {}

PA.Router = Backbone.Router.extend({

    routes : {
        "/" : "homeLoader",
        "projects" : "projects",
        "projects/:title" : "viewer",
        "photography" : "photography",
        "photograhy/:title" : "viewer",
        "film" : "film",
        "film/:title" : "viewer",
        "profile" : "profile",
        "profile/:title" : "viewer",
        "contact" : "contact",
        "stream" : "stream"
    },

    viewer : function(title) {
        if (PA.projects.length === 0) {
            // Projects haven't loaded yet because
            // A) navigate to direct URL
            // B) navigate from a different page section

            $.get('/fixtures/projectFixture').done(function(d) {
                var project = new PA.Project( _.findWhere(d, {url : title}) )
                $('.page').html( new SingleView({ model : project }).render() )
            })

        } else {
            // Projects are loaded

            var p = PA.projects.findWhere({ url : title })
            $('.page').html( new SingleView({ model : p }).render() )
        }
    },

    projects : function() {
                   /*
        PA.chrome = PA.chrome || new PA.Page({ el : '.page' })
        PA.chrome.render({
            section : 'Projects',
            pageClass : 'projects'
        })
        */

        $.get('/fixtures/projectFixture').done(function(d) {
            PA.projects = new PA.Projects(d)
            PA.app.header.render({ filter: true, collection : PA.projects })
            PA.coverImages = new PA.CoverGallery( PA.projects.pluck('coverImage'))
            PA.starInit()

            /*
            PA.page.$el.html( new PA.ShowcaseContainer().render({
                collection : PA.coverImages,
                cover : true,
                type : 'image'
            }) )
            */
        })
    },

    film : function() {
        $('.page').append('film')
    },

    photography : function() {
        $('.page').append('photography')
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

PA.router = new PA.Router()
Backbone.history.start({pushState: true, root: "/"})
