/* collections/instagrams.js - Instagram Stream Collection
 * NO custom model. Uses Backbone.Model */
'use strict';

define([
    'backbone'
], function( Backbone ) {
    var Instagrams = Backbone.Collection.extend({
        model : Backbone.Model,
        url : '/api/stream'
    })

    return new Instagrams()
})
