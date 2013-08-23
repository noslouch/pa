/* collections/profilesections.js - All Profile Section collections */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.ProfileCollection = Backbone.Collection.extend({
    initialize : function() {
        _.bindAll( this, 'activate', 'deactivate' )
    },
    active: false,
    activate : function(href){
        this.active = true
        PA.router.navigate( '/profile/' + this.section )
        PA.dispatcher.trigger('profile:sectionActivate', this)
    },
    deactivate : function() {
        this.active = false
    }
})

PA.Press = PA.ProfileCollection.extend({
    model : PA.ProfileListItem,
    url : '/fixtures/pressFixture.json',
    section : 'press'
})

PA.Awards = PA.ProfileCollection.extend({
    model : PA.ProfileListItem,
    url : '/fixtures/awardsFixture.json',
    section : 'awards'
})

PA.ArticlesBy = PA.ProfileCollection.extend({
    model : PA.ProfileListItem,
    url : '/fixtures/pressFixture.json',
    section : 'articles-by-pa'
})

PA.ArticlesAbout = PA.ProfileCollection.extend({
    model : PA.ProfileListItem,
    url : '/fixtures/pressFixture.json',
    section : 'articles-about-pa'
})

PA.Interviews = PA.ProfileCollection.extend({
    model : PA.ProfileListItem,
    url : '/fixtures/pressFixture.json',
    section : 'interviews'
})

PA.Transcripts = PA.ProfileCollection.extend({
    model : PA.ProfileListItem,
    url : '/fixtures/pressFixture.json',
    section : 'transcripts'
})

