/* init.js */
/*global document,window*/
'use strict';

require.config({
    baseUrl : '/bower_components/',
    paths : {
        jquery                          : '/js/lib/jquery/jquery',
        underscore                      : '/js/lib/underscore/underscore-amd.min',
        // using ext                    end/js/ backbone
        backbone                        : '/js/utils/backbone.@@hash',
        moment                          : '/js/lib/moment/moment.min',
        isotope                         : '/js/utils/iso.@@hash',
        //isotope :                     '../js//bower_components/isotope/dist/isotope.pkgd.min',
        bbq                             : '/js/lib/bbq/jquery.ba-bbq.no-legacy',
        foundation                      : 'foundation/js/foundation.min',
        tooltips                        : 'foundation/js/foundation/foundation.tooltip',
        fancybox                        : '/js/lib/fancybox/jquery.fancybox',
        swipe                           : '/js/lib/swipe/swipe',
        json                            : '/js/lib/json/json2',
        imagesLoaded                    : 'imagesloaded/imagesloaded',
        mixitup                         : 'mixitup/jquery.mixitup.min',
        mixfilter                       : '/js/app/views/partials/mixfilter.@@hash',
        fastclick                       : '/js/lib/fastclick/fastclick.min',
        domReady                        : '/js/lib/requirejs/domReady',
        //'eventie': '../bower_components/eventie',
        //'eventEmitter': '../bower_components/eventEmitter',
        'app/router'                    : '/js/app/router.@@hash',
        'app/collections/covergallery'  : '/js/app/collections/covergallery.@@hash',
        'app/collections/films'         : '/js/app/collections/films.@@hash',
        'app/collections/instagrams'    : '/js/app/collections/instagrams.@@hash',
        'app/collections/photography'   : '/js/app/collections/photography.@@hash',
        'app/collections/books'         : '/js/app/collections/books.@@hash',
        'app/collections/profile'       : '/js/app/collections/profile.@@hash',
        'app/collections/projects'      : '/js/app/collections/projects.@@hash',
        'app/collections/showcases'     : '/js/app/collections/showcases.@@hash',
        'app/models/cover'              : '/js/app/models/cover.@@hash',
        'app/models/film'               : '/js/app/models/film.@@hash',
        'app/models/photo'              : '/js/app/models/photo.@@hash',
        'app/models/book'               : '/js/app/models/book.@@hash',
        'app/models/profile'            : '/js/app/models/profile.@@hash',
        'app/models/project'            : '/js/app/models/project.@@hash',
        'app/models/searchQuery'        : '/js/app/models/searchQuery.@@hash',
        'app/models/showcase'           : '/js/app/models/showcase.@@hash',
        'app/views/chrome'              : '/js/app/views/chrome.@@hash',
        //'app/views/details/album' : 'app//js/views/details/album.@@hash',
        'app/views/details/books'       : '/js/app/views/details/books.@@hash',
        'app/views/details/films'       : '/js/app/views/details/films.@@hash',
        'app/views/details/photography' : '/js/app/views/details/photography.@@hash',
        'app/views/details/projects'    : '/js/app/views/details/projects.@@hash',
        'app/views/partials/album'      : '/js/app/views/partials/album.@@hash',
        'app/views/partials/filterviews': '/js/app/views/partials/filterviews.@@hash',
        'app/views/partials/grid'       : '/js/app/views/partials/grid.@@hash',
        'app/views/partials/jumplist'   : '/js/app/views/partials/jumplist.@@hash',
        'app/views/sections/books'      : '/js/app/views/sections/books.@@hash',
        'app/views/sections/contact'    : '/js/app/views/sections/contact.@@hash',
        'app/views/sections/film'       : '/js/app/views/sections/film.@@hash',
        'app/views/sections/home'       : '/js/app/views/sections/home.@@hash',
        'app/views/sections/photography': '/js/app/views/sections/photography.@@hash',
        'app/views/sections/profile'    : '/js/app/views/sections/profile.@@hash',
        'app/views/sections/projects'   : '/js/app/views/sections/projects.@@hash',
        'app/views/sections/search'     : '/js/app/views/sections/search.@@hash',
        'app/views/sections/stream'     : '/js/app/views/sections/stream.@@hash',
        'app/views/showcases/gallery'   : '/js/app/views/showcases/gallery.@@hash',
        'app/views/showcases/list'      : '/js/app/views/showcases/list.@@hash',
        'app/views/showcases/starfield' : '/js/app/views/showcases/starfield.@@hash',
        'app/views/showcases/text'      : '/js/app/views/showcases/text.@@hash',
        'app/views/showcases/video'     : '/js/app/views/showcases/video.@@hash',
        'utils/spinner'                 : '/js/utils/spinner.@@hash',
        'utils/quotes'                  : '/js/utils/quotes.@@hash',
        'utils/fbLoader'                : '/js/utils/fbLoader.@@hash',
        'tpl/jst'                       : '/js/tpl/jst.@@hash'
    },
    map : {
        '*' : {
            'is' : '/js/lib/require-is/is'
        }
    },
    config : {
        '/js/lib/require-is/is' : {
            mobile : 'ontouchstart' in window
        }
    },
    shim : {
        'jquery': {
            exports: '$'
        },
        //'isotope' : ['jquery'],
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

require( ['jquery', 'underscore', 'backbone', 'app/router', 'fastclick', 'app/views/chrome'],
function( $, _, Backbone, Router, fastClick ){
    Backbone.history.start({ pushState : true, root : '/' })
    fastClick.attach(document.body)
} )
