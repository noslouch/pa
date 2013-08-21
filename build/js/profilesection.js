/* models/profilesection.js - Profile Section model */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.ProfileSection = Backbone.Model.extend({

    defaults : {
        active : false
    },

    initialize : function( section, options ) {

        if ( section instanceof Backbone.Collection ) {

            this.set({
                collection : section
            })

        }

        this.url = function() {
            return '/profile/' + section.path
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
