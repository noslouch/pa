<?php
    $_GET['template'] = 'film';
    include("includes/header.php"); 
?>
    <h2 class="visuallyhidden">Film Home</h2>

    <?php
        //$_GET['template'] = 'film';
        //include("includes/filter-bar.php"); ?>

    <div class="covers-container" id="showcaseContainer">
        <?php include("includes/film-grid.php"); ?>
    </div>

<?php include("includes/footer.php"); ?>
