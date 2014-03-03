/* app/views/sections/search.js - Search view
 *
 * searchForm
 * **********
 * uses html in header include
 * creates searchresults collection on success event
 *
 * searchResults
 * *************
 * navigated to on serachresults creation
 * sorts/groups parses results (maybe make model for that)
 * renders to page
 */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    //'app/models/searchQuery',
    'utils/spinner',
    'json'
    //'app/router'
], function( $, Backbone, _, Spinner ) {

    var search = {}

    var Form = Backbone.View.extend({
        initialize : function(options) {
            _.bindAll(this, 'submit', 'onClose' )
        },

        render : function() {
            this.$el.addClass('active')
            this.$('#searchInput').focus()
        },

        events : {
            'submit' : 'submit',
            'click #cancelSearch' : 'onClose'
        },

        submit : function(e) {
            e.preventDefault()
            var r = require('app/router'),
                advanced = this.$el.serializeArray().slice(1),
                q = this.$el.serializeArray().slice(0,1),
                spinner = new Spinner(),
                self = this,
                o

            if ( advanced.length ) {
                o = { name : 'channel' }
                o.value = ''
                _.each(advanced, function(el, i, adv) {
                    o.value += el['value'] + (i + 1 === adv.length ? '' : ' ')
                })
                q[1] = o
            }
            $.get( '/api/search/search', $.param(q)
            ).done( function(d) {
                self.results = new Results({
                    collection : new Backbone.Collection(JSON.parse(d))
                })
                r.router.navigate('/search/results', {trigger: true})
                $('.page').html( self.results.render() )
                spinner.detach()
                self.onClose()
            })
        },

        onClose : function(e) {
            if (e) { e.preventDefault() }
            this.el.reset()
            this.$el.removeClass('active')
        }
    })

    var Results = Backbone.View.extend({
        tagName : 'div',
        className : 'search-results',
        initialize : function(){
            this.groups = this.collection.groupBy('type')
            this.on('change:collection', function(){
                this.groups = this.collection.groupBy('type')
            })
        },

        render : function() {
            if ( this.groups['no results'] ) {
                this.$el.append('<h2>No Results</h2>')
                return this.el
            }

            _.each( this.groups, function( modelsArray, type ) {
                var sec = document.createElement('section')

                switch (type) {
                    case 'gallery':
                        $(sec).append('<h2>Galleries</h2>')
                        _.each( modelsArray, function( model, idx) {
                            var a = document.createElement('a'),
                                p = document.createElement('p')
                            $(a).attr('href', model.get('url')).html(model.get('title'))
                            $(p).append(a)
                            $(sec).append(p)
                        } )
                    break;

                    case 'image':
                        $(sec).append('<h2>Images</h2>')
                        _.each( modelsArray, function( model, idx) {
                            _.each( model.get('images'), function(image, idx) {
                                var img = document.createElement('img')
                                $(img).attr('src', image.thumb)
                                $(sec).append(img)
                            } )
                        } )
                    break;

                    case 'project':
                        $(sec).append('<h2>Projects</h2>')
                        _.each( modelsArray, function( model, idx ) {
                            var a = document.createElement('a'),
                                p = document.createElement('p')
                            $(a).attr('href', model.get('url')).html(model.get('title'))
                            $(p).append(a)
                            $(sec).append(p)
                        } )
                    break;

                    case 'article':
                        $(sec).append('<h2>Articles</h2>')
                        _.each( modelsArray, function( model, idx ) {
                            var a = document.createElement('a'),
                                p = document.createElement('p'),
                                p2 = document.createElement('p')
                            $(a).attr('href', model.get('url')).html(model.get('title'))
                            $(p).append(a)
                            $(p2).append(model.get('summary'))
                            $(sec).append(p)
                            $(sec).append(p2)
                        } )
                    break;

                    case 'film':
                        $(sec).append('<h2>Films</h2>')
                        _.each( modelsArray, function( model, idx) {
                            var img = document.createElement('img'),
                                a = document.createElement('a'),
                                p = document.createElement('p'),
                                p2 = document.createElement('p')
                            $(img).attr('src', model.get('thumb'))
                            $(a).attr('href', model.get('url')).html('<p>'+model.get('title')+'</p>').prepend(img)
                            $(p).append(a)
                            $(p2).append( model.get('summary') )
                            $(sec).append(p).append(p2)
                        } )
                    break;

                    case 'video':
                        $(sec).append('<h2>Project Videos</h2>')
                        _.each( modelsArray, function( model, idx) {
                            var a = document.createElement('a'),
                                p = document.createElement('p'),
                                p2 = document.createElement('p')
                            $(a).attr('href', model.get('url')).html(model.get('title'))
                            $(p).append(a)
                            $(p2).append( model.get('summary') )
                            $(sec).append(p).append(p2)
                        } )
                    break;

                    case 'book':
                        $(sec).append('<h2>Books</h2>')
                        _.each( modelsArray, function( model, idx) {
                            var a = document.createElement('a'),
                                p = document.createElement('p'),
                                p2 = document.createElement('p')
                            $(a).attr('href', model.get('url')).html(model.get('title'))
                            $(p).append(a)
                            $(p2).append( model.get('summary') )
                            $(sec).append(p).append(p2)
                        } )
                    break;

                    default:
                    break;
                }
                this.$el.append(sec)
            }, this)

            return this.el
        }
    })

    search.Form = Form
    search.Results = Results

    return search
})



