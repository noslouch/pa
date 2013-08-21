/* collections/profilesections.js - Collection of Profile Sections */

'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend( {}, Backbone.Events )

PA.ProfileSections = Backbone.Collection.extend({

    model : PA.ProfileSection

})
