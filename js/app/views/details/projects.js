/*global PA*/
/* app/views/details/project.js
 * detail view for projects */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
    'app/models/project',
    'app/collections/projects',
    'app/views/partials/album',
    'foundation'
], function( $, Backbone, _, TPL, ProjectModel, Projects, Album ) {

    try {
        Projects.add( PA.projects, { parse : true } )
    } catch (e) {
        Projects.fetch()
    }

    var ProjectDetails = new Album({
        collection : Projects,
        model : new ProjectModel(),
        section : 'projects'
    })

    return ProjectDetails
})
