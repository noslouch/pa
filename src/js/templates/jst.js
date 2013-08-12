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

for (var tmpl in JST) {
    if (JST.hasOwnProperty(tmpl)) {
        JST[tmpl] = _.template(JST[tmpl])
    }
}

PA.jst = JST
