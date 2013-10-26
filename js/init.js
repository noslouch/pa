/* init.js */
/*global document*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.1382748479083180521',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.1382748479083180521',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox.pack',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1382748479083180521',
        'app/collections/covergallery' : 'app/collections/covergallery.1382748479083180521',
        'app/collections/films' : 'app/collections/films.1382748479083180521',
        'app/collections/instagrams' : 'app/collections/instagrams.1382748479083180521',
        'app/collections/photography' : 'app/collections/photography.1382748479083180521',
        'app/collections/profile' : 'app/collections/profile.1382748479083180521',
        'app/collections/projects' : 'app/collections/projects.1382748479083180521',
        'app/collections/showcases' : 'app/collections/showcases.1382748479083180521',
        'app/models/album' : 'app/models/album.1382748479083180521',
        'app/models/cover' : 'app/models/cover.1382748479083180521',
        'app/models/film' : 'app/models/film.1382748479083180521',
        'app/models/profile' : 'app/models/profile.1382748479083180521',
        'app/models/project' : 'app/models/project.1382748479083180521',
        'app/models/searchQuery' : 'app/models/searchQuery.1382748479083180521',
        'app/models/showcase' : 'app/models/showcase.1382748479083180521',
        'app/views/chrome' : 'app/views/chrome.1382748479083180521',
        'app/views/filterviews' : 'app/views/filterviews.1382748479083180521',
        'app/views/header' : 'app/views/header.1382748479083180521',
        'app/views/home' : 'app/views/home.1382748479083180521',
        'app/views/jumplist' : 'app/views/jumplist.1382748479083180521',
        'app/views/page' : 'app/views/page.1382748479083180521',
        'app/views/photography' : 'app/views/photography.1382748479083180521',
        'app/views/profile' : 'app/views/profile.1382748479083180521',
        'app/views/film' : 'app/views/film.1382748479083180521',
        'app/views/projects' : 'app/views/projects.1382748479083180521',
        'app/views/search' : 'app/views/search.1382748479083180521',
        'app/views/showcaseviews' : 'app/views/showcaseviews.1382748479083180521',
        'app/views/singlealbum' : 'app/views/singlealbum.1382748479083180521',
        'app/views/singlefilm' : 'app/views/singlefilm.1382748479083180521',
        'app/views/singleproject' : 'app/views/singleproject.1382748479083180521',
        'app/views/singleviews' : 'app/views/singleviews.1382748479083180521',
        'utils/spinner' : 'utils/spinner.1382748479083180521',
        'utils/quotes' : 'utils/quotes.1382748479083180521',
        'utils/fbLoader' : 'utils/fbLoader.1382748479083180521',
        'tpl/jst' : 'tpl/jst.1382748479083180521'
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
function( $, _, Backbone, Router, attachFastClick ){
    Backbone.history.start({ pushState : true, root : '/' })
    attachFastClick(document.body)
} )
