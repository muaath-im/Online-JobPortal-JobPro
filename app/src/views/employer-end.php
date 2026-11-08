</section>
</div>
<script src="<?= $shellEscape($shellAsset('assets/js/employer.js')) ?>" defer></script>
<?php include jobproPath('src/views/footer.php'); if(ob_get_level()>$employerBufferLevel)ob_end_flush(); ?>
