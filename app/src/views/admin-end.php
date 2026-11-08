</section></div>
<script src="<?= $shellEscape($shellAsset('assets/js/admin.js')) ?>" defer></script>
<?php include jobproPath('src/views/footer.php');if(ob_get_level()>$adminBufferLevel)ob_end_flush(); ?>
