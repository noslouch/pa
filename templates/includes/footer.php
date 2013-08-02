            </div> <!-- .page -->
        </div> <!-- .outer-wrapper -->
        
        <?php if ( $_GET['template'] == 'home' ) {
            include("templates/includes/noteworthy.php");
        } ?>

        <script src="/lib/vendor/jquery.js"></script>
        <script src="/lib/isotope/jquery.isotope.min.js"></script>
        <script src="/lib/fancybox/jquery.fancybox.js"></script>

        <script src="/build/js/utils.js"></script>

        <?php if ( $_GET['template'] == 'home' ) { ?>
        <script src="/build/js/quotes.js"></script>
        <script>
            checkQuoteHeight()
        </script>
        <?php } ?>

        <?php if ( $_GET['template'] == 'projects' ) { ?>
        <script src="/build/js/starfield.js"></script>
        <?php } ?>


        <?php
            $p = null;
            if ( isset($_SERVER['QUERY_STRING']) ) { $p = $_SERVER['QUERY_STRING']; }

            switch ($p) {
                case 1:
                case 2:
                case 3:
                case 4:
        ?>
        <script src="/build/js/isoRTL.js"></script>
        <?php
                break;
                default:
        ?>
        <script src="/build/js/iso.js"></script>
        <?php break; } ?>

        <script src="/build/js/mockClicks.js"></script>
        <script src="/build/js/main.js"></script>


        <script src="/lib/foundation/foundation.js"></script>
        <script src="/lib/foundation/foundation.tooltips.js"></script>

        <script>
            $(document).foundation()
        </script>

        <script src="http://localhost:35729/livereload.js"></script>
    </body>
</html>
