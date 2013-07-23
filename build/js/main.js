"use strict";
/*jshint -W002*/

var qContainer = document.getElementById('quotes')

$('#n-container header').click(function(e){
    e.preventDefault()
    $('#n-container').toggleClass('open')
    $(qContainer).toggleClass('short')
})

var filterBar = document.getElementById('filter-bar')
var $filters = $('.filter')

$(filterBar).click(function(e){
    if (e.target.nodeName === 'H3') {
        $filters.removeClass('open')
        $(e.target.parentElement).addClass('open')
    }
    if (!$(e.target).hasClass('filter-bar')){
        e.stopPropagation()
    }
})

$(document).click(function(e){
    if ($filters.hasClass('open')){
        $filters.removeClass('open')
    }
})

$('#logoView').click(function(e){
    $(this).addClass('active')
    $('#titleView').removeClass('active')
    $('#brandList').removeClass('names').addClass('icons')
})

$('#titleView').click(function(e){
    $(this).addClass('active')
    $('#logoView').removeClass('active')
    $('#brandList').removeClass('icons').addClass('names')
})

$('#mockProjectList').click(function(e){
    e.preventDefault()
    $(this).addClass('active')
    $('#mockProjectCovers').removeClass('active')
})

$('#mockProjectCovers').click(function(e){
    e.preventDefault()
    $(this).addClass('active')
    $('#mockProjectList').removeClass('active')
})

$('.close').click(function(e){
    $(this).parents('.open').removeClass('open')
})

var iso = document.getElementById('iso-grid')

$(iso).imagesLoaded( function(){
    isoLoader(iso)
})

