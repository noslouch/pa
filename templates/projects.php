<?php include("includes/header.php"); ?>
        <h2 class="visuallyhidden">Projects Home</h2>

        <?php 
            $_GET['template'] = 'projects';
            include("includes/filter-bar.php"); ?>

        <div class="covers-container" id="showcaseContainer">
        
        <!--
            This container will start with the starfield of project covers (randomly chosen).
            Based on user choice, these contents can be replaced with an image showcase or list showcase (double wide).
        -->
            
        <!-- 
            STARFIELD TO BE ADDED LATER
            <div id="starfield" class="starfield"></div>
        -->
            <?php include("includes/image-showcase.php"); ?>
        </div> 

<?php include("includes/footer.php"); ?>
