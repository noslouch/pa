/* app/collections/showcases.js - Showcases collection */
'use strict';

define([
    'backbone',
    'app/models/showcase'
], function( Backbone, ShowcaseModel ) {
    var Showcases = Backbone.Collection.extend({
        model : ShowcaseModel
    })

    return Showcases
})
