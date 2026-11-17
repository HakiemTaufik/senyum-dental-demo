<?php

return [
    /*
     * May search engines list this site?
     * Only when this is true AND the site runs in production. The test copy (staging)
     * and your PC are never indexed. Keep false for the demo clinic so a fictional
     * clinic never appears in Google; set SEO_INDEXABLE=true for a real clinic.
     */
    'indexable' => (bool) env('SEO_INDEXABLE', false) && env('APP_ENV') === 'production',

    // Image shown when a page is shared on WhatsApp / Facebook (1200×630).
    'default_image' => 'images/og-default.png',
];
