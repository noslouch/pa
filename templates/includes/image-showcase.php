    <!-- 
        IMAGE SHOWCASE
        *an image gallery sorted by isotope*

        the .fixed class is a conditional class.
            the standard image showcase fixes image heights to 216px and allows widths to flow proportionally.
            the fixed class will also fix the width to 164px for portrait oriented images or 334px or landscape oriented images

        the .large class is a conditional class.
            if the count of thumbnails to load is less than 5, all thumbnails will be doubled in size to 432px high and either 328px wide, 668px wide, or proportionally wide to its native aspect ratio, depending on the circumstances.
        isotope will handle layout.
    -->
    <div class="showcase image fixed large">
        <div class="isotope-grid" id="iso-grid">
            <div class="thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <?php
                    if (isset($_GET['template'])) {
                        $q = $_GET['template'];
                    } else {
                        $q = $_SERVER['QUERY_STRING'];
                    }
                    
                    if ($q == 'projects' || $q == 'mockProjectCovers') {
                        $aTag = '<a href="/pa/templates/project-single.php">';
                    } else if ($q == 'photo' || $q == 'mockPhotoCovers') {
                        $aTag = '<a href="/pa/templates/photo-single.php">';
                    } else if ($q == 'single-project' || $q == 'single-photo') {
                        $aTag = '<a href="#" id="mockFancybox">';
                    }
                ?>
                
                <?php echo $aTag; ?>
                    <img src="http://placekitten.com/164/216" alt="" />
                </a>
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <?php echo $aTag; ?>
                <img src="http://placekitten.com/334/216" alt="" /></a>
            </div> <!-- .wide .thumb -->
            <div class="thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <?php echo $aTag; ?>
                <img src="http://placekitten.com/164/216" alt="" /></a>
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <?php echo $aTag; ?>
                <img src="http://placekitten.com/164/216" alt="" /></a>
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <?php echo $aTag; ?>
                <img src="http://placekitten.com/164/216" alt="" /></a>
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <a href=""><img src="http://placekitten.com/334/216" alt="" /></a>
            </div> <!-- .wide .thumb -->
            <div class="thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <a href=""><img src="http://placekitten.com/164/216" alt="" /></a>
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <a href=""><img src="http://placekitten.com/334/216" alt="" /></a>
            </div> <!-- .wide .thumb -->
            <div class="wide thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <a href=""><img src="http://placekitten.com/334/216" alt="" /></a>
            </div> <!-- .wide .thumb -->
            <div class="wide thumb">
                <div class="caption">
                    <p></p>
                </div> <!-- .caption -->
                <a href=""><img src="http://placekitten.com/334/216" alt="" /></a>
            </div> <!-- .wide .thumb -->
        </div> <!-- .masonry-grid -->
    </div> <!-- .showcase .image -->
