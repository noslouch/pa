"use strict";

var PA = PA || {},
    JST = {}

JST.thumbTemplate = [
    '<div class="wrapper">',
        '<a href="<%= url %>"<% if (!cover) { %> class="fancybox" rel="gallery"<% } %>>',
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
            '<date datetime="<%= date %>"><%= date %></date>',
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
        '<a href="<%= path %><%= url %>">',
            '<h4>',
                '<%= title %>',
            '</h4>',
            '<p>',
                '<%= summary %>',
            '</p>',
        '</a>',
    '</li>'
].join('\n')


JST.awardItemPartial = [
    '<li>',
        '<h4>Award Title</h4>',
        '<p><%= summary %></p>',
    '</li>'
].join('\n')

JST.filmThumb = [
    '<a href="<%= url %>">',
        '<div class="img">',
            '<img src="<%= thumb %>">',
        '</div>',
        '<h3><%= title %></h3>',
        '<p><%= summary %></p>',
    '</a>'
].join('\n')

JST.filmRow = '<div class="film-row"></div>'

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
