/* models/showcase.js - Showcase model */

'use strict';
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
        this.url = function() {
            return options.path + '/' + this.get('url_title')
        }
    },

    activate : function(){
        this.set('active', true)
        PA.router.navigate(this.url())
    },

    deactivate : function(){
        this.set('active', false)
    }

})
