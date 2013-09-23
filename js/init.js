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
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1379968004283461688',
        'app/collections/covergallery' : 'app/collections/covergallery.1379968004283461688',
        'app/collections/films' : 'app/collections/films.1379968004283461688',
        'app/collections/instagrams' : 'app/collections/instagrams.1379968004283461688',
        'app/collections/photography' : 'app/collections/photography.1379968004283461688',
        'app/collections/profile' : 'app/collections/profile.1379968004283461688',
        'app/collections/projects' : 'app/collections/projects.1379968004283461688',
        'app/collections/showcases' : 'app/collections/showcases.1379968004283461688',
        'app/models/album' : 'app/models/album.1379968004283461688',
        'app/models/cover' : 'app/models/cover.1379968004283461688',
        'app/models/film' : 'app/models/film.1379968004283461688',
        'app/models/profilesection' : 'app/models/profilesection.1379968004283461688',
        'app/models/project' : 'app/models/project.1379968004283461688',
        'app/models/searchQuery' : 'app/models/searchQuery.1379968004283461688',
        'app/models/showcase' : 'app/models/showcase.1379968004283461688',
        'app/views/chrome' : 'app/views/chrome.1379968004283461688',
        'app/views/filterviews' : 'app/views/filterviews.1379968004283461688',
        'app/views/header' : 'app/views/header.1379968004283461688',
        'app/views/home' : 'app/views/home.1379968004283461688',
        'app/views/page' : 'app/views/page.1379968004283461688',
        'app/views/profileviews' : 'app/views/profileviews.1379968004283461688',
        'app/views/projects' : 'app/views/projects.1379968004283461688',
        'app/views/search' : 'app/views/search.1379968004283461688',
        'app/views/showcaseviews' : 'app/views/showcaseviews.1379968004283461688',
        'app/views/singleviews' : 'app/views/singleviews.1379968004283461688',
        'utils/spinner' : 'utils/spinner.1379968004283461688',
        'tpl/jst' : 'tpl/jst.1379968004283461688'
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
