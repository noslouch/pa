/* collections/albums.js - Photography Albums collection
 * used on /photography */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.PhotoAlbums = Backbone.Collection.extend({
    model : PA.PhotoAlbum,
    url : '/api/photography',
    path : 'photography'
})

PA.albums = new PA.PhotoAlbums()

