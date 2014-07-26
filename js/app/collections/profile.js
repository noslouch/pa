/* app/collections/profile.js - All Profile Section collections */
'use strict';

define([
    'backbone',
    'underscore',
    'app/collections/covergallery',
], function( Backbone, _, CoverGallery ) {

    var Section = Backbone.Model.extend({
        initialize : function() {
            _.bindAll( this, 'activate', 'deactivate')
        },
        gt : function(t) {
            return this.get('content').get(t)
        },
        defaults : {
            active : false
        },
        activate : function(urlTitle){
            this.set('active', true, {urlTitle : urlTitle ? true : false})
        },
        deactivate : function(silent){
            this.set({'active' : false},{ silent : silent || false })
        }
    })

    var Collection = Backbone.Collection.extend({
        initialize : function( models, options ) {
            this.url = options.url
        },
        parse : function( response, options ) {
            _.each( response, function( model ) {
                model.htmlDate = Backbone.Model.prototype.makeHtmlDate( model.timestamp )
                model.date = Backbone.Model.prototype.parseDate( model.timestamp )
            } )
            return response
        }
    })

    var bio = new Backbone.Model()
    bio.url = "/api/bio"

    var photosOf = new Backbone.Model()
    photosOf.url = '/api/paphotos'
    photosOf.parse = function( response, options ) {
        response.photos = new CoverGallery( response.gallery )
        return response
    }

    var ack = new Backbone.Model()
    ack.url = '/api/acknowledgements'

    var press = new Collection([], {
        model : Backbone.Model,
        url : '/api/press'
    })

    var awards = new Collection([], {
        model : Backbone.Model,
        url : '/api/awards'
    })

    var paAuthor = new Collection([], {
        model : Backbone.Model,
        url : '/api/paauthor'
    })

    var paSubject = new Collection([], {
        model : Backbone.Model,
        url : '/api/pasubject'
    })

    var interviews = new Collection([], {
        model : Backbone.Model,
        url : '/api/interviews'
    })

    var transcripts = new Collection([], {
        model : Backbone.Model,
        url : '/api/transcripts'
    })

    var profileSections = new Backbone.Collection([
        new Section({ id : 'bio', content : bio })
        , new Section({ id : 'acknowledgements', content : ack })
        , new Section({ id : 'press', content : press })
        , new Section({ id : 'awards', content : awards })
        , new Section({ id : 'photos-of-pa', content : photosOf })
        , new Section({ id : 'articles-by-pa', content : paAuthor })
        //, new Section({ id : 'articles-about-pa', content : paSubject })
        , new Section({ id : 'interviews', content : interviews })
        //, new Section({ id : 'transcripts', content : transcripts })
    ])
    profileSections.section = function(t) {
        return this._byId[t].get('content')
    }
    profileSections.active = function() {
        return this.findWhere({ active : true }).get('content')
    }

    return profileSections
})
