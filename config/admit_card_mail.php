<?php

return [
    // Keep mail dispatch safe by default outside production.
    'dry_run' => env('ADMIT_CARD_MAIL_DRY_RUN', env('APP_ENV', 'production') !== 'production'),
];

