<?php
    $t = $_GET['template'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width">
        <title>Peter Arnell</title>

        <link href="/lib/fancybox/jquery.fancybox.css" rel="stylesheet" />
        <link href="/lib/jscrollpane/jquery.jscrollpane.css" rel="stylesheet">
        <link href="/build/css/app.css" rel="stylesheet">
    
        <script src="/lib/vendor/custom.modernizr.js"></script>
    </head>
    <body>
        <div class="outer-wrapper">
            <header class="site-header">
                <h1 class="logo"><a href="/">Peter Arnell</a></h1>
                <nav>
                    <h2 class="visuallyhidden">Main Navigation</h2>
                    <ul>
                    <li><a href="/"<?php if ($t =='home'){echo ' class="active"'; } ?>>Home</a></li>
                        <li><a href="/templates/projects.php"<?php if ($t =='projects'){echo ' class="active"'; } ?>>Projects</a></li>
                        <li><a href="/templates/photography.php"<?php if ($t =='photography'){echo ' class="active"'; } ?>>Photography</a></li>
                        <li><a href="/templates/film.php"<?php if ($t =='film'){echo ' class="active"'; } ?>>Film</a></li>
                        <li><a href="/templates/profile.php"<?php if ($t =='profile'){echo ' class="active"'; } ?>>Profile</a></li>
                        <li><a href="/templates/contact.php"<?php if ($t =='contact'){echo ' class="active"'; } ?>>Contact</a></li>
                        <li><a href="/templates/stream.php"<?php if ($t =='stream'){echo ' class="active"'; } ?>>Stream</a></li>
                        <li><a href="#" id="searchIcon" class="search-icon"></a></li>
                    </ul>
                    <form class="search-form" action=""><input type="search" /></form>
                </nav>
                <?php include("filter-bar.php"); ?>
            </header> <!-- .site-header -->
            <div class="page <?php echo $t; ?>">
