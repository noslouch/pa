/* init.js */
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.138264094955636737',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.138264094955636737',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox.pack',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.138264094955636737',
        'app/collections/covergallery' : 'app/collections/covergallery.138264094955636737',
        'app/collections/films' : 'app/collections/films.138264094955636737',
        'app/collections/instagrams' : 'app/collections/instagrams.138264094955636737',
        'app/collections/photography' : 'app/collections/photography.138264094955636737',
        'app/collections/profile' : 'app/collections/profile.138264094955636737',
        'app/collections/projects' : 'app/collections/projects.138264094955636737',
        'app/collections/showcases' : 'app/collections/showcases.138264094955636737',
        'app/models/album' : 'app/models/album.138264094955636737',
        'app/models/cover' : 'app/models/cover.138264094955636737',
        'app/models/film' : 'app/models/film.138264094955636737',
        'app/models/profile' : 'app/models/profile.138264094955636737',
        'app/models/project' : 'app/models/project.138264094955636737',
        'app/models/searchQuery' : 'app/models/searchQuery.138264094955636737',
        'app/models/showcase' : 'app/models/showcase.138264094955636737',
        'app/views/chrome' : 'app/views/chrome.138264094955636737',
        'app/views/filterviews' : 'app/views/filterviews.138264094955636737',
        'app/views/header' : 'app/views/header.138264094955636737',
        'app/views/home' : 'app/views/home.138264094955636737',
        'app/views/jumplist' : 'app/views/jumplist.138264094955636737',
        'app/views/page' : 'app/views/page.138264094955636737',
        'app/views/photography' : 'app/views/photography.138264094955636737',
        'app/views/profile' : 'app/views/profile.138264094955636737',
        'app/views/film' : 'app/views/film.138264094955636737',
        'app/views/projects' : 'app/views/projects.138264094955636737',
        'app/views/search' : 'app/views/search.138264094955636737',
        'app/views/showcaseviews' : 'app/views/showcaseviews.138264094955636737',
        'app/views/singlealbum' : 'app/views/singlealbum.138264094955636737',
        'app/views/singlefilm' : 'app/views/singlefilm.138264094955636737',
        'app/views/singleproject' : 'app/views/singleproject.138264094955636737',
        'app/views/singleviews' : 'app/views/singleviews.138264094955636737',
        'utils/spinner' : 'utils/spinner.138264094955636737',
        'utils/quotes' : 'utils/quotes.138264094955636737',
        'utils/fbLoader' : 'utils/fbLoader.138264094955636737',
        'tpl/jst' : 'tpl/jst.138264094955636737'
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
