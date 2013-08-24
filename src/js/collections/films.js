/* collections/films.js - All Films
 * used on /films */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.Films = Backbone.Collection.extend({
    model : PA.Film,
    url : '/fixtures/filmFixture.json'
})

PA.films = new PA.Films()
