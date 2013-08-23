"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Films = Backbone.Collection.extend({
    model : PA.Film
})

PA.films = new PA.Films()
