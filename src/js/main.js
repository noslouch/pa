"use strict";
/*jshint -W002*/

$('#n-container').click(function(e){
    e.preventDefault()
    $(this).toggleClass('open')
})

var filterBar = document.getElementById('filter-bar')

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

var iso = document.getElementById('iso-grid')

$(iso).imagesLoaded( function(){
    isoLoader(iso)
})

var $showcase = $('#showcaseContainer')

$('#mockImageGallery').click(function(e){
    e.preventDefault()
    $showcase.empty()

    $.get('/pa/templates/includes/image-showcase.php', 'single-project', function(d){
        $showcase.append(d)
    }).done(function(){
        isoLoader('#iso-grid')
    })
})

$('#mockVideo').click(function(e){
    e.preventDefault()
    $showcase.empty()

    $.get('/pa/templates/includes/video-showcase.php', function(d){
        $showcase.append(d)
    })
})

$('#mockInfo').click(function(e){
    e.preventDefault()
    $showcase.empty()

    $.get('/pa/templates/includes/text-showcase.php','mockInfo', function(d){
        $showcase.append(d)
    })
})

$('#mockRelated').click(function(e){
    e.preventDefault()
    $showcase.empty()

    $.get('/pa/templates/includes/list-showcase.php', 'mockRelated', function(d){
        $showcase.append(d)
    })
})

$('#mockTag').click(function(e){
    e.preventDefault()
    
    window.location = '/pa/templates/projects-filtered.php'
})

$('#mockProjectList').click(function(e){
    e.preventDefault()
    $showcase.empty()
    var q

    if (e.target.baseURI.indexOf('photography') > -1) {
        // photography
        q = 'mockPhotoList'
    } else if (e.target.baseURI.indexOf('project') > -1){
        q = 'mockProjectList'
    } else if (e.target.baseURI.indexOf('film') > -1) {
        q = 'mockFilmList'
    }

    $.get('/pa/templates/includes/list-showcase.php', q, function(d){
        $showcase.append(d)
    })
})

$('#mockProjectCovers').click(function(e){
    e.preventDefault()
    $showcase.empty()

    var q

    if (e.target.baseURI.indexOf('photography') > -1) {
        // photography
        q = 'mockPhotoCovers'
    } else if (e.target.baseURI.indexOf('project') > -1){
        q = 'mockProjectCovers'
    } else if (e.target.baseURI.indexOf('film') > -1) {
        q = 'mockFilmCovers'
        $.get('/pa/templates/includes/film-grid.php', q, function(d){
            $showcase.append(d)
        }).done(function(){
            isoLoader('#iso-grid')
        })
        return
    }

    $.get('/pa/templates/includes/image-showcase.php', q, function(d){
        $showcase.append(d)
    }).done(function(){
        isoLoader('#iso-grid')
    })
})

$showcase.on('click', '#mockFancybox', function(e){
    e.preventDefault()
    
    // to be implemented
    alert('Image Popup')
})
