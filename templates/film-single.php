<?php include("includes/header.php"); ?>
    <h2 class="visuallyhidden">Single Film View</h2>

    <div class="viewer">

        <div class="details">
        <?php
            $_GET['template'] = 'mockVideoInfo';
            include("includes/text-showcase.php"); ?>
        </div> <!-- .details -->

        <div class="container" id="showcaseContainer">
            <?php include("includes/video-showcase.php"); ?>
        </div> <!-- .container -->

    </div> <!-- .viewer -->


<?php include("includes/footer.php"); ?>
