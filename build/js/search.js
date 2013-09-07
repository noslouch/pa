/*global Spinner*/

/* views/search.js - Search view
 *
 * searchForm
 * **********
 * uses html in header include
 * creates a new search query on show
 * sets keywords on submit and calls model's search method
 * advanced search dimensions are set/unset with checkboxes
 * creates searchresults collection on success event
 *
 * searchResults
 * *************
 * navigated to on serachresults creation
 * sorts/groups parses results (maybe make model for that)
 * renders to page
 */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.SearchForm = Backbone.View.extend({
    initialize : function(options) {
        this.model = new PA.SearchQuery()
        this.page = options.page
        _.bindAll(this, 'submit', 'close')
    },

    render : function() {
        this.keywords = this.$('#keywords')
        this.$el.addClass('active')
        this.$('#searchInput').focus()
    },
    events : {
        'submit' : 'submit',
        'click #cancelSearch' : 'close'
    },
    submit : function(e) {
        e.preventDefault()

        // FIND A BETTER METHOD FOR THIS
        PA.app.pageView.$el.empty()

        var spinner = new Spinner()
        var keywords = this.keywords.val().trim(),
            self = this

        this.model.set( 'keywords' , keywords )
        this.model.search()
        .done(function(d){
            self.page.set( { 
                page : new PA.SearchResults({
                    collection : new Backbone.Collection(JSON.parse(d))
                }),
                className : 'search-page',
                outlineTitle : 'Search Results'
            })
            PA.router.navigate('/search/results')
            spinner.detach()
        })
        this.close()
    },
    close : function(e) {
        if (e) { e.preventDefault() }
        this.keywords.val('')
        this.$el.removeClass('active')
    }
})

PA.SearchResults = Backbone.View.extend({
    tagName : 'div',
    className : 'search-results',
    initialize : function(){
        this.groups = this.collection.groupBy('type')

        this.on('change:collection', function(){
            this.groups = this.collection.groupBy('type')
        })
    },

    render : function() {
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
                        $(img).attr('src', model.thumb)
                        $(a).attr('href', model.get('url')).html(model.get('title'))
                        $(p).append(a)
                        $(p2).append( model.get('summary') )
                        $(sec).append(img).append(p).append(p2)
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
            }
            this.$el.append(sec)
        }, this)
        return this.el
    }
})

