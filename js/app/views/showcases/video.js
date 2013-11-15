/* app/views/showcases/videos.js
 * Video view
 * Doesn't return an object; returns directly accessible Video View */
'use strict';

define([
    'jquery',
    'backbone',
    'underscore',
    'tpl/jst'
], function( $, Backbone, _, TPL ) {

    // Video
    // Video showcase used on Single projects
    return Backbone.View.extend({
        tagname : 'div',
        className : 'showcase video',
        videoCaption : TPL.videoCaption,

        initialize : function() {
            this.videoTmpl = this.model.get('video_id') ? TPL.videoID : TPL.iframeVideo
            this.videoSrc = this.model.get('video_id') ? this.model.get('video_id') : this.model.get('video_src')
        },

        render : function() {
            var film = this.videoTmpl({
                videoSrc : this.videoSrc,
                youtube : this.model.get('youtube')
            })

            var caption = this.videoCaption({
                title : this.model.get('title'),
                content : this.model.get('caption'),
            })

            this.$el
                .append(film)
                .append(caption)

            return this.el
        }
    })

})
