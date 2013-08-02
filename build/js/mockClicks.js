"use strict";
var $showcase = $('#showcaseContainer')

$('#mockProjectList').click(function(e){
    e.preventDefault()
    $showcase.empty()
    $('#starfield').remove()

    var q = 'mockProjectList'

    /*
    if (e.target.baseURI.indexOf('photography') > -1) {
        // photography
        q = 'mockPhotoList'
    } else if (e.target.baseURI.indexOf('project') > -1){
        q = 'mockProjectList'
    } else if (e.target.baseURI.indexOf('film') > -1) {
        q = 'mockFilmList'
    }
    */

    $.get('/templates/includes/list-showcase.php', q, function(d){
        $showcase.append(d)
    })
})

$('#mockProjectCovers').click(function(e){
    e.preventDefault()
    $showcase.empty()
    $('#starfield').remove()

    var q = 'mockProjectCovers'
    /*

    if (e.target.baseURI.indexOf('photography') > -1) {
        // photography
        q = 'mockPhotoCovers'
    } else if (e.target.baseURI.indexOf('project') > -1){
        q = 'mockProjectCovers'
    } else if (e.target.baseURI.indexOf('film') > -1) {
        q = 'mockFilmCovers'
        $.get('/templates/includes/film-grid.php', q, function(d){
            $showcase.append(d)
        }).done(function(){
            isoLoader('#iso-grid')
        })
        return
    }
    */

    $.get('/templates/includes/image-showcase.php', q, function(d){
        $showcase.append(d)
    }).done(function(){
        isoLoader('#iso-grid')
        $('#iso-grid').css('overflow', 'visible')
    })
})

$('#starfieldView').click(function(e){
    e.preventDefault()
    $showcase.empty()
    $('#starfield').remove()
    stars()
})

$('#mockImageGallery').click(function(e){
    var s;
    e.preventDefault()
    $showcase.empty()
    
    //if ( e.view.location.search ) { s = e.view.location.search.slice(1) }
        
    $.get('/templates/includes/image-showcase.php', s || 'single-project', function(d){
        $showcase.append(d)
    }).done(function(){
        isoLoader('#iso-grid')
        fbLoader()
    })
})

$('#mockVideo').click(function(e){
    e.preventDefault()
    $showcase.empty()

    $.get('/templates/includes/video-showcase.php', function(d){
        $showcase.append(d)
    })
})

$('#mockInfo').click(function(e){
    e.preventDefault()
    $showcase.empty()

    $.get('/templates/includes/text-showcase.php','mockInfo', function(d){
        $showcase.append(d)
    })
})

$('#mockRelated').click(function(e){
    e.preventDefault()
    $showcase.empty()

    $.get('/templates/includes/list-showcase.php', 'mockRelated', function(d){
        $showcase.append(d)
    })
})

$('#mockTag').click(function(e){
    e.preventDefault()
    
    window.location = '/templates/projects.php'
})

$('#mockPressList').click(function(e){
    e.preventDefault()

    $.get('/templates/includes/list-showcase.php', 'mockPressList', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$showcase.on('click', '#mockPress', function(e){
    e.preventDefault()

    $.get('/templates/includes/text-showcase.php', 'mockPress', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$('#mockBio').click(function(e) {
    e.preventDefault()

    $.get('/templates/includes/text-showcase.php', 'mockBio', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$('#mockAwardList').click(function(e){
    e.preventDefault()

    $.get('/templates/includes/list-showcase.php', 'mockAwardList', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$('#mockArticleList').click(function(e){
    e.preventDefault()

    $.get('/templates/includes/list-showcase.php', 'mockArticleList', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$showcase.on('click', '#mockArticle', function(e){
    e.preventDefault()

    $.get('/templates/includes/text-showcase.php', 'mockArticle', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

