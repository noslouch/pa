<?php

    if (isset($_GET['template'])) {
        $q = $_GET['template'];
    } else {
        $q = $_SERVER['QUERY_STRING'];
    }

?>

<!--
    TEXT SHOWCASE
    *a simple text viewer with a few variations*
    
    Everything besides the p tags are optional. Some structural variations:
        press clipping or article: header>h3+time | p tags | gallery | button
        bio: .img | p tags
        project info: p tags (special font)
        film info: header>h3+time | p tags | button
-->

<div class="showcase text">
    <?php if ($q == 'mockInfo') { ?>
    <article class="project-info">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
    </article>
    <?php } else if ($q == 'mockPress') { ?>
    <article class="press">
        <header>
            <h3>Press Title</h3>
            <time datetime="2013-7-12">August 12, 2013</time>
        </header>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <div class="gallery">
            <div class="img">
                <a href="http://placehold.it/1168x817" class="mockFancybox" rel="gallery">
                    <img src="http://placehold.it/168x217" alt="" />
                </a>
            </div>
            <div class="img">
                <a href="http://placehold.it/918x767" class="mockFancybox" rel="gallery">
                    <img src="http://placehold.it/168x217" alt="" />
                </a>
            </div>
            <div class="img">
                <a href="http://placehold.it/1068x817" class="mockFancybox" rel="gallery">
                    <img src="http://placehold.it/168x217" alt="" />
                </a>
            </div>
        </div>
        <div class="wrapper">
            <a href="/templates/profile.php?mockPressList" class="button">View All Press</a>
        </div>
    </article>
    <?php } else if ($q == 'mockArticle') { ?>
    <article class="press">
        <header>
            <h3>Article Title</h3>
            <time datetime="2013-7-12">August 12, 2013</time>
        </header>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <div class="gallery">
            <div class="img">
                <a href="http://placehold.it/968x717" class="mockFancybox" rel="gallery">
                    <img src="http://placehold.it/168x217" alt="" />
                </a>
            </div>
            <div class="img">
                <a href="http://placehold.it/868x617" class="mockFancybox" rel="gallery">
                    <img src="http://placehold.it/168x217" alt="" />
                </a>
            </div>
            <div class="img">
                <a href="http://placehold.it/768x717" class="mockFancybox" rel="gallery">
                    <img src="http://placehold.it/168x217" alt="" />
                </a>
            </div>
        </div>
        <div class="wrapper">
            <a href="/templates/profile.php?mockArticleList" class="button">View All Articles</a>
        </div>
    </article>
    <?php } else if ($q == 'mockBio') { ?>
    <article class="bio">
        <div class="img">
            <img src="/assets/img/arnellbio.png" alt="" />
        </div>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
    </article>
    <?php } else if ($q == 'mockVideoInfo') { ?>
    <article>
        <header>
            <h3>Film</h3>
            <time datetime="2013">2013</time>
        </header>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <a href="/templates/film.php" class="button">View All Film</a>
    </article>
    <?php } ?>
</div>
