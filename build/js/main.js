"use strict";
/*jshint -W002*/

var qContainer = document.getElementById('quotes'),
    $showcase = $('#showcaseContainer'),
    PA = PA || {}

$('#n-container header').click(function(e){
    e.preventDefault()
    $('#n-container').toggleClass('open')
    $(qContainer).toggleClass('short')
})

$(document).on('click', '#starfield a', function(e){
    //e.preventDefault()
    //e.stopPropagation()
})

var filterBar = document.getElementById('filter-bar')
var $filters = $('.filter a')

/*

$filters.click(function(){
    var f = $(this).attr('data-filter')

    if (!!$('#starfield').length) {
        $('#starfield').remove()

        $.get('/templates/includes/image-showcase.php',  'mockProjectCovers', function(d){
            $showcase.append(d)
        }).done(function(){
            isoLoader('#iso-grid')
            $('#iso-grid').css('overflow', 'visible')
            $('#iso-grid').isotope({filter : f })
        })
    } else {
        $('#iso-grid').isotope({filter : f })
    }

    $(filterBar).children('.open').removeClass('open')
    return false
})

$('#logoView').click(function(e){
    $(this).addClass('active')
    $('#titleView').removeClass('active')
    $('#brandList').removeClass('names').addClass('icons')
    e.preventDefault()
    e.stopPropagation()
})

$('#titleView').click(function(e){
    $(this).addClass('active')
    $('#logoView').removeClass('active')
    $('#brandList').removeClass('icons').addClass('names')
    e.preventDefault()
    e.stopPropagation()
})

$('#views').click(function(e){
    e.preventDefault()
    $(e.delegateTarget).find('.active').removeClass('active')
    $(e.target).addClass('active')
})

$('.viewer li a').click(function(e){
    $('.viewer .active').removeClass('active')
    $(this).addClass('active')
})

$('.close').click(function(e){
    $(this).parents('.open').removeClass('open')
})
*/

var iso = document.getElementById('iso-grid')


$('time').each(function(){
    // to be implemented
})

$(function(){
    try {
        fbLoader()
    } catch(e) {
        console.log('no fancybox')
    }

    checkScroll()


})

//$(window).load(function(){
    //$(iso).imagesLoaded( function(){
        //isoLoader(iso)
    //})
//})
//


