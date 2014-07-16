/* init.js */
/*global document*/
'use strict';

require.config({
    baseUrl : '/bower_components/',
    paths : {
        jquery                          : '/js/lib/jquery/jquery',
        underscore                      : '/js/lib/underscore/underscore-amd.min',
        // using ext                    end/js/ backbone
        backbone                        : '/js/utils/backbone.1405532303611868315',
        moment                          : '/js/lib/moment/moment.min',
        isotope                         : '/js/utils/iso.1405532303611868315',
        //isotope :                     '../js//bower_components/isotope/dist/isotope.pkgd.min',
        bbq                             : '/js/lib/bbq/jquery.ba-bbq.no-legacy',
        foundation                      : 'foundation/js/foundation.min',
        tooltips                        : 'foundation/js/foundation/foundation.tooltip',
        fancybox                        : '/js/lib/fancybox/jquery.fancybox',
        swipe                           : '/js/lib/swipe/swipe',
        json                            : '/js/lib/json/json2',
        imagesLoaded                    : 'imagesloaded/imagesloaded',
        mixitup                         : 'mixitup/jquery.mixitup.min',
        mixfilter                       : '/js/app/views/partials/mixfilter.1405532303611868315',
        fastclick                       : '/js/lib/fastclick/fastclick.min',
        domReady                        : '/js/lib/requirejs/domReady',
        //'eventie': '../bower_components/eventie',
        //'eventEmitter': '../bower_components/eventEmitter',
        'app/router'                    : '/js/app/router.1405532303611868315',
        'app/collections/covergallery'  : '/js/app/collections/covergallery.1405532303611868315',
        'app/collections/films'         : '/js/app/collections/films.1405532303611868315',
        'app/collections/instagrams'    : '/js/app/collections/instagrams.1405532303611868315',
        'app/collections/photography'   : '/js/app/collections/photography.1405532303611868315',
        'app/collections/books'         : '/js/app/collections/books.1405532303611868315',
        'app/collections/profile'       : '/js/app/collections/profile.1405532303611868315',
        'app/collections/projects'      : '/js/app/collections/projects.1405532303611868315',
        'app/collections/showcases'     : '/js/app/collections/showcases.1405532303611868315',
        'app/models/cover'              : '/js/app/models/cover.1405532303611868315',
        'app/models/film'               : '/js/app/models/film.1405532303611868315',
        'app/models/photo'              : '/js/app/models/photo.1405532303611868315',
        'app/models/book'               : '/js/app/models/book.1405532303611868315',
        'app/models/profile'            : '/js/app/models/profile.1405532303611868315',
        'app/models/project'            : '/js/app/models/project.1405532303611868315',
        'app/models/searchQuery'        : '/js/app/models/searchQuery.1405532303611868315',
        'app/models/showcase'           : '/js/app/models/showcase.1405532303611868315',
        'app/views/chrome'              : '/js/app/views/chrome.1405532303611868315',
        //'app/views/details/album' : 'app//js/views/details/album.1405532303611868315',
        'app/views/details/books'       : '/js/app/views/details/books.1405532303611868315',
        'app/views/details/film'       : '/js/app/views/details/film.1405532303611868315',
        'app/views/details/photography' : '/js/app/views/details/photography.1405532303611868315',
        'app/views/details/projects'    : '/js/app/views/details/projects.1405532303611868315',
        'app/views/partials/album'      : '/js/app/views/partials/album.1405532303611868315',
        'app/views/partials/filterviews': '/js/app/views/partials/filterviews.1405532303611868315',
        'app/views/partials/grid'       : '/js/app/views/partials/grid.1405532303611868315',
        'app/views/partials/jumplist'   : '/js/app/views/partials/jumplist.1405532303611868315',
        'app/views/sections/books'      : '/js/app/views/sections/books.1405532303611868315',
        'app/views/sections/contact'    : '/js/app/views/sections/contact.1405532303611868315',
        'app/views/sections/film'       : '/js/app/views/sections/film.1405532303611868315',
        'app/views/sections/home'       : '/js/app/views/sections/home.1405532303611868315',
        'app/views/sections/photography': '/js/app/views/sections/photography.1405532303611868315',
        'app/views/sections/profile'    : '/js/app/views/sections/profile.1405532303611868315',
        'app/views/sections/projects'   : '/js/app/views/sections/projects.1405532303611868315',
        'app/views/sections/search'     : '/js/app/views/sections/search.1405532303611868315',
        'app/views/sections/stream'     : '/js/app/views/sections/stream.1405532303611868315',
        'app/views/showcases/gallery'   : '/js/app/views/showcases/gallery.1405532303611868315',
        'app/views/showcases/list'      : '/js/app/views/showcases/list.1405532303611868315',
        'app/views/showcases/starfield' : '/js/app/views/showcases/starfield.1405532303611868315',
        'app/views/showcases/text'      : '/js/app/views/showcases/text.1405532303611868315',
        'app/views/showcases/video'     : '/js/app/views/showcases/video.1405532303611868315',
        'utils/spinner'                 : '/js/utils/spinner.1405532303611868315',
        'utils/quotes'                  : '/js/utils/quotes.1405532303611868315',
        'utils/fbLoader'                : '/js/utils/fbLoader.1405532303611868315',
        'utils/touchLoader'             : '/js/utils/touchLoader.1405532303611868315',
        'tpl/jst'                       : '/js/tpl/jst.1405532303611868315'
    },
    // map : {
    //     '*' : {
    //         'is' : '/js/lib/require-is/is'
    //     }
    // },
    // config : {
    //     '/js/lib/require-is/is' : {
    //         mobile : 'ontouchstart' in window
    //     }
    // },
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
