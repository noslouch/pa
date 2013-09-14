/* models/film.js - Film model */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.Film = Backbone.Model.extend({
    initialize : function() {
        this.set({
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date') )
        })
    },

    url : function() {
        return '/film/' + this.get('url')
    }
})
