"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.PhotoAlbums = Backbone.Collection.extend({
    model : PA.PhotoAlbum
})

PA.albums = new PA.PhotoAlbums()

