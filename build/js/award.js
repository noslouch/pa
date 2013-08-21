/* models/award.js - Award Item model */

"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.AwardItem = Backbone.Model.extend({
    initialize : function(awardItem, options){
        this.set({
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date' ) )
        })
     }
})
