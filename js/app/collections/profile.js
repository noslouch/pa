/* app/collections/profilesections.js - All Profile Section collections */
'use strict';

define([
    'backbone',
    'underscore',
    'app/router',
    'app/models/profilesection'
], function( Backbone, _, Router, Models ) {

    var Collection = Backbone.Collection.extend({
        initialize : function() {
            _.bindAll( this, 'activate', 'deactivate' )
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

    Profile.Press = Collection.extend({
        model : Models.ListItem,
        url : '/api/press',
        section : 'press'
    })

    Profile.Awards = Collection.extend({
        model : Models.ListItem,
        url : '/api/awards',
        section : 'awards'
    })

    Profile.ArticlesBy = Collection.extend({
        model : Models.ListItem,
        url : '/api/paauthor',
        section : 'articles-by-pa'
    })

    Profile.ArticlesAbout = Collection.extend({
        model : Models.ListItem,
        url : '/api/pasubject',
        section : 'articles-about-pa'
    })

    Profile.Interviews = Collection.extend({
        model : Models.ListItem,
        url : '/api/interviews',
        section : 'interviews'
    })

    Profile.Transcripts = Collection.extend({
        model : Models.ListItem,
        url : '/api/transcripts',
        section : 'transcripts'
    })

    return new Profile()
})


