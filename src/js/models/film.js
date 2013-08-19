"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Film = Backbone.Model.extend({
    url : function() {
        return '/film/' + this.get('url')
    }
})
