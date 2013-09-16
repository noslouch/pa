/* app/collections/profilesections.js - All Profile Section collections */
'use strict';

define([
    'backbone',
    'underscore',
    'app/router',
    'app/models/profilesection'
], function( Backbone, _, Router, Models ) {
    console.log(Models)

    var Collection = Backbone.Collection.extend({
        parse : function( response, options ) {
            _.each( response, function( model ) {
                model.htmlDate = Backbone.Model.prototype.makeHtmlDate( model.timestamp )
                model.date = Backbone.Model.prototype.parseDate( model.timestamp )
                //model.segment = model.url
            } )
            return response
        },
        initialize : function(model, options) {
            _.bindAll( this, 'activate', 'deactivate' )
            this.section = options.section
        },
        active: false,
        activate : function(href){
            this.active = true
            var r = require( 'app/router' )
            r.router.navigate( '/profile/' + this.section )
            Backbone.dispatcher.trigger('profile:sectionActivate', this)
        },
        deactivate : function() {
            this.active = false
        }
    })

    var Profile = {}
    Profile.bio = new Models.Bio()
    Profile['photos-of-pa'] = new Models.PhotosOf()
    Profile.acknowledgements = new Models.Acknowledgements()

    Profile.press = new Collection({
    }, {
        model : Models.ListItem,
        section : 'press',
        url : '/api/press'
    })

    Profile.awards = new Collection({
    }, {
        model : Models.ListItem,
        section : 'awards',
        url : '/api/awards'
    })

    Profile['articles-by-pa'] = new Collection({
    }, {
        model : Models.ListItem,
        section : 'articles-by-pa',
        url : '/api/paauthor'
    })

    Profile['articles-about-pa'] = new Collection({
    }, {
        model : Models.ListItem,
        section : 'articles-about-pa',
        url : '/api/pasubject'
    })

    Profile.interviews = new Collection({
    }, {
        model : Models.ListItem,
        section : 'interviews',
        url : '/api/interviews'
    })

    Profile.transcripts = new Collection({
    }, {
        model : Models.ListItem,
        section : 'transcripts',
        url : '/api/transcripts'
    })

    return Profile
})


