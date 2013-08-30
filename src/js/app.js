/*jshint -W002*/
/*global moment*/
'use strict';
var PA = PA || {}
PA.dispatcher = PA.dispatcher || _.extend({}, Backbone.Events)

Backbone.Model.prototype.makeHtmlDate = function(dateString, onlyYear) {
    var res = [],
        d = parseInt(dateString, 10)

    d = new Date(d)

    res[0] = d.getFullYear()
    if (onlyYear) {
        return res[0]
    }
    res[1] = d.getMonth() + 1
    res[2] = d.getDate()
    return res.join("-")
}

Backbone.Model.prototype.parseDate = function(dateString) {
    return moment( new Date( parseInt(dateString, 10) ) )
}

PA.router = new PA.Router()
PA.app = new PA.App({ el : document })
Backbone.history.start({pushState: true, root: "/"})

$(function(){
    var qContainer = document.getElementById('quotes')

    $('#n-container header').click(function(e){
        e.preventDefault()
        $('#n-container').toggleClass('open')
        $(qContainer).toggleClass('short')
    })

    try {
        fbLoader()
    } catch(e) {
        console.log('no fancybox')
    }

    checkScroll()

    $('#searchIcon').click(function(e){
        e.preventDefault()
        $('#searchForm').addClass('active')
        $('#searchInput').focus()
    })
    $('#cancelSearch').click(function(e){
        e.preventDefault()
        $('#searchInput').val('')
        $('#searchForm').removeClass('active')
    })
})
