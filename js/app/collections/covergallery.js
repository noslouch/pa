/* app/collections/covergallery.js - Project/Photo Album Cover Image collection
 * used on /projects and /photography */
'use strict';

define([
    'backbone',
    'models/cover'
], function( Backbone, CoverImage ) {

    var CoverGallery = Backbone.Collection.extend({
        model : CoverImage
    })

    return CoverGallery
})



