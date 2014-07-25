'use strict';

define([
    'require',
    'jquery',
    '/bower_components/isotope/dist/isotope.pkgd.js'
], function( require, $, Isotope ) {

    require( [ 'jquery-bridget/jquery.bridget' ],
        function() {
          // make Isotope a jQuery plugin
          $.bridget( 'isotope', Isotope );
        })

    return $

})
