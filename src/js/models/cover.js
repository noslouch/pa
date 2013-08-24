/* models/cover.js - Project/Photo Album Cover Image
 * used on /projects and /photography */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.CoverImage = Backbone.Model.extend({
    initialize: function(image, options) {
        this.set({ tags : options.tags || [] })
    }
})
