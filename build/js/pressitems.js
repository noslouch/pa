/* collections/pressitems.js - Press Items collection */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.PressCollection = Backbone.Collection.extend({
    model : PA.PressItem,
    url : '/fixtures/pressFixture.json',
    path : '/press'
})

PA.pressCollection = new PA.PressCollection()
