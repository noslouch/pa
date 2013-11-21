/* init.js */
/*global document*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.1385067959982434144',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.1385067959982434144',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1385067959982434144',
        'app/collections/covergallery' : 'app/collections/covergallery.1385067959982434144',
        'app/collections/films' : 'app/collections/films.1385067959982434144',
        'app/collections/instagrams' : 'app/collections/instagrams.1385067959982434144',
        'app/collections/photography' : 'app/collections/photography.1385067959982434144',
        'app/collections/profile' : 'app/collections/profile.1385067959982434144',
        'app/collections/projects' : 'app/collections/projects.1385067959982434144',
        'app/collections/showcases' : 'app/collections/showcases.1385067959982434144',
        'app/models/album' : 'app/models/album.1385067959982434144',
        'app/models/cover' : 'app/models/cover.1385067959982434144',
        'app/models/film' : 'app/models/film.1385067959982434144',
        'app/models/profile' : 'app/models/profile.1385067959982434144',
        'app/models/project' : 'app/models/project.1385067959982434144',
        'app/models/searchQuery' : 'app/models/searchQuery.1385067959982434144',
        'app/models/showcase' : 'app/models/showcase.1385067959982434144',
        'app/views/chrome' : 'app/views/chrome.1385067959982434144',
        'app/views/filterviews' : 'app/views/filterviews.1385067959982434144',
        'app/views/header' : 'app/views/header.1385067959982434144',
        'app/views/home' : 'app/views/home.1385067959982434144',
        'app/views/jumplist' : 'app/views/jumplist.1385067959982434144',
        'app/views/page' : 'app/views/page.1385067959982434144',
        'app/views/photography' : 'app/views/photography.1385067959982434144',
        'app/views/profile' : 'app/views/profile.1385067959982434144',
        'app/views/film' : 'app/views/film.1385067959982434144',
        'app/views/projects' : 'app/views/projects.1385067959982434144',
        'app/views/search' : 'app/views/search.1385067959982434144',
        'app/views/showcaseviews' : 'app/views/showcaseviews.1385067959982434144',
        'app/views/singlealbum' : 'app/views/singlealbum.1385067959982434144',
        'app/views/singlefilm' : 'app/views/singlefilm.1385067959982434144',
        'app/views/singleproject' : 'app/views/singleproject.1385067959982434144',
        'app/views/singleviews' : 'app/views/singleviews.1385067959982434144',
        'utils/spinner' : 'utils/spinner.1385067959982434144',
        'utils/quotes' : 'utils/quotes.1385067959982434144',
        'utils/fbLoader' : 'utils/fbLoader.1385067959982434144',
        'tpl/jst' : 'tpl/jst.1385067959982434144'
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
