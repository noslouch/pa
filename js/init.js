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
        'app/router' : 'app/router.138003689474451524',
        'app/collections/covergallery' : 'app/collections/covergallery.138003689474451524',
        'app/collections/films' : 'app/collections/films.138003689474451524',
        'app/collections/instagrams' : 'app/collections/instagrams.138003689474451524',
        'app/collections/photography' : 'app/collections/photography.138003689474451524',
        'app/collections/profile' : 'app/collections/profile.138003689474451524',
        'app/collections/projects' : 'app/collections/projects.138003689474451524',
        'app/collections/showcases' : 'app/collections/showcases.138003689474451524',
        'app/models/album' : 'app/models/album.138003689474451524',
        'app/models/cover' : 'app/models/cover.138003689474451524',
        'app/models/film' : 'app/models/film.138003689474451524',
        'app/models/profilesection' : 'app/models/profilesection.138003689474451524',
        'app/models/project' : 'app/models/project.138003689474451524',
        'app/models/searchQuery' : 'app/models/searchQuery.138003689474451524',
        'app/models/showcase' : 'app/models/showcase.138003689474451524',
        'app/views/chrome' : 'app/views/chrome.138003689474451524',
        'app/views/filterviews' : 'app/views/filterviews.138003689474451524',
        'app/views/header' : 'app/views/header.138003689474451524',
        'app/views/home' : 'app/views/home.138003689474451524',
        'app/views/page' : 'app/views/page.138003689474451524',
        'app/views/profileviews' : 'app/views/profileviews.138003689474451524',
        'app/views/projects' : 'app/views/projects.138003689474451524',
        'app/views/search' : 'app/views/search.138003689474451524',
        'app/views/showcaseviews' : 'app/views/showcaseviews.138003689474451524',
        'app/views/singleviews' : 'app/views/singleviews.138003689474451524',
        'utils/spinner' : 'utils/spinner.138003689474451524',
        'tpl/jst' : 'tpl/jst.138003689474451524'
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
