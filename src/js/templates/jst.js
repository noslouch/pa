"use strict";

var PA = PA || {},
    JST = {}

JST.viewer = [
    '<div class="details" id="details"></div>',
    '<div class="container" id="showcaseContainer"></div>'
].join('\n')

JST.projectDetails = [
    '<header>',
        '<h3><%= title %></h3>',
        '<time datetime="<%= htmlDate %>"><%= date %></time>',
    '</header>',
    '<p class="summary"><%= summary %></p>',
    '<ul class="showcase-links" id="showcaseLinks">',
    '</ul>',
    '<ul class="tags" id="tags"></ul>'
].join('\n')

JST.profileLinks = [
    '<ul class="profile-links" id="profileViewer">',
        '<li><a href="#" id="bio" class="active">Bio/CV</a></li>',
        '<li><a href="#" id="press">Press</a></li>',
        '<li><a href="#" id="awards">Selected Awards</a></li>',
        '<li><a href="#" id="paPhotos">Photos of PA</a></li>',
        '<li><a href="#" id="paAuthor">Articles by PA</a></li>',
        '<li><a href="#" id="paSubject">Articles about PA</a></li>',
        '<li><a href="#" id="interviews">Interviews</a></li>',
        '<li><a href="#" id="transcripts">Transcripts</a></li>',
        '<li><a href="#" id="acknowledgements">Acknowledgements</a></li>',
    '</ul>'
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

JST.tag = '<a href="#"><%= tag %></a>'

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
    '<div class="wrapper">',
        '<a href="<%= url %>" class="button" id="back"><%= buttonText %></a>',
    '</div>'
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
        '<a href="<%= url %>"<% if (!cover) { %> class="fancybox" rel="gallery"<% } %> title="<%= caption %>">',
            '<% if (caption) { %>',
                '<div class="caption">',
                    '<p><%= caption %></p>',
                '</div>',
            '<% } %>',
            '<img src="<% large ? print(lg_thumb) : print(thumb) %>">',
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
    '<li>',
        '<% if (url) { %>',
            '<a href="<%= path %><%= url %>" id="<%= id %>">',
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
    '</li>'
].join('\n')


JST.awardItemPartial = [
    '<li>',
        '<h4>Award Title</h4>',
        '<p><%= summary %></p>',
    '</li>'
].join('\n')

JST.filmRow = '<div class="film-row"></div>'

JST.filmThumb = [
    '<a href="<%= url %>">',
        '<div class="img">',
            '<img src="<%= thumb %>">',
        '</div>',
        '<h3><%= title %></h3>',
        '<p><%= summary %></p>',
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
    '<div class="filter view-all">',
        '<h3><a href="#" data-filter="*">View All</a></h3>',
    '</div>'
].join('\n')

JST.jumps = [
    '<div class="jump-to alpha">',
        '<h3>Jump To</h3>',
        '<div class="wrapper"></div>',
    '</div>'
].join('\n')

JST.sorts = [
    '<div class="sorts">',
        '<h3>Sort By</h3>',
        '<div class="wrapper">',
            '<ul>',
                '<li><button id="alphaSort" type="button">Name</button></li>',
                '<li><button id="dateSort" type="button">Date</button></li>',
            '</ul>',
        '</div> <!-- .wrapper -->',
    '</div> <!-- .sorts -->'
].join('\n')

JST.views = [
    '<div class="views" id="views">',
        '<h3>View By</h3>',
        '<div class="wrapper">',
            '<ul>',
                '<li><button class="icon-view" id="mockProjectCovers" type="button">Cover Image</button></li>',
                '<li><button class="title-view" id="mockProjectList" type="button">Project Title</button></li>',
                '<li><button class="random-view" id="starfieldView" type="button">Random</button></li>',
           '</ul>',
        '</div> <!-- .wrapper -->',
    '</div> <!-- .views -->'
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
        '<a href="#" data-filter=".<%= tag %>">',
            '<img class="has-tip tip-top" data-tooltip src="<%= logo %>" title="<%= tag %>">',
        '</a>',
    '</div>'
].join('\n')

JST.namePartial = [
    '<h4 class="name">',
        '<a href="#" data-filter=".<%= tag %>">',
            '<%= tag %>',
        '</a>',
    '</h4>'
].join('\n')

for (var tmpl in JST) {
    if (JST.hasOwnProperty(tmpl)) {
        JST[tmpl] = _.template(JST[tmpl])
    }
}

PA.jst = JST
