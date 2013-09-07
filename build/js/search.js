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
    initialize : function() {
        this.model = new PA.SearchQuery()
        this.render()
    },
    render : function() {
        this.keywords = this.$('#keywords')
        this.$el.addClass('active')
        this.$('#searchInput').focus()
    },
    events : {
        'submit' : 'submit',
        'click #cancelSearch' : 'cancel'
    },
    submit : function(e) {
        e.preventDefault()
        var keywords = this.keywords.val().trim()
        this.model.set( 'keywords' , keywords )
        this.model.search()
        .done(function(d){
            console.log(JSON.parse(d))
        })
    },
    cancel : function(e) {
        e.preventDefault()
        this.keywords.val('')
        this.$el.removeClass('active')
        this.remove()
    }
})

