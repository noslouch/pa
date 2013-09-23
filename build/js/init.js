/* init.js */
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox.pack',
        json : 'lib/json/json2',
        'app/router' : 'app/router.@@hash',
        'app/collections/covergallery' : 'app/collections/covergallery.@@hash',
        'app/collections/films' : 'app/collections/films.@@hash',
        'app/collections/instagrams' : 'app/collections/instagrams.@@hash',
        'app/collections/photography' : 'app/collections/photography.@@hash',
        'app/collections/profile' : 'app/collections/profile.@@hash',
        'app/collections/projects' : 'app/collections/projects.@@hash',
        'app/collections/showcases' : 'app/collections/showcases.@@hash',
        'app/models/album' : 'app/models/album.@@hash',
        'app/models/cover' : 'app/models/cover.@@hash',
        'app/models/film' : 'app/models/film.@@hash',
        'app/models/profilesection' : 'app/models/profilesection.@@hash',
        'app/models/project' : 'app/models/project.@@hash',
        'app/models/searchQuery' : 'app/models/searchQuery.@@hash',
        'app/models/showcase' : 'app/models/showcase.@@hash',
        'app/views/chrome' : 'app/views/chrome.@@hash',
        'app/views/filterviews' : 'app/views/filterviews.@@hash',
        'app/views/header' : 'app/views/header.@@hash',
        'app/views/home' : 'app/views/home.@@hash',
        'app/views/page' : 'app/views/page.@@hash',
        'app/views/profileviews' : 'app/views/profileviews.@@hash',
        'app/views/projects' : 'app/views/projects.@@hash',
        'app/views/search' : 'app/views/search.@@hash',
        'app/views/showcaseviews' : 'app/views/showcaseviews.@@hash',
        'app/views/singleviews' : 'app/views/singleviews.@@hash'
    },
    shim : {
        'jquery': {
            exports: '$'
        },
        'isotope' : ['jquery'],
        'bbq' : {
            deps : ['jquery']
        },
        'foundation' : {
            deps : ['jquery']
        },
        'tooltips' : {
            deps : ['jquery', 'foundation']
        },
        'fancybox' : {
            deps : ['jquery']
        },
        'json' : {
            deps : ['jquery']
        }
    }
})

require( ['jquery', 'underscore', 'backbone', 'app/router', 'app/views/chrome'],
function( $, _, Backbone, Router ){
    Backbone.history.start({ pushState : true, root : '/' })
} )
