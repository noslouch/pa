<?php
    $q = null;
    $p = null;

    if ( isset($_GET['template']) ) {
        $q = $_GET['template'];
        $p = $_SERVER['QUERY_STRING'];
    } else {
        $q = $_SERVER['QUERY_STRING'];
    }
    
    if ($q == 'projects' || $q == 'mockProjectCovers') {
        $aTag = '<a href="/templates/project-single.php">';
    } else if ($q == 'photography' || $q == 'mockPhotoCovers') {
        $aTag = '<a href="/templates/photo-single.php">';
    } else if ( $q == 'single-project' || $q == 'single-photo' ) {
        $aTag = '<a href="http://placekitten.com/1080/600" class="mockFancybox" rel="gallery" title="Caption goes here">';
    }
?>
                
    <!-- 
        IMAGE SHOWCASE
        *an image gallery sorted by isotope*

        the .fixed class is a conditional class.
            the standard image showcase fixes image heights to 216px or 323px and allows widths to flow proportionally.
            the fixed class will also fix the width to 164px (246px in .large containers) for portrait oriented images or 334px (501px in .large containers) or landscape oriented images

        the .large class is a conditional class.
            if the count of thumbnails to load is less than 5, all thumbnails will be scaled in size to 323px high and either 246px wide, 501px wide, or proportionally wide to its native aspect ratio, depending on the circumstances.
        isotope will handle layout.
    -->
    <?php
        switch($p) {
            case 1:
            case 2:
            case 3:
            case 4:
                    echo '<div class="showcase image large">';
            break;

            default:
                    echo '<div class="showcase image">';
            break;
        }
    ?>

        <div class="clearfix isotope-grid" id="iso-grid">
            <?php if ( $p == '1' ) { ?>
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <?php } else if ( $p == '3' ) { ?>
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/494/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/802/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <?php } else if ( $p == '3a' ) { ?>
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <?php } else if ( $p == '4' ) { ?>
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <?php } else if ( $p == '4a' ) { ?>
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/394/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/802/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->

            <?php } else if ( $p == '8' ) { ?>

            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/601/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/501/309" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/497/284" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/802/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <?php } else if ( $p == '20' ) { ?>
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/802/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/802/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/802/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/802/518" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb">
                <div class="wrapper">
                    <?php 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->


            <?php } else { ?>


            <div class="thumb pepsi food-beverage publications">
                <div class="wrapper">
                <?php if ( $q == 'projects' || $q == 'mockProjectCovers' ) { ?>
                    <a href="/templates/project-single.php?1">
                        <div class="caption">
                            <p>One Photo.</p>
                        </div> <!-- .caption -->
                <?php } else { 
                    echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                <?php } ?>
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb sony technology publications">
                <div class="wrapper">
                <?php if ( $q == 'projects' || $q == 'mockProjectCovers' ) { ?>
                    <a href="/templates/project-single.php?3">
                        <div class="caption">
                            <p>Three Photos.</p>
                        </div> <!-- .caption -->
                <?php } else { 
                    echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                <?php } ?>
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb sony technology product-design">
                <div class="wrapper">
                <?php if ( $q == 'projects' || $q == 'mockProjectCovers' ) { ?>
                    <a href="/templates/project-single.php?3a">
                        <div class="caption">
                            <p>Three Photos Alternate.</p>
                        </div> <!-- .caption -->
                    <?php } else { 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                    <?php } ?>
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .wide .thumb -->
            <div class="wide thumb sony technology product-design">
                <div class="wrapper">
                <?php if ( $q == 'projects' || $q == 'mockProjectCovers' ) { ?>
                    <a href="/templates/project-single.php?4">
                        <div class="caption">
                            <p>Four Photos.</p>
                        </div> <!-- .caption -->
                    <?php } else { 
                        echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                    <?php } ?>
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .wide .thumb -->
            <div class="thumb sony technology communications">
                <div class="wrapper">
                <?php if ( $q == 'projects' || $q == 'mockProjectCovers' ) { ?>
                    <a href="/templates/project-single.php?4a">
                        <div class="caption">
                            <p>Four Photos Alternate.</p>
                        </div> <!-- .caption -->
                <?php } else { 
                    echo $aTag; ?>
                        <div class="caption">
                            <p>This is a slightly longer caption so we can see what it looks like when the captions span multiple lines.</p>
                        </div> <!-- .caption -->
                <? } ?>
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb dkny apparel-fashion publications">
                <div class="wrapper">
                <?php if ( $q == 'projects' || $q == 'mockProjectCovers' ) { ?>
                    <a href="/templates/project-single.php?8">
                        <div class="caption">
                            <p>Eight Photos.</p>
                        </div> <!-- .caption -->
                <?php } else { 
                    echo $aTag; ?>
                        <div class="caption">
                            <p>This is a slightly longer caption so we can see what it looks like when the captions span multiple lines.</p>
                        </div> <!-- .caption -->
                <? } ?>
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb pepsi food-beverage publications">
                <div class="wrapper">
                <?php if ( $q == 'projects' || $q == 'mockProjectCovers' ) { ?>
                    <a href="/templates/project-single.php?20">
                        <div class="caption">
                            <p>Twenty Photos.</p>
                        </div> <!-- .caption -->
                <?php } else { 
                    echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                <?php } ?>
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="thumb sony technology communications">
                <div class="wrapper">
                    <?php echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb pepsi food-beverage product-design">
                <div class="wrapper">
                    <?php echo $aTag; ?>
                        <div class="caption">
                            <p>This is a caption on a wide thumbnail.</p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .wide .thumb -->
            <div class="thumb dkny apparel-fashion communications">
                <div class="wrapper">
                    <?php echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/197/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .thumb -->
            <div class="wide thumb pepsi food-beverage communications">
                <div class="wrapper">
                    <?php echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .wide .thumb -->
            <div class="wide thumb dkny apparel-fashion product-design">
                <div class="wrapper">
                    <?php echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .wide .thumb -->
            <div class="wide thumb sony technology publications">
                <div class="wrapper">
                    <?php echo $aTag; ?>
                        <div class="caption">
                            <p></p>
                        </div> <!-- .caption -->
                        <img src="http://placekitten.com/401/259" alt="" />
                    </a>
                </div> <!-- .wrapper -->
            </div> <!-- .wide .thumb -->
        <?php } ?>
        </div> <!-- .masonry-grid -->
    </div> <!-- .showcase .image -->
