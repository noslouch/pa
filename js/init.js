/* init.js */
/*global document,window*/
'use strict';

require.config({
    paths : {
        jquery : 'lib/jquery/jquery',
        underscore : 'lib/underscore/underscore-amd.min',
        // using extend backbone
        backbone : 'utils/backbone.138647341588172310',
        moment : 'lib/moment/moment.min',
        isotope : 'utils/iso.138647341588172310',
        bbq : 'lib/bbq/jquery.ba-bbq.no-legacy',
        foundation : 'lib/foundation/foundation',
        tooltips : 'lib/foundation/foundation.tooltips',
        fancybox : 'lib/fancybox/jquery.fancybox',
        swipe : 'lib/swipe/swipe',
        json : 'lib/json/json2',
        imagesLoaded : '../bower_components/imagesloaded/imagesloaded',
        'eventie': '../bower_components/eventie',
        'eventEmitter': '../bower_components/eventEmitter',
        'app/router' : 'app/router.138647341588172310',
        'app/collections/covergallery' : 'app/collections/covergallery.138647341588172310',
        'app/collections/films' : 'app/collections/films.138647341588172310',
        'app/collections/instagrams' : 'app/collections/instagrams.138647341588172310',
        'app/collections/photography' : 'app/collections/photography.138647341588172310',
        'app/collections/profile' : 'app/collections/profile.138647341588172310',
        'app/collections/projects' : 'app/collections/projects.138647341588172310',
        'app/collections/showcases' : 'app/collections/showcases.138647341588172310',
        'app/models/cover' : 'app/models/cover.138647341588172310',
        'app/models/film' : 'app/models/film.138647341588172310',
        'app/models/photo' : 'app/models/photo.138647341588172310',
        'app/models/profile' : 'app/models/profile.138647341588172310',
        'app/models/project' : 'app/models/project.138647341588172310',
        'app/models/searchQuery' : 'app/models/searchQuery.138647341588172310',
        'app/models/showcase' : 'app/models/showcase.138647341588172310',
        'app/views/chrome' : 'app/views/chrome.138647341588172310',
        //'app/views/details/album' : 'app/views/details/album.138647341588172310',
        'app/views/details/books' : 'app/views/details/books.138647341588172310',
        'app/views/details/films' : 'app/views/details/films.138647341588172310',
        'app/views/details/photography' : 'app/views/details/photography.138647341588172310',
        'app/views/details/projects' : 'app/views/details/projects.138647341588172310',
        'app/views/partials/album' : 'app/views/partials/album.138647341588172310',
        'app/views/partials/filterviews' : 'app/views/partials/filterviews.138647341588172310',
        'app/views/partials/grid' : 'app/views/partials/grid.138647341588172310',
        'app/views/partials/jumplist' : 'app/views/partials/jumplist.138647341588172310',
        'app/views/sections/books' : 'app/views/sections/books.138647341588172310',
        'app/views/sections/contact' : 'app/views/sections/contact.138647341588172310',
        'app/views/sections/film' : 'app/views/sections/film.138647341588172310',
        'app/views/sections/home' : 'app/views/sections/home.138647341588172310',
        'app/views/sections/photography' : 'app/views/sections/photography.138647341588172310',
        'app/views/sections/profile' : 'app/views/sections/profile.138647341588172310',
        'app/views/sections/projects' : 'app/views/sections/projects.138647341588172310',
        'app/views/sections/search' : 'app/views/sections/search.138647341588172310',
        'app/views/sections/stream' : 'app/views/sections/stream.138647341588172310',
        'app/views/showcases/gallery' : 'app/views/showcases/gallery.138647341588172310',
        'app/views/showcases/list' : 'app/views/showcases/list.138647341588172310',
        'app/views/showcases/starfield' : 'app/views/showcases/starfield.138647341588172310',
        'app/views/showcases/text' : 'app/views/showcases/text.138647341588172310',
        'app/views/showcases/video' : 'app/views/showcases/video.138647341588172310',
        'utils/spinner' : 'utils/spinner.138647341588172310',
        'utils/quotes' : 'utils/quotes.138647341588172310',
        'utils/fbLoader' : 'utils/fbLoader.138647341588172310',
        'tpl/jst' : 'tpl/jst.138647341588172310'
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
