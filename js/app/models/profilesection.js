/* app/models/profilesection.js - All Profile Section models */
'use strict';

define([
    'backbone',
    'underscore'
], function( Backbone, _ ) {

    var Model = Backbone.Model.extend({
        initialize : function() {
            _.bindAll( this, 'activate', 'deactivate')
        },
        active : false,
        activate : function(first){
            this.active = true
            var r = require( 'app/router' )
            r.router.navigate( '/profile/' + this.section, {replace : first} )
            Backbone.dispatcher.trigger('profile:sectionActivate', this)
        },

        deactivate : function(){
            this.active = false
        }
    })

    var Single = Model.extend({
        parse : function(r) {
            return r[0]
        }
    })

    var Profile = {}

    Profile.ListItem = Model.extend({
        parse : function( response, options ) {
            response.htmlDate = this.makeHtmlDate( response.timestamp )
            response.date = this.parseDate( response.timestamp )
            return response
        },

        initialize : function(item, options){
            _.bindAll( this, 'activate', 'deactivate' )
            this.set({
                //htmlDate : this.makeHtmlDate( this.get('date') ),
                //date : this.parseDate( this.get('date') )
            })
        },

        activate : function(){
            //this.active = true
            var r = require( 'app/router' )
            r.router.navigate( '/profile/' + this.collection.section + '/' + this.url() )
            Backbone.dispatcher.trigger('profile:listItemActivate', this)
        },

        url : function() {
            return this.get('url')
        }
    })

    Profile.Bio = Single.extend({
        url : '/api/bio',
        section : 'bio'
    })

    Profile.PhotosOf = Single.extend({
        url : '/api/paphotos',
        section : 'photos-of-pa'
    })

    Profile.Acknowledgements = Single.extend({
        url : '/api/acknowledgements',
        section: 'acknowledgements'
    })

    return Profile
})


