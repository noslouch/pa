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

JST.listTemplate = [
    '<ul>',
        '<li id="date">',
            '<h3><%= date %></h3>',
        '</li>',
    '</ul>',
].join('\n')

JST.listItemPartial = [
    '<li>',
        '<h4>',
            '<a href="<%= section %>/<%= url %>">',
                '<%= title %>',
            '</a>',
        '</h4>',
    '</li>'
].join('\n')


for (var tmpl in JST) {
    if (JST.hasOwnProperty(tmpl)) {
        JST[tmpl] = _.template(JST[tmpl])
    }
}

PA.jst = JST
