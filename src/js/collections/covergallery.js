/* collections/covergallery.js - Project/Photo Album Cover Image collection
 * used on /projects and /photography */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.CoverGallery = Backbone.Collection.extend({
    model : PA.CoverImage
})

