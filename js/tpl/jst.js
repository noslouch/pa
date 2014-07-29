'use strict';

define([ 'underscore' ],
function( _ ){

    var JST = {}

    JST.noteworthy = [
        '<div class="n-wrapper home">',
            '<section class="n-container" id="n-container">',
                '<header>',
                    '<h2><a href="#n-container">Noteworthy</a></h2>',
                '</header>',
                '<div class="row" id="brickRow"></div>',
            '</section>',
        '</div>'
    ].join('\n')

    JST.brick = [
        '<article class="brick">',
            '<div class="img">',
                '<img src="<%= src %>">',
            '</div>',
            '<a href="<%= link %>"<%= external %>>',
                '<div class="copy">',
                    '<h3><%= title %></h3>',
                    '<%= summary %>',
                '</div>',
            '</a>',
        '</article>'
    ].join('\n')

    JST.quotes = [
        '<div class="quotes" id="quotes">',
            '<div class="container" id="qContainer">',
                '<div class="indicators" id="bullets"></div>',
            '</div>',
        '</div>'
    ].join('\n')

    JST.quoteSlide = [
        '<div class="slide closed">',
            '<h3></h3>',
        '</div>'
    ].join('\n')

    JST.viewer = [
        '<div class="details" id="details">',
            '<div class="project-controls" id="controls"></div>',
        '</div>',
        '<div class="container" id="showcaseContainer"></div>'
    ].join('\n')

    JST.projectDetails = [
        '<header>',
            '<h3><%= title %></h3>',
            '<time datetime="<%= htmlDate %>"><%= date %></time>',
        '</header>',
        '<ul class="tags" id="tags"></ul>'
    ].join('\n')

    JST.profileLinks = [
        '<div class="details">',
            '<ul class="profile-links" id="profileLinks">',
                '<li><a href="/profile/bio" id="bio">Bio/CV</a></li>',
                '<li><a href="/profile/press" id="press">Press</a></li>',
                '<li><a href="/profile/interviews" id="interviews">Interviews</a></li>',
                '<li><a href="/profile/articles-by-pa" id="articles-by-pa">Articles by PA</a></li>',
                '<li><a href="/profile/awards" id="awards">Selected Awards</a></li>',
                '<li><a href="/profile/photos-of-pa" id="photos-of-pa">Photos of PA</a></li>',
                //'<li><a href="/profile/articles-about-pa" id="articles-about-pa">Articles About PA</a></li>',
                //'<li><a href="/profile/transcripts" id="transcripts">Transcripts</a></li>',
                '<li><a href="/profile/acknowledgements" id="acknowledgements">Acknowledgements</a></li>',
            '</ul>',
        '</div>'
    ].join('\n')

    JST.showcaseLinks = [
        '<a href="#" id="<%= cid %>">',
            '<%= title %>',
        '</a>'
    ].join('\n')

    JST.tagLinks = [
        '<span class="type">',
            '<%= type %>',
        '</span>',
        '<div class="links" id="tagLinks"></div>'
    ].join('\n')

    JST.tag = '<a href="/<%= section %>#filter=.<%= className %>"><%= tag %></a>'

    JST.textTemplate = [
        '<article class="<%= type %>">', // .project-info, .press, .bio
            '<%= content %>',
        '</article>'
    ].join('\n')

    JST.textTemplateHeader = [
        '<header>',
            '<h3><%= title %></h3>',
            '<time datetime="<%= htmlDate %>"><%= date %></time>',
        '</header>'
    ].join('\n')

    JST.bioImage = [
        '<div class="img">',
            '<img src="<%= bioImg %>">',
        '</div>'
    ].join('\n')

    JST.textGallery = [
        '<div class="gallery">',
            '<% _.each(images, function(image) { %>',
                '<% print( imageTemplate({ url : image.url, thumb : image.thumb, caption : image.caption }) ) %>',
            '<% }) %>',
        '</div>'
    ].join('\n')

    JST.textGalleryImage = [
        '<div class="img">',
            '<a href="<%= url %>" class="fancybox" rel="gallery">',
                '<img src="<%= thumb %>" alt="<%= caption %>">',
            '</a>',
        '</div>'
    ].join('\n')

    JST.backButton = [
        '<a href="<%= url %>" class="back" id="back"><%= buttonText %></a>'
    ].join('\n')

    JST.videoID = [
        '<div>',
            '<iframe src="http://<% youtube ? print("www.youtube.com/embed/") : print("player.vimeo.com/video/") %><%= videoSrc %>" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
        '</div>'
    ].join('\n')

    JST.iframeVideo = '<div><%= videoSrc %></div>'

    JST.videoCaption = [
        '<div class="video-caption">',
            '<h3><%= title %></h3>',
            '<p><%= content %></p>',
        '</div>'
    ].join('\n')

    JST.thumbTemplate = [
        '<div class="wrapper">',
            '<a href="<%= url %>" class="fancybox" id="<%= id %>" rel="gallery" title="<%= caption %>">',
                '<img src="<%= thumb %>">',
                '<% if (caption) { %>',
                    '<div class="caption">',
                        '<p class="title"><%= caption %></p>',
                    '</div>',
                '<% } %>',
            '</a>',
        '</div>'
    ].join('\n')

    JST.projectCover = [
        '<div class="wrapper">',
            '<a href="<%= url %>" id="<%= id %>" title="<%= caption %>">',
                '<img src="<%= thumb %>" >',
                '<% if (caption) { %>',
                    '<div class="caption">',
                        '<p class="title"><%= caption %></p>',
                        '<% if (year) { %>',
                            '<span class="year"><%= year %></span>',
                        '<% } %>',
                    '</div>',
                '<% } %>',
            '</a>',
        '</div>'
    ].join('\n')

    JST.listHeaderPartial = [
        '<li>',
            '<h3>',
                '<date datetime="<%= htmlDate %>"><%= date %></date>',
            '</h3>',
        '</li>'
    ].join('\n')

    JST.listHiddenHeaderPartial = [
        '<h3 class="visuallyhidden">',
            '<%= title %>',
        '</h3>'
    ].join('\n')

    JST.listItemPartial = [
        '<% if (url) { %>',
            '<a href="<%= path %>/<%= url %>" id="<%= id %>">',
        '<% } %>',
            '<h4>',
                '<%= title %>',
            '</h4>',
            '<p>',
                '<%= summary %>',
            '</p>',
        '<% if (url) { %>',
            '</a>',
        '<% } %>',
    ].join('\n')


    JST.awardItemPartial = [
        '<li>',
            '<h4>Award Title</h4>',
            '<p><%= summary %></p>',
        '</li>'
    ].join('\n')

    JST.gridRow = '<div class="four-column-row"></div>'

    JST.gridThumb = [
        '<a href="<%= url %>">',
            '<div class="img">',
                '<img src="<%= thumb %>">',
            '</div>',
            '<h3><%= title %></h3>',
            '<%= summary %>',
        '</a>'
    ].join('\n')

    JST.projectFilter = [
        '<div class="filter brand" id="brand">',
            '<h3>Brand</h3>',
            '<div class="wrapper"></div>',
        '</div>',
        '<div class="filter" id="industry">',
            '<h3>Industry</h3>',
            '<div class="wrapper"></div>',
        '</div>',
        '<div class="filter" id="type">',
            '<h3>Project Type</h3>',
            '<div class="wrapper"></div>',
        '</div>',
        '<div class="filter view-all" id="all">',
            '<h3><a href="#" data-hash="filter=*">View All</a></h3>',
        '</div>'
    ].join('\n')

    JST.controlsPartial = [
        '<div class="views">',
            '<button class="icon-view active" id="logoView" type="button"></button>',
            '<button class="title-view" id="titleView" type="button"></button>',
        '</div>',
        '<button class="close" id="close" type="button"></button>'
    ].join('\n')

    JST.logoPartial = [
        '<div class="icon">',
            '<a href="#" data-hash="filter=.<%= tagFilter %>" class="has-tip tip-top" data-tooltip title="<%= tag %>">',
                '<img src="<%= logo %>" title="<%= tag %>">',
            '</a>',
        '</div>'
    ].join('\n')

    JST.namePartial = [
        '<h4 class="name">',
            '<a href="#" data-hash="filter=.<%= tagFilter %>">',
                '<%= tag %>',
            '</a>',
        '</h4>'
    ].join('\n')


    JST.jumps = [
        '<h3>Jump To</h3>',
        '<div class="wrapper"></div>'
    ].join('\n')

    JST.mobileJumps = [
        '<select>',
            '<option>Jump To</option>',
        '</select>'
    ].join('\n')

    JST.sorts = [
        '<h3>Sort By</h3>',
        '<div class="wrapper">',
            '<ul>',
                '<li><button data-hash="sort=name" id="name" type="button">Name</button></li>',
                '<li><button data-hash="sort=date" id="date" type="button">Date</button></li>',
            '</ul>',
        '</div>'
    ].join('\n')

    JST.mobileSorts = [
        '<select>',
            '<option>Sort By</option>',
            '<option data-hash="sort=name" id="name">Name</option>',
            '<option data-hash="sort=date" id="date">Date</option>',
        '</select>',
        '<div class="jumps-mobile" id="jumps"></div>'
    ].join('\n')

    JST.views = [
        '<h3>View By</h3>',
        '<div class="wrapper">',
            '<ul>',
                '<li><button class="icon-view active" data-hash="view=cover" id="cover" type="button">Image</button></li>',
                '<li><button class="title-view" data-hash="view=list" id="list" type="button">Title</button></li>',
                //'<li><button class="random-view" data-hash="view=random" id="random" type="button">Stream</button></li>',
           '</ul>',
        '</div>'
    ].join('\n')

    JST.mobileViews = [
        '<select>',
            '<option>View By</option>',
            '<option data-hash="view=cover" id="cover">Image</option>',
            '<option data-hash="view=list" id="list">Title</option>',
            //'<option data-hash="view=random" id="random">Stream</option>',
        '</select>'
    ].join('\n')

    for (var tmpl in JST) {
        if (JST.hasOwnProperty(tmpl)) {
            JST[tmpl] = _.template(JST[tmpl])
        }
    }

    return JST
})
