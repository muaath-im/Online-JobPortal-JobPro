<?php
// For hosting, copy to config/mail.local.php, or set JOBPRO_MAIL_CONFIG privately.
// The existing repository includes/mail.local.php remains supported unchanged.
// Examples contain no credentials. Never paste an API key into chat or tracked files.
return [
    'transport' => 'disabled', // brevo for HTTPS delivery; log for LOCAL capture only.
    'base_url' => 'http://jobpro.test', // Set an HTTPS origin before deployment.
    'api_key' => '',
    'from_address' => '',
    'from_name' => 'JobPro',
    'contact_to' => '', // Inbox for contact messages; never use the visitor as sender.
];
