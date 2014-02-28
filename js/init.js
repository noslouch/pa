/* init.js */
/*global document,window*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.1393625731640110589',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.1393625731640110589',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : '../bower_components/foundation/js/foundation.min',
        tooltips : '../bower_components/foundation/js/foundation/foundation.tooltip',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        mixitup : '../bower_components/mixitup/jquery.mixitup.min',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.1393625731640110589',
        'app/collections/covergallery' : 'app/collections/covergallery.1393625731640110589',
        'app/collections/films' : 'app/collections/films.1393625731640110589',
        'app/collections/instagrams' : 'app/collections/instagrams.1393625731640110589',
        'app/collections/photography' : 'app/collections/photography.1393625731640110589',
        'app/collections/profile' : 'app/collections/profile.1393625731640110589',
        'app/collections/projects' : 'app/collections/projects.1393625731640110589',
        'app/collections/showcases' : 'app/collections/showcases.1393625731640110589',
        'app/models/cover' : 'app/models/cover.1393625731640110589',
        'app/models/film' : 'app/models/film.1393625731640110589',
        'app/models/photo' : 'app/models/photo.1393625731640110589',
        'app/models/profile' : 'app/models/profile.1393625731640110589',
        'app/models/project' : 'app/models/project.1393625731640110589',
        'app/models/searchQuery' : 'app/models/searchQuery.1393625731640110589',
        'app/models/showcase' : 'app/models/showcase.1393625731640110589',
        'app/views/chrome' : 'app/views/chrome.1393625731640110589',
        //'app/views/details/album' : 'app/views/details/album.1393625731640110589',
        'app/views/details/books' : 'app/views/details/books.1393625731640110589',
        'app/views/details/films' : 'app/views/details/films.1393625731640110589',
        'app/views/details/photography' : 'app/views/details/photography.1393625731640110589',
        'app/views/details/projects' : 'app/views/details/projects.1393625731640110589',
        'app/views/partials/album' : 'app/views/partials/album.1393625731640110589',
        'app/views/partials/filterviews' : 'app/views/partials/filterviews.1393625731640110589',
        'app/views/partials/grid' : 'app/views/partials/grid.1393625731640110589',
        'app/views/partials/jumplist' : 'app/views/partials/jumplist.1393625731640110589',
        'app/views/sections/books' : 'app/views/sections/books.1393625731640110589',
        'app/views/sections/contact' : 'app/views/sections/contact.1393625731640110589',
        'app/views/sections/film' : 'app/views/sections/film.1393625731640110589',
        'app/views/sections/home' : 'app/views/sections/home.1393625731640110589',
        'app/views/sections/photography' : 'app/views/sections/photography.1393625731640110589',
        'app/views/sections/profile' : 'app/views/sections/profile.1393625731640110589',
        'app/views/sections/projects' : 'app/views/sections/projects.1393625731640110589',
        'app/views/sections/search' : 'app/views/sections/search.1393625731640110589',
        'app/views/sections/stream' : 'app/views/sections/stream.1393625731640110589',
        'app/views/showcases/gallery' : 'app/views/showcases/gallery.1393625731640110589',
        'app/views/showcases/list' : 'app/views/showcases/list.1393625731640110589',
        'app/views/showcases/starfield' : 'app/views/showcases/starfield.1393625731640110589',
        'app/views/showcases/text' : 'app/views/showcases/text.1393625731640110589',
        'app/views/showcases/video' : 'app/views/showcases/video.1393625731640110589',
        'utils/spinner' : 'utils/spinner.1393625731640110589',
        'utils/quotes' : 'utils/quotes.1393625731640110589',
        'utils/fbLoader' : 'utils/fbLoader.1393625731640110589',
        'tpl/jst' : 'tpl/jst.1393625731640110589'
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
