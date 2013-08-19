"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Showcase = Backbone.Model.extend({
    initialize: function(showcase, options){
        if ( showcase.type === 'gallery' ) {
            this.set({
                gallery : new PA.Gallery(showcase.images)
            })
        }
    }
})
