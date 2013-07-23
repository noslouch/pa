"use strict";
var $showcase = $('#showcaseContainer')

$showcase.on('click', '#mockFancybox', function(e){
    e.preventDefault()
    
    // to be implemented
    alert('Image Popup')
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
        console.log('hit')
        $('#iso-grid').css('overflow', 'visible')
    })
})

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
    
    window.location = '/pa/templates/projects.php'
})

$('#mockPressList').click(function(e){
    e.preventDefault()

    $.get('/pa/templates/includes/list-showcase.php', 'mockPressList', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$showcase.on('click', '#mockPress', function(e){
    e.preventDefault()

    $.get('/pa/templates/includes/text-showcase.php', 'mockPress', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$('#mockBio').click(function(e) {
    e.preventDefault()

    $.get('/pa/templates/includes/text-showcase.php', 'mockBio', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$('#mockAwardList').click(function(e){
    e.preventDefault()

    $.get('/pa/templates/includes/list-showcase.php', 'mockAwardList', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$('#mockArticleList').click(function(e){
    e.preventDefault()

    $.get('/pa/templates/includes/list-showcase.php', 'mockArticleList', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

$showcase.on('click', '#mockArticle', function(e){
    e.preventDefault()

    $.get('/pa/templates/includes/text-showcase.php', 'mockArticle', function(d){
        $showcase.empty()
        $showcase.append(d)
    })
})

