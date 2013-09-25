/* app/collections/projects.js
 * projects collection */
'use strict';

define([
    'backbone',
    'underscore',
    'app/models/project'
], function( Backbone, _, ProjectModel ) {

    var Projects = Backbone.Collection.extend({
        model : ProjectModel,
        url : '/api/projects/',
        comparator : function(project) {
            return project.get('title')
        }
    })

    return new Projects()
})
