/* models/pressitem.js - Press Item model */

'use strict';
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
    },

    activate : function(){
        this.set('active', true)
        PA.router.navigate(this.url())
    },

    deactivate : function(){
        this.set('active', false)
    }
})
