import { router } from '@inertiajs/vue3';
import type { DeepReadonly, Ref } from 'vue';
import { readonly, ref } from 'vue';

const previousUrl = ref<string | null>(null);
let currentUrl: string | null = null;
let isInitialNavigation = true;
let listening = false;

/**
 * Starts tracking the URL visited before the current one. Must run once at
 * app bootstrap (not lazily inside a page component) — the `navigate` event
 * for the transition into the first article a visitor opens has already
 * fired by the time that page's own `<script setup>` runs, so attaching the
 * listener from within the page itself would always miss it.
 *
 * Mirrors the `previousPathname` context the Spotlight template builds in its
 * Next.js providers.
 */
export function initializePreviousUrlTracking(): void {
    if (listening || typeof window === 'undefined') {
        return;
    }

    listening = true;

    // Inertia fires `navigate` once for the initial page load in addition to
    // subsequent visits, so the first event must be ignored — otherwise
    // previousUrl starts out pointing at the current page itself instead of
    // staying null.
    router.on('navigate', (event) => {
        if (isInitialNavigation) {
            isInitialNavigation = false;
        } else {
            previousUrl.value = currentUrl;
        }

        currentUrl = event.detail.page.url;
    });
}

export function usePreviousUrl(): {
    previousUrl: DeepReadonly<Ref<string | null>>;
    goBack: () => void;
} {
    function goBack() {
        if (typeof window !== 'undefined') {
            window.history.back();
        }
    }

    return {
        previousUrl: readonly(previousUrl),
        goBack,
    };
}
