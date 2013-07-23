<?php
    if (isset($_GET['template'])){
        $t = $_GET['template'];
    } else { $t = null; }
?>
    <!--
        VIDEO SHOWCASE
        *a single video player with an embedded video and text details*

        embedded media is the full width of the area.
        additional information goes below.
    -->

    <div class="showcase video">
        <div>
            <iframe src="http://player.vimeo.com/video/70642037" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe> 
        </div>
        <?php if ($t != 'film') { ?>
        <div class="video-caption">
            <h3>Optional Video caption and info</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed volutpat tellus odio, eu pulvinar leo luctus quis. Mauris eget risus euismod, cursus.</p>
        </div>
        <?php } ?>
    </div>
