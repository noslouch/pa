/*global SingleView*/
"use strict";

var PA = PA || {}

var Router = Backbone.Router.extend({

    routes : {
        "projects" : "projectLoader",
        "projects/:title" : "singleProject"
    },

    singleProject : function(title) { 
        console.log ("/projects/" + title)
        $('#showcaseContainer').remove()
        var s = new SingleView({ model : projects.findWhere({ url : "/projects/" + title }) })
    },

    projectLoader : function() {
        $.get('/fixtures/projectFixture').done(function(d) {
            _.each(d, function(e, i, l){
                projects.add( new Project(e) )
            })
        })
    }
})

PA.router = new Router()

Backbone.history.start({pushState: true, root: "/"})
