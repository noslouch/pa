"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.PressItem = Backbone.Model.extend({
    initialize : function(pressItem, options){
        this.set({
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date') )
        })
     },
    url : function() {
        return '/press/' + this.get('url')
    }
})
