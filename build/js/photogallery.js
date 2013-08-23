/* collections/photogallery.js - Image/Photo Gallery model 

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.PhotoGallery = Backbone.Collection.extend({
    model : PA.GalleryImage
})

PA.photoGallery = new PA.PhotoGallery()
*/
