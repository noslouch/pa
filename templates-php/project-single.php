<?php 
    $_GET['template'] = 'project-single';
    include("includes/header.php"); 
?>

    <div class="project viewer">
        <div class="details">
            <header>
                <h3>Donna Karen Bath & Body</h3>
                <time datetime="1986">1986</time>
            </header>
            <p class="summary">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus rhoncus mauris vel molestie cursus. Suspendisse vehicula tortor metus, non iaculis.</p> <!-- .summary -->
            <ul class="showcase-links">
                <li><a href="#" id="mockImageGallery" class="active">Image Gallery</a></li>
                <li><a href="#" id="mockVideo">Video</a></li>
                <li><a href="#" id="mockInfo">Info</a></li>
                <li><a href="#" id="mockRelated">Related</a></li>
            </ul> <!-- .showcase-links -->
            <ul class="tags">
                <li class="row">
                    <span class="type">Brand</span>
                    <div class="links">
                        <a href="#" id="mockTag">DKNY</a>
                    </div> <!-- .links -->
                </li> <!-- .row -->
                <li class="row">
                    <span class="type">Industry</span>
                    <div class="links">
                        <a href="#" id="mockTag">Fashion</a>,
                        <a href="#" id="mockTag">Bath & Body</a>
                    </div> <!-- .links -->
                </li> <!-- .row -->
                <li class="row">
                    <span class="type">Project Type</span>
                    <div class="links">
                        <a href="#" id="mockTag">Communications</a>,
                        <a href="#" id="mockTag">Advertising</a>,
                        <a href="#" id="mockTag">Identity</a>,
                        <a href="#" id="mockTag">Interactive</a>
                    </div> <!-- .links -->
                </li> <!-- .row -->
            </ul> <!-- .tags -->
        </div> <!-- .details -->
        <div class="container" id="showcaseContainer">

        <!--
            This container will dynamically load one of the following showcases:
                image-showcase
                list-showcase
                text-showcase
                video-showcase
        -->

        <?php
            $_GET['template'] = 'single-project';
            include("includes/image-showcase.php"); ?>
    
        </div> <!-- .container -->
    </div> <!-- .viewer -->
<?php include("includes/footer.php"); ?>
