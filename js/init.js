/* init.js */
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.1382723367024655473',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.1382723367024655473',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox.pack',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1382723367024655473',
        'app/collections/covergallery' : 'app/collections/covergallery.1382723367024655473',
        'app/collections/films' : 'app/collections/films.1382723367024655473',
        'app/collections/instagrams' : 'app/collections/instagrams.1382723367024655473',
        'app/collections/photography' : 'app/collections/photography.1382723367024655473',
        'app/collections/profile' : 'app/collections/profile.1382723367024655473',
        'app/collections/projects' : 'app/collections/projects.1382723367024655473',
        'app/collections/showcases' : 'app/collections/showcases.1382723367024655473',
        'app/models/album' : 'app/models/album.1382723367024655473',
        'app/models/cover' : 'app/models/cover.1382723367024655473',
        'app/models/film' : 'app/models/film.1382723367024655473',
        'app/models/profile' : 'app/models/profile.1382723367024655473',
        'app/models/project' : 'app/models/project.1382723367024655473',
        'app/models/searchQuery' : 'app/models/searchQuery.1382723367024655473',
        'app/models/showcase' : 'app/models/showcase.1382723367024655473',
        'app/views/chrome' : 'app/views/chrome.1382723367024655473',
        'app/views/filterviews' : 'app/views/filterviews.1382723367024655473',
        'app/views/header' : 'app/views/header.1382723367024655473',
        'app/views/home' : 'app/views/home.1382723367024655473',
        'app/views/jumplist' : 'app/views/jumplist.1382723367024655473',
        'app/views/page' : 'app/views/page.1382723367024655473',
        'app/views/photography' : 'app/views/photography.1382723367024655473',
        'app/views/profile' : 'app/views/profile.1382723367024655473',
        'app/views/film' : 'app/views/film.1382723367024655473',
        'app/views/projects' : 'app/views/projects.1382723367024655473',
        'app/views/search' : 'app/views/search.1382723367024655473',
        'app/views/showcaseviews' : 'app/views/showcaseviews.1382723367024655473',
        'app/views/singlealbum' : 'app/views/singlealbum.1382723367024655473',
        'app/views/singlefilm' : 'app/views/singlefilm.1382723367024655473',
        'app/views/singleproject' : 'app/views/singleproject.1382723367024655473',
        'app/views/singleviews' : 'app/views/singleviews.1382723367024655473',
        'utils/spinner' : 'utils/spinner.1382723367024655473',
        'utils/quotes' : 'utils/quotes.1382723367024655473',
        'utils/fbLoader' : 'utils/fbLoader.1382723367024655473',
        'tpl/jst' : 'tpl/jst.1382723367024655473'
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
