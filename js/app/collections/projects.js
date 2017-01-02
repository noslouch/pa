/* app/collections/projects.js
 * projects collection */
'use strict';

define([
    'backbone',
    'underscore',
    'app/models/cover'
], function( Backbone, _, Cover ) {

    var Projects = Backbone.Collection.extend({
        model : Cover,
        url : '/api/projects',
        comparator : function(project) {
            return project.get('title')
        }
    })

    return new Projects()
})
