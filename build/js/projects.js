"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Projects = Backbone.Collection.extend({
    model : PA.Project,
    url : '/api/projects'
})

PA.projects = new PA.Projects()
