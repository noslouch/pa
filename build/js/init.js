/* init.js */
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.@@hash',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.@@hash',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
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
        'app/models/profile' : 'app/models/profile.@@hash',
        'app/models/project' : 'app/models/project.@@hash',
        'app/models/searchQuery' : 'app/models/searchQuery.@@hash',
        'app/models/showcase' : 'app/models/showcase.@@hash',
        'app/views/chrome' : 'app/views/chrome.@@hash',
        'app/views/filterviews' : 'app/views/filterviews.@@hash',
        'app/views/header' : 'app/views/header.@@hash',
        'app/views/home' : 'app/views/home.@@hash',
        'app/views/jumplist' : 'app/views/jumplist.@@hash',
        'app/views/page' : 'app/views/page.@@hash',
        'app/views/photography' : 'app/views/photography.@@hash',
        'app/views/profile' : 'app/views/profile.@@hash',
        'app/views/film' : 'app/views/film.@@hash',
        'app/views/projects' : 'app/views/projects.@@hash',
        'app/views/search' : 'app/views/search.@@hash',
        'app/views/showcaseviews' : 'app/views/showcaseviews.@@hash',
        'app/views/singlealbum' : 'app/views/singlealbum.@@hash',
        'app/views/singlefilm' : 'app/views/singlefilm.@@hash',
        'app/views/singleproject' : 'app/views/singleproject.@@hash',
        'app/views/singleviews' : 'app/views/singleviews.@@hash',
        'utils/spinner' : 'utils/spinner.@@hash',
        'utils/quotes' : 'utils/quotes.@@hash',
        'utils/fbLoader' : 'utils/fbLoader.@@hash',
        'tpl/jst' : 'tpl/jst.@@hash'
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
    },
    waitSeconds : 20
})

require( ['jquery', 'underscore', 'backbone', 'app/router', 'app/views/chrome'],
function( $, _, Backbone, Router ){
    Backbone.history.start({ pushState : true, root : '/' })
} )
