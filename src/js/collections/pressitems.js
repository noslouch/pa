"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.PressCollection = Backbone.Collection.extend({
    model : PA.PressItem
})

PA.pressCollection = new PA.PressCollection()
