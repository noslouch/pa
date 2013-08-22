/* collections/awards.js - Award collection */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.AwardCollection = Backbone.Collection.extend({
    model : PA.AwardItem,
    url : '/fixtures/awardsFixture.json'
})

