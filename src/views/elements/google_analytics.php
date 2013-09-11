<?php
/**Google Analytics configuration
 */
//$controller = new \Jazzee\Controller();
$configuration = $controller->getConfig();

if($configuration->getEnableGoogleAnalytics()){
?>
    <!-- Google Analytics -->
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php print $configuration->getGoogleAnalyticsWebPropertyId(); ?>');
    <?php if($dimension = $configuration->getGoogleAnalyticsSiteDimension()){ ?>
        var dimensionValue = '<?php print $controller->getGoogleAnalyticsDeminsion('site'); ?>';
        ga('set', '<?php print $dimension; ?>', dimensionValue);
    <?php  } ?>
    ga('send', 'pageview');

    </script>
    <!-- End Google Analytics -->
<?php } ?>