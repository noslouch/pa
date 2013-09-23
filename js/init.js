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
        'app/router' : 'app/router.1379961343441317212',
        'app/collections/covergallery' : 'app/collections/covergallery.1379961343441317212',
        'app/collections/films' : 'app/collections/films.1379961343441317212',
        'app/collections/instagrams' : 'app/collections/instagrams.1379961343441317212',
        'app/collections/photography' : 'app/collections/photography.1379961343441317212',
        'app/collections/profile' : 'app/collections/profile.1379961343441317212',
        'app/collections/projects' : 'app/collections/projects.1379961343441317212',
        'app/collections/showcases' : 'app/collections/showcases.1379961343441317212',
        'app/models/album' : 'app/models/album.1379961343441317212',
        'app/models/cover' : 'app/models/cover.1379961343441317212',
        'app/models/film' : 'app/models/film.1379961343441317212',
        'app/models/profilesection' : 'app/models/profilesection.1379961343441317212',
        'app/models/project' : 'app/models/project.1379961343441317212',
        'app/models/searchQuery' : 'app/models/searchQuery.1379961343441317212',
        'app/models/showcase' : 'app/models/showcase.1379961343441317212',
        'app/views/chrome' : 'app/views/chrome.1379961343441317212',
        'app/views/filterviews' : 'app/views/filterviews.1379961343441317212',
        'app/views/header' : 'app/views/header.1379961343441317212',
        'app/views/home' : 'app/views/home.1379961343441317212',
        'app/views/page' : 'app/views/page.1379961343441317212',
        'app/views/profileviews' : 'app/views/profileviews.1379961343441317212',
        'app/views/projects' : 'app/views/projects.1379961343441317212',
        'app/views/search' : 'app/views/search.1379961343441317212',
        'app/views/showcaseviews' : 'app/views/showcaseviews.1379961343441317212',
        'app/views/singleviews' : 'app/views/singleviews.1379961343441317212',
        'utils/spinner' : 'utils/spinner.1379961343441317212',
        'tpl/jst' : 'tpl/jst.1379961343441317212'
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
