"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

PA.router = new PA.Router()
PA.app = new PA.App({ el : document })
Backbone.history.start({pushState: true, root: "/"})

