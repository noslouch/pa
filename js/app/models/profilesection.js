/* models/profilesection.js - All Profile Section models */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

// Profile Classes

PA.ProfileBase = Backbone.Model.extend({
    initialize : function() {
        _.bindAll( this, 'activate', 'deactivate')
    },
    active : false,
    activate : function(replace){
        this.active = true
        PA.router.navigate( '/profile/' + this.section , {replace : replace ? true : false} )
        PA.dispatcher.trigger('profile:sectionActivate', this)
    },

    deactivate : function(){
        this.active = false
    }
})

PA.OneOff = PA.ProfileBase.extend({
    parse : function(r) {
        return r[0]
    }
})

PA.ProfileListItem = PA.ProfileBase.extend({
    initialize : function(item, options){
        _.bindAll( this, 'activate', 'deactivate' )
        this.set({
            htmlDate : this.makeHtmlDate( this.get('date') ),
            date : this.parseDate( this.get('date') )
        })
    },

    activate : function(){
        //this.active = true
        PA.router.navigate( '/profile/' + this.collection.section + '/' + this.url() )
        PA.dispatcher.trigger('profile:listItemActivate', this)
    },

    url : function() {
        return this.get('url')
    }
})

PA.Bio = PA.OneOff.extend({
    url : '/api/bio',
    section : 'bio'
})

PA.PhotosOf = PA.OneOff.extend({
    url : '/api/paphotos',
    section : 'photos-of-pa'
})

PA.Acknowledgements = PA.OneOff.extend({
    url : '/api/acknowledgements',
    section: 'acknowledgements'
})
