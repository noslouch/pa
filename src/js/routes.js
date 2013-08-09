/*global SingleView, CoverView*/
"use strict";

var PA = PA || {}

var Router = Backbone.Router.extend({

    routes : {
        "projects" : "projectLoader",
        "projects/:title" : "singleProject",
        "photography" : "photoLoader",
        "photograhy/:title" : "singlePhoto",
        "film" : "filmLoader",
        "film/:title" : "filmSingle",
        "profile" : "profileLoader",
        "profile/:title" : "profileSingle",
        "contact" : "contactLoader",
        "stream" : "streamLoader"
    },

    singleProject : function(title) { 
        $('#showcaseContainer').remove()
        var s = new SingleView({ model : projects.findWhere({ url : title }) })
    },

    projectLoader : function() {
        $.get('/fixtures/projectFixture').done(function(d) {
            _.each(d, function(e, i, l){
                projects.add( new Project(e) )
            })
            var c = new CoverView({
                collection : projects,
                container : '#showcaseContainer'
            })
        })
    }
})

PA.router = new Router()
PA.router.on('route', function(){
    console.dir(arguments)
})

Backbone.history.start({pushState: true, root: "/"})
