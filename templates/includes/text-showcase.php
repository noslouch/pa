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
    <article>
    <?php 

        if (isset($_GET['template'])) {
            $q = $_GET['template'];
        } else {
            $q = $_SERVER['QUERY_STRING'];
        }

        if ($q == 'mockInfo') {
    ?>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
    <?php } else if ($q == 'mockPress') { ?>
        <header>
            <h3></h3>
            <time></time>
        </header>
        <div class="img"><img src="" alt="" /></div>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <div class="gallery">
            <div class="img"><img src="" alt="" /></div>
            <div class="img"><img src="" alt="" /></div>
            <div class="img"><img src="" alt="" /></div>
            <div class="img"><img src="" alt="" /></div>
        </div>
        <button></button>
    <?php } else if ($q == 'mockBio') { ?>
        <div class="img"><img src="" alt="" /></div>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>In porttitor eleifend nisl sed dignissim. Quisque lacinia nec diam eget mattis. Mauris sed venenatis turpis. Donec facilisis sem augue, vitae elementum dui tempor eu. Nullam condimentum mauris auctor erat aliquet bibendum nec quis urna. Ut id mauris a nulla elementum condimentum.</p> 

        <p>Mauris faucibus eros varius nunc gravida bibendum. Morbi egestas eget sapien pellentesque tristique. Fusce sit amet mollis justo. Duis nec nisi dui. Ut pulvinar, neque quis aliquet auctor, lectus diam tempor sem, sit amet ullamcorper purus ante a justo. In euismod augue lobortis risus venenatis accumsan.</p>

        <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Morbi lobortis, dui eget bibendum adipiscing, magna est placerat purus, nec malesuada ligula tortor vitae urna. Aliquam tristique neque leo, eu ultricies elit dignissim eget. Sed imperdiet leo at pellentesque condimentum.</p>

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
    <?php } else if ($q == 'mockVideoInfo') { ?>
        <header>
            <h3>Film</h3>
            <time datetime="2013">2013</time>
        </header>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris fringilla, libero et cursus pulvinar, nisl urna bibendum metus, sit amet aliquet libero tellus sit amet velit. Aliquam lectus metus, euismod eget consequat quis, egestas et eros. Mauris rhoncus lacinia varius. Duis lectus arcu, sagittis non ligula suscipit, euismod mattis lacus.</p> 

        <p>Nullam vitae dignissim arcu. Nam ante erat, dictum sed magna sed, tincidunt aliquet nulla. Pellentesque at elit a mi tempus euismod. Duis at mollis augue. Donec a ligula nisl. Proin ut ante turpis.</p>
        <button></button>
    <?php } ?>
    </article>
</div>
