/* collections/instagrams.js - Instagram Stream Collection
 * NO custom model. Uses Backbone.Model */

'use strict';
var PA  = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.Instagrams = Backbone.Collection.extend({
    model : Backbone.Model,
    url : '/api/stream'
})

PA.instagrams = new PA.Instagrams()
