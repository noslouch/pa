<?php 
    $_GET['template'] = 'projects';
    include("includes/header.php"); 
?>
        <h2 class="visuallyhidden">Projects Home</h2>

        <?php 
            //$_GET['template'] = 'projects';
            //include("includes/filter-bar.php"); ?>

        <div class="fixed covers-container" id="showcaseContainer">
        <?php //echo '<pre>'; print_r($GLOBALS); ?>
        
        <!--
            This container will start with the starfield of project covers (randomly chosen).
            Based on user choice, these contents can be replaced with an image showcase or list showcase (double wide).
        -->

            <?php //include("includes/image-showcase.php"); ?>
        </div> 

<?php include("includes/footer.php"); ?>
