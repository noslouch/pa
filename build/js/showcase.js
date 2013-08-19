"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.Showcase = Backbone.Model.extend({
    defaults : {
        active : false
    },
    initialize: function(showcase, options){
        if ( showcase.type === 'gallery' ) {
            this.set({
                gallery : new PA.CoverGallery(showcase.images)
            })
        }
    },
    activate : function(){
        this.set('active', true)
    },
    deactivate : function(){
        this.set('active', false)
    }
})
