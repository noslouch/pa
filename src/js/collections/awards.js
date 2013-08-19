"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.AwardCollection = Backbone.Collection.extend({
    model : PA.AwardItem
})

PA.awardCollection = new PA.AwardCollection()
