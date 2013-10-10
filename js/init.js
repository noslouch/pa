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
        'app/router' : 'app/router.138137638200259752',
        'app/collections/covergallery' : 'app/collections/covergallery.138137638200259752',
        'app/collections/films' : 'app/collections/films.138137638200259752',
        'app/collections/instagrams' : 'app/collections/instagrams.138137638200259752',
        'app/collections/photography' : 'app/collections/photography.138137638200259752',
        'app/collections/profile' : 'app/collections/profile.138137638200259752',
        'app/collections/projects' : 'app/collections/projects.138137638200259752',
        'app/collections/showcases' : 'app/collections/showcases.138137638200259752',
        'app/models/album' : 'app/models/album.138137638200259752',
        'app/models/cover' : 'app/models/cover.138137638200259752',
        'app/models/film' : 'app/models/film.138137638200259752',
        'app/models/profilesection' : 'app/models/profilesection.138137638200259752',
        'app/models/project' : 'app/models/project.138137638200259752',
        'app/models/searchQuery' : 'app/models/searchQuery.138137638200259752',
        'app/models/showcase' : 'app/models/showcase.138137638200259752',
        'app/views/chrome' : 'app/views/chrome.138137638200259752',
        'app/views/filterviews' : 'app/views/filterviews.138137638200259752',
        'app/views/header' : 'app/views/header.138137638200259752',
        'app/views/home' : 'app/views/home.138137638200259752',
        'app/views/page' : 'app/views/page.138137638200259752',
        'app/views/profileviews' : 'app/views/profileviews.138137638200259752',
        'app/views/projects' : 'app/views/projects.138137638200259752',
        'app/views/search' : 'app/views/search.138137638200259752',
        'app/views/showcaseviews' : 'app/views/showcaseviews.138137638200259752',
        'app/views/singleviews' : 'app/views/singleviews.138137638200259752',
        'utils/spinner' : 'utils/spinner.138137638200259752',
        'tpl/jst' : 'tpl/jst.138137638200259752'
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
