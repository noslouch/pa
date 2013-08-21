/* models/bio.js - Bio model */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.Bio = Backbone.Model.extend({
    url : '/fixtures/bioFixture.json'
})
