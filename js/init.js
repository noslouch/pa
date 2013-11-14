/* init.js */
/*global document*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.1384464846069353519',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.1384464846069353519',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1384464846069353519',
        'app/collections/covergallery' : 'app/collections/covergallery.1384464846069353519',
        'app/collections/films' : 'app/collections/films.1384464846069353519',
        'app/collections/instagrams' : 'app/collections/instagrams.1384464846069353519',
        'app/collections/photography' : 'app/collections/photography.1384464846069353519',
        'app/collections/profile' : 'app/collections/profile.1384464846069353519',
        'app/collections/projects' : 'app/collections/projects.1384464846069353519',
        'app/collections/showcases' : 'app/collections/showcases.1384464846069353519',
        'app/models/album' : 'app/models/album.1384464846069353519',
        'app/models/cover' : 'app/models/cover.1384464846069353519',
        'app/models/film' : 'app/models/film.1384464846069353519',
        'app/models/profile' : 'app/models/profile.1384464846069353519',
        'app/models/project' : 'app/models/project.1384464846069353519',
        'app/models/searchQuery' : 'app/models/searchQuery.1384464846069353519',
        'app/models/showcase' : 'app/models/showcase.1384464846069353519',
        'app/views/chrome' : 'app/views/chrome.1384464846069353519',
        'app/views/filterviews' : 'app/views/filterviews.1384464846069353519',
        'app/views/header' : 'app/views/header.1384464846069353519',
        'app/views/home' : 'app/views/home.1384464846069353519',
        'app/views/jumplist' : 'app/views/jumplist.1384464846069353519',
        'app/views/page' : 'app/views/page.1384464846069353519',
        'app/views/photography' : 'app/views/photography.1384464846069353519',
        'app/views/profile' : 'app/views/profile.1384464846069353519',
        'app/views/film' : 'app/views/film.1384464846069353519',
        'app/views/projects' : 'app/views/projects.1384464846069353519',
        'app/views/search' : 'app/views/search.1384464846069353519',
        'app/views/showcaseviews' : 'app/views/showcaseviews.1384464846069353519',
        'app/views/singlealbum' : 'app/views/singlealbum.1384464846069353519',
        'app/views/singlefilm' : 'app/views/singlefilm.1384464846069353519',
        'app/views/singleproject' : 'app/views/singleproject.1384464846069353519',
        'app/views/singleviews' : 'app/views/singleviews.1384464846069353519',
        'utils/spinner' : 'utils/spinner.1384464846069353519',
        'utils/quotes' : 'utils/quotes.1384464846069353519',
        'utils/fbLoader' : 'utils/fbLoader.1384464846069353519',
        'tpl/jst' : 'tpl/jst.1384464846069353519'
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
