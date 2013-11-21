/* init.js */
/*global document*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.138506856892722009',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.138506856892722009',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.138506856892722009',
        'app/collections/covergallery' : 'app/collections/covergallery.138506856892722009',
        'app/collections/films' : 'app/collections/films.138506856892722009',
        'app/collections/instagrams' : 'app/collections/instagrams.138506856892722009',
        'app/collections/photography' : 'app/collections/photography.138506856892722009',
        'app/collections/profile' : 'app/collections/profile.138506856892722009',
        'app/collections/projects' : 'app/collections/projects.138506856892722009',
        'app/collections/showcases' : 'app/collections/showcases.138506856892722009',
        'app/models/album' : 'app/models/album.138506856892722009',
        'app/models/cover' : 'app/models/cover.138506856892722009',
        'app/models/film' : 'app/models/film.138506856892722009',
        'app/models/profile' : 'app/models/profile.138506856892722009',
        'app/models/project' : 'app/models/project.138506856892722009',
        'app/models/searchQuery' : 'app/models/searchQuery.138506856892722009',
        'app/models/showcase' : 'app/models/showcase.138506856892722009',
        'app/views/chrome' : 'app/views/chrome.138506856892722009',
        'app/views/filterviews' : 'app/views/filterviews.138506856892722009',
        'app/views/header' : 'app/views/header.138506856892722009',
        'app/views/home' : 'app/views/home.138506856892722009',
        'app/views/jumplist' : 'app/views/jumplist.138506856892722009',
        'app/views/page' : 'app/views/page.138506856892722009',
        'app/views/photography' : 'app/views/photography.138506856892722009',
        'app/views/profile' : 'app/views/profile.138506856892722009',
        'app/views/film' : 'app/views/film.138506856892722009',
        'app/views/projects' : 'app/views/projects.138506856892722009',
        'app/views/search' : 'app/views/search.138506856892722009',
        'app/views/showcaseviews' : 'app/views/showcaseviews.138506856892722009',
        'app/views/singlealbum' : 'app/views/singlealbum.138506856892722009',
        'app/views/singlefilm' : 'app/views/singlefilm.138506856892722009',
        'app/views/singleproject' : 'app/views/singleproject.138506856892722009',
        'app/views/singleviews' : 'app/views/singleviews.138506856892722009',
        'utils/spinner' : 'utils/spinner.138506856892722009',
        'utils/quotes' : 'utils/quotes.138506856892722009',
        'utils/fbLoader' : 'utils/fbLoader.138506856892722009',
        'tpl/jst' : 'tpl/jst.138506856892722009'
    },
    map : {
        '*' : {
            'is' : 'lib/require-is/is'
        }
    },
    config : {
        'lib/require-is/is' : {
            mobile : 'ontouchstart' in window
        }
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

require( ['jquery', 'underscore', 'backbone', 'app/router', 'lib/fastclick/fastclick.min', 'app/views/chrome'],
function( $, _, Backbone, Router, fastClick ){
    Backbone.history.start({ pushState : true, root : '/' })
    fastClick.attach(document.body)
} )
