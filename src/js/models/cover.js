"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.CoverImage = Backbone.Model.extend({
    initialize: function(image, options) {
        this.set({ tags : options.tags || [] })
    }
})
