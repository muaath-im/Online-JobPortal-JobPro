<?php

// Herd links directly to the JobPro folder. Always redirect from the domain root,
// including when Herd falls back here for an obsolete or malformed address.
header('Cache-Control: no-store');
header('Location: /pages/landing.php', true, 302);
exit;
