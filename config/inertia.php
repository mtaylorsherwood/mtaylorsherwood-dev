<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Inertia uses Server Side Rendering
    | to pre-render each initial request made to your application's pages
    | so that server rendered HTML is delivered for the user's browser.
    |
    | See: https://inertiajs.com/server-side-rendering
    |
    */

    /*
     * SSR is not optional for this site: every meta tag on the public pages
     * (title, description, canonical, OG, Twitter Card, JSON-LD) is emitted by
     * SeoHead.vue on the client, so without server rendering, crawlers and link
     * unfurlers see an empty body. Inertia falls back to client-only rendering
     * *silently* when the SSR server is unreachable - see the SsrRenderFailed
     * listener in AppServiceProvider, which exists to make that failure loud.
     *
     * Kept behind an env var (rather than hardcoded true) so it can be disabled
     * deliberately - the test suite does exactly that, since it would otherwise
     * make a real HTTP round trip to the Vite dev server on every render.
     */
    'ssr' => [
        'enabled' => env('INERTIA_SSR_ENABLED', true),
        'url' => 'http://127.0.0.1:13714',
        // 'bundle' => base_path('bootstrap/ssr/ssr.mjs'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | These options configure how Inertia discovers page components on the
    | filesystem. The paths and extensions are used to locate components
    | when rendering responses and during testing assertions.
    |
    */

    'pages' => [

        'paths' => [
            resource_path('js/pages'),
        ],

        'extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | The values described here are used to locate Inertia components on the
    | filesystem. For instance, when using `assertInertia`, the assertion
    | attempts to locate the component as a file relative to the paths.
    |
    */

    'testing' => [

        'ensure_pages_exist' => true,

    ],

];
