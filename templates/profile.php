<?php 
    $_GET['template'] = 'profile';
    include("includes/header.php"); 
?>

    <h2 class="visuallyhidden">Profile Home</h2>

    <div class="viewer">

        <div class="details">
            <ul class="showcase-links">
                <li><a href="#" id="mockPressList">Press</a></li>
                <li><a href="#" id="mockBio">Bio/CV</a></li>
                <li><a href="#" id="mockAwardList">Selected Awards</a></li>
                <li><a href="#" id="mockImageGallery">Photos of PA</a></li>
                <li><a href="#" id="mockArticleList">Articles by PA</a></li>
                <li><a href="#" id="mockArticleList">Articles about PA</a></li>
                <li><a href="#" id="mockArticleList">Interviews</a></li>
                <li><a href="#" id="mockArticleList">Transcripts</a></li>
                <li><a href="#" id="mockArticleList">Collaborators/Acknowledgements</a></li>
            </ul>
        </div> <!-- .showcase-links -->
        <div class="container" id="showcaseContainer">

        </div> <!-- .container -->

    </div> <!-- .viewer -->
<?php include("includes/footer.php"); ?>
