<?php
$var            = variables();
$set            = $var['setting_home'];
$assets         = $var['assets'];
$url            = $var['url'];
$policy_page_id = (int) get_option( 'wp_page_for_privacy_policy' );
$logo           = carbon_get_theme_option( 'logo' );
$preloader      = carbon_get_theme_option( 'preloader' );
$projects_url    = get_post_type_archive_link( 'projects' );
?>


</main>

<div class="users-cards"></div>

<?php if ( $preloader ): ?>
    <div class="preload-wm" >
        <div class="preload-content">
			<?php the_image( $preloader ); ?>
        </div>
    </div>
<?php endif; ?>

<div class="preloader">
    <img src="<?php echo $assets; ?>img/loading.gif" alt="loading.gif">
</div>

<footer class="footer"></footer>
<div class="dialog-window" id="dialog-js">
    <div class="dialog-title"></div>
    <div class="dialog-subtitle"></div>
</div>
<script>
    var adminAjax = '<?php echo $var['admin_ajax']; ?>';
    var projectsUrl = '<?php echo $projects_url; ?>';
</script>
<?php wp_footer(); ?>

</body>

</html>