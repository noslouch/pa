<?php
    if (isset($_GET['template'])) {
        $q = $_GET['template'];
    } else {
        $q = $_SERVER['QUERY_STRING'];
    }
    
    if ($q == 'projects' || $q == 'mockProjectList' || $q == 'mockRelated') {
        $aTag = '<a href="/templates/project-single.php">';
    } else if ($q == 'photo' || $q == 'mockPhotoList') {
        $aTag = '<a href="/templates/photo-single.php">';
    } else if ($q == 'mockFilmList') {
        $aTag = '<a href="/templates/film-single.php">';
    }
?>
        <!--
            LIST SHOWCASE
            *a nicely formatted list of titles. summaries are optional.*
        -->
        <div class="showcase list">
            <?php if ($q == 'mockProjectList') { ?>
            <section>
                <ul>
                    <li>
                        <h3>1986</h3>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Project Title
                            </a>
                            <div class="thumb">
                                <img src="http://placekitten.com/197/259" alt="" />
                            </div> <!-- .thumb -->
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <a href="">Project Title</a>
                            <div class="wide thumb">
                                <img src="http://placekitten.com/401/259" alt="" />
                            </div> <!-- .thumb -->
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <a href="">Project Title</a>
                            <div class="wide thumb">
                                <img src="http://placekitten.com/401/259" alt="" />
                            </div> <!-- .thumb -->
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <a href="">Project Title</a>
                            <div class="thumb">
                                <img src="http://placekitten.com/197/259" alt="" />
                            </div> <!-- .thumb -->
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <a href="">Project Title</a>
                            <div class="wide thumb">
                                <img src="http://placekitten.com/401/259" alt="" />
                            </div> <!-- .thumb -->
                        </h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                </ul>
            </section>
            <section>
                <ul>
                    <li>
                        <h3>1987</h3>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Project Title</a></h4>
                    </li>
                </ul>
            </section>
            <?php } else if ($q == 'mockRelated') { ?>
            <section>
                <ul>
                    <li>
                        <h3>Related Projects</h3>
                    </li>
                    <li>
                    <h4>
                        <?php echo $aTag; ?>
                            Project Title
                        </a>
                    </h4>
                        <p></p>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Project Title
                            </a>
                        </h4>
                        <p></p>
                    </li>
                </ul>
            </section>
            <section>
                <ul>
                    <li>
                        <h3>Related Press</h3>
                    </li>
                    <li>
                        <h4><a href="#" id="mockPress">Press Title</a></h4>
                        <p></p>
                    </li>
                    <li>
                        <h4><a href="#" id="mockPress">Press Title</a></h4>
                        <p></p>
                    </li>
                </ul>
            </section>
            <?php } else if ($q == 'mockPressList') { ?>
            <section>
                <ul>
                    <li>
                        <h3>2013</h3>
                    </li>
                    <li>     
                        <a href="#" id="mockPress">
                            <h4>Press Title</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit.</p>
                        </a>
                   </li>
                    <li>
                        <a href="#" id="mockPress">
                            <h4>Press Title</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit.</p>
                        </a>
                   </li>
                </ul>
            </section>
            <section>
                <ul>
                    <li>
                        <h3>2012</h3>
                    </li>
                    <li>
                        <a href="#" id="mockPress">
                            <h4>Press Title</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit.</p>
                        </a>
                   </li>
                    <li>
                        <a href="#" id="mockPress">
                            <h4>Press Title</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit.</p>
                        </a>
                   </li>
                    <li>
                        <a href="#" id="mockPress">
                            <h4>Press Title</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit.</p>
                        </a>
                   </li>
                </ul>
            </section>
            <?php } else if ($q == 'mockAwardList') { ?>
            <section>
                <ul>
                    <li>
                        <h3>2013</h3>
                    </li>
                    <li>
                        <h4><a href="">Award Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit.</p>
                    </li>
                    <li>
                        <h4><a href="">Award Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                </ul>
            </section>
            <section>
                <ul>
                    <li>
                        <h3>2012</h3>
                    </li>
                    <li>
                        <h4><a href="">Award Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                    <li>
                        <h4><a href="">Award Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                    <li>
                        <h4><a href="">Award Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                </ul>
            </section>
            <?php } else if($q == 'mockArticleList') { ?>
            <section>
                <ul>
                    <li>
                        <h3>2013</h3>
                    </li>
                    <li>
                        <a href="#" id="mockArticle">
                            <h4>Article Title</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit.</p>
                        </a>
                    </li>
                    <li>
                        <h4><a href="">Article Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                </ul>
            </section>
            <section>
                <ul>
                    <li>
                        <h3>2012</h3>
                    </li>
                    <li>
                        <h4><a href="">Article Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                    <li>
                        <h4><a href="">Article Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                    <li>
                        <h4><a href="">Article Title</a></h4>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar.</p>
                    </li>
                </ul>
            </section>
            <?php } else if ($q == 'mockPhotoList') { ?>
            <section>
                <ul>
                    <li>
                        <h3>1986</h3>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Photo Gallery
                            </a>
                        </h4>
                    </li>
                </ul>
            </section>
            <section>
                <ul>
                    <li>
                        <h3>1987</h3>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                    <li>
                        <h4><a href="">Photo Gallery</a></h4>
                    </li>
                </ul>
            </section>
            <?php } else if ($q == 'mockFilmList') { ?>
            <section>
                <ul>
                    <li>
                        <h3>1986</h3>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                </ul>
            </section>
            <section>
                <ul>
                    <li>
                        <h3>1987</h3>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                    <li>
                        <h4>
                            <?php echo $aTag; ?>
                                Film
                            </a>
                        </h4>
                    </li>
                </ul>
            </section>
            <?php } ?>
        </div> <!-- .showcase .list -->
