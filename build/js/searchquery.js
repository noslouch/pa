/* models/searchQuery.js - Search Query model
 * created by search form input
 * sends search request to EE's Super Search
 */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.SearchQuery = Backbone.Model.extend({
    url : '/api/search/search',
    search : function(){
        var query = this.toJSON()
        var promise = $.get( this.url, query )
        return promise
    }
})
