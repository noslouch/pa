<?php
    $t = $_GET['template'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Peter Arnell</title>

        <link href="/pa/lib/jscrollpane/jquery.jscrollpane.css" rel="stylesheet">
        <link href="/pa/build/css/app.css" rel="stylesheet">
    
        <script src="/pa/lib/vendor/custom.modernizr.js"></script>
    </head>
    <body>
        <div class="page">
            <header>
                <h1 class="logo"><a href="/pa">Peter Arnell</a></h1>
                <nav>
                    <h2 class="visuallyhidden">Main Navigation</h2>
                    <ul>
                    <li><a href="/pa"<?php if ($t =='home'){echo ' class="active"'; } ?>>Home</a></li>
                        <li><a href="/pa/templates/projects.php"<?php if ($t =='projects'){echo ' class="active"'; } ?>>Projects</a></li>
                        <li><a href="/pa/templates/photography.php"<?php if ($t =='photography'){echo ' class="active"'; } ?>>Photography</a></li>
                        <li><a href="/pa/templates/film.php"<?php if ($t =='film'){echo ' class="active"'; } ?>>Film</a></li>
                        <li><a href="/pa/templates/profile.php"<?php if ($t =='profile'){echo ' class="active"'; } ?>>Profile</a></li>
                        <li><a href="/pa/templates/contact.php"<?php if ($t =='contact'){echo ' class="active"'; } ?>>Contact</a></li>
                        <li><a href="/pa/templates/stream.php"<?php if ($t =='stream'){echo ' class="active"'; } ?>>Stream</a></li>
                        <li><a href="#" id="searchIcon" class="search-icon"></a></li>
                    </ul>
                    <form class="search-form" action=""><input type="search" /></form>
                </nav>
            </header>
