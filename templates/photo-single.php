<?php 
    $_GET['template'] = 'photography';
    include("includes/header.php"); 
?>
    <div class="viewer">
        <div class="details">
            <h3>
                Photo Gallery
                <span>2012</span>
            </h3>
            <p class="summary">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus rhoncus mauris vel molestie cursus. Suspendisse vehicula tortor metus, non iaculis.</p> <!-- .summary -->
        </div> <!-- .details -->
        <div class="container" id="showcaseContainer">

            <?php
                $_GET['template'] = 'single-photo';
                include("includes/image-showcase.php"); ?>

        </div> <!-- .container -->
    </div> <!-- .viewer -->
<?php include("includes/footer.php"); ?>
