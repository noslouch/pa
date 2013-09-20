/* app/models/cover.js - Project/Photo Album Cover Image
 * used on /projects and /photography */
'use strict';

define([
    'backbone'
], function( Backbone ) {

    var CoverImage = Backbone.Model.extend({
        initialize: function(image, options) {
            this.set({ 
                tags : options.tags || [],
                year : options.year
            })
        }
    })

    return CoverImage
})

