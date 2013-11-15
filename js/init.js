/* init.js */
/*global document*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.13844824530131301',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.13844824530131301',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.13844824530131301',
        'app/collections/covergallery' : 'app/collections/covergallery.13844824530131301',
        'app/collections/films' : 'app/collections/films.13844824530131301',
        'app/collections/instagrams' : 'app/collections/instagrams.13844824530131301',
        'app/collections/photography' : 'app/collections/photography.13844824530131301',
        'app/collections/profile' : 'app/collections/profile.13844824530131301',
        'app/collections/projects' : 'app/collections/projects.13844824530131301',
        'app/collections/showcases' : 'app/collections/showcases.13844824530131301',
        'app/models/album' : 'app/models/album.13844824530131301',
        'app/models/cover' : 'app/models/cover.13844824530131301',
        'app/models/film' : 'app/models/film.13844824530131301',
        'app/models/profile' : 'app/models/profile.13844824530131301',
        'app/models/project' : 'app/models/project.13844824530131301',
        'app/models/searchQuery' : 'app/models/searchQuery.13844824530131301',
        'app/models/showcase' : 'app/models/showcase.13844824530131301',
        'app/views/chrome' : 'app/views/chrome.13844824530131301',
        'app/views/filterviews' : 'app/views/filterviews.13844824530131301',
        'app/views/header' : 'app/views/header.13844824530131301',
        'app/views/home' : 'app/views/home.13844824530131301',
        'app/views/jumplist' : 'app/views/jumplist.13844824530131301',
        'app/views/page' : 'app/views/page.13844824530131301',
        'app/views/photography' : 'app/views/photography.13844824530131301',
        'app/views/profile' : 'app/views/profile.13844824530131301',
        'app/views/film' : 'app/views/film.13844824530131301',
        'app/views/projects' : 'app/views/projects.13844824530131301',
        'app/views/search' : 'app/views/search.13844824530131301',
        'app/views/showcaseviews' : 'app/views/showcaseviews.13844824530131301',
        'app/views/singlealbum' : 'app/views/singlealbum.13844824530131301',
        'app/views/singlefilm' : 'app/views/singlefilm.13844824530131301',
        'app/views/singleproject' : 'app/views/singleproject.13844824530131301',
        'app/views/singleviews' : 'app/views/singleviews.13844824530131301',
        'utils/spinner' : 'utils/spinner.13844824530131301',
        'utils/quotes' : 'utils/quotes.13844824530131301',
        'utils/fbLoader' : 'utils/fbLoader.13844824530131301',
        'tpl/jst' : 'tpl/jst.13844824530131301'
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
