/* app/views/showcases/text.js
 * Generic text view
 * Returns directly accessible Text View */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst',
], function( $, Backbone, _, TPL ) {

    // Text
    // Generic Text view with modular html components
    return Backbone.View.extend({
        tagName : 'div',
        className : 'showcase text',
        base : TPL.textTemplate,
        header : TPL.textTemplateHeader,
        bioImg : TPL.bioImage,
        gallery : TPL.textGallery,
        back : TPL.backButton,
        render : function() {
            return this.$el
        }
    })
})
