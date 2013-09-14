/* app/collections/projects.js
 * projects collection */
'use strict';

define([
    'backbone',
    'app/models/project'
], function( Backbone, ProjectModel ) {

    var Projects = Backbone.Collection.extend({
        model : ProjectModel,
        url : '/api/projects/'
    })

    return Projects
})
