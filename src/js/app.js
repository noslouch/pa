"use strict";
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

Backbone.Model.prototype.makeHtmlDate = function(dateString, onlyYear) {
    var res = [],
        d = parseInt(dateString, 10)

    d = new Date(d)
    onlyYear = onlyYear || true

    res[0] = d.getFullYear()
    if (onlyYear) {
        return res[0]
    }
    res[1] = d.getMonth() + 1
    res[2] = d.getDate()
    return res.join("-")
}

Backbone.Model.prototype.parseDate = function(dateString) {
    return new Date( parseInt(dateString, 10) )
}

PA.router = new PA.Router()
PA.app = new PA.App({ el : document })
Backbone.history.start({pushState: true, root: "/"})

