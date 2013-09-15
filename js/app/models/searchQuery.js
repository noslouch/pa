/* app/models/searchQuery.js - Search Query model
 * created by search form input
 * sends search request to EE's Super Search
 */
'use strict';

define([
    'jquery',
    'backbone'
], function( $, Backbone ) {

    var SearchQuery = Backbone.Model.extend({
        url : '/api/search/search',
        search : function(){
            var query = this.toJSON()
            var promise = $.get( this.url, query )
            return promise
        }
    })

    return SearchQuery
})

