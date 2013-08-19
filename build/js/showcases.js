"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Showcases = Backbone.Collection.extend({
    model : PA.Showcase
})

PA.showcases = new PA.Showcases()
