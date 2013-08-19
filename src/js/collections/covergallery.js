"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.CoverGallery = Backbone.Collection.extend({
    model : PA.CoverImage
})

//PA.coverGallery = new PA.CoverGallery()
