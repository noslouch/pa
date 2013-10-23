/* DEPRECATED
 * *************************************************************
 * app/models/profilesection.js - All Profile Section models */
'use strict';

define([
    'backbone',
    'underscore',
    'app/collections/covergallery'
], function( Backbone, _, CoverGallery ) {

    var Model = Backbone.Model.extend({
        initialize : function() {
            _.bindAll( this, 'activate', 'deactivate')
        },
        defaults : {
            active : false
        },
        activate : function(first){
            this.set('active', true)
            Backbone.dispatcher.trigger('profile:sectionActivate', this, first)
        },

        deactivate : function(){
            this.set('active', false)
        }
    })

    var Profile = {}

    Profile.ListItem = Model.extend({
        activate : function(){
            this.set('active', true)
            Backbone.dispatcher.trigger('profile:listItemActivate', this)
        }
    })

    Profile.bio = new Model({}, {
        url : '/api/bio'
    })

    Profile.photosOf = new Model({}, {
        url : '/api/paphotos'
    })
    Profile.photosOf.parse = function( response, options ) {
        response.photos = new CoverGallery( response.gallery )
        return response
    }

    Profile.ack = new Model({}, {
        url : '/api/acknowledgements'
    })

    Profile.Section = Backbone.Model.extend({
        initialize : function() {
            _.bindAll( this, 'activate', 'deactivate')
        },
        defaults : {
            active : false
        },
        activate : function(first){
            this.set('active', true)
        },
        deactivate : function(){
            this.set('active', false)
        }
    })

    return Profile
})


