<?php 
    $_GET['template'] = 'photography';
    include("includes/header.php"); 
?>

    <h2 class="visuallyhidden">Photography Home</h2>

    <?php 
        $_GET['template'] = 'photo';
        include("includes/filter-bar.php"); ?>

    <div class="covers-container" id="showcaseContainer">
        <!--
            This container will hold cover images for photo galleries.
        -->
        <?php include("includes/image-showcase.php"); ?>
    </div>

<?php include("includes/footer.php"); ?>
