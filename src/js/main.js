"use strict";
/*jshint -W002*/

$('#n-container').click(function(e){
    e.preventDefault()
    $(this).toggleClass('open')
})

var filterBar = document.getElementById('pro-filter')

$(filterBar).on('click', 'h3', function(e){
    $(e.target.parentElement).toggleClass('open')
})

$('#logoView').click(function(e){
    $('#brandList').removeClass('names').addClass('icons')
})

$('#titleView').click(function(e){
    $('#brandList').removeClass('icons').addClass('names')
})

$('.close').click(function(e){
    $(this).parents('.open').removeClass('open')
})
