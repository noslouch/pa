<html>
<head>
<title>Error</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" href="underdog.php?S=4d3da89442cc76a0946b4f2493b90d6cd58ccdec&amp;D=cp&amp;C=css" type="text/css" media="screen" title="Global Styles" charset="utf-8" />
<style type="text/css">

body {padding:0;margin:0 auto;font: 13px/20px normal Helvetica, Arial, sans-serif;}
body, html {background:#27343C!important;}

.error_content {border:#999 1px solid; background:#FFF; padding:20px 20px 12px 20px; margin:50px auto; width:80%;}
h2 {font-weight: normal; font-size:17px; color:#990000; margin:0 0 4px 0;}
p{margin:15px 0;}
div#wrapper {background:#27343C; height:17px; margin-bottom:5px;}
</style>


</head>
<body>
<div id="wrapper"></div>
    <div class="error_content">
        <h2>Updater encountered an error</h2>
        <p>You shouldn't see this page. The only reason you are seeing this page is because the Updater "installer" directory still exists on your server.</p>
        <p>
            There are two reason this might happen:
            <ul>
                <li>The ExpressionEngine update initiated by Updater failed. Updater stops immediately when it finds an error during the update process.</li>
                <li>Updater failed to do the final cleanup which removes the installer dir.</li>
            </ul>
        </p>
        <p>The Updater "installer" dir is located at: <strong><?=APPPATH?></strong></p>
        <p><a href="#" onClick="window.location.reload();return false;">Click here to refresh the page</a> </p>
    </div>

    <div class="error_content">
        <h2>Automatic Removal Attempt</h2>
        <p>Updater will now try to manually remove the Updater "installer" dir.</p>

        <p><strong>METHOD 1: Using specified location settings in Updater</strong></p>
        <hr>
        <?=$dupdater->cleanup(TRUE);?>
        <p><strong>METHOD 2: PHP built in file delete (webserver)</strong></p>
        <hr>
        <?php
            sleep (2);
            if (file_exists(APPPATH) === TRUE)
            {
                $this->load->helper('file');
                delete_files(APPPATH, TRUE);
                rmdir(APPPATH);

                if (file_exists(APPPATH) === TRUE) echo 'Failed, directory still exists. Remove it manually.';
                else echo 'Success...';
            }
            else
            {
                echo 'Not Needed, installer dir maybe already removed.';
            }
        ?>
    </div>
</body>
</html>
