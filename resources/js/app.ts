import { createInertiaApp } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';
import { initializePreviousUrlTracking } from '@/composables/usePreviousUrl';
import SiteLayout from '@/layouts/SiteLayout.vue';

// These are only needed behind auth, so they become their own chunk instead
// of landing in every public visitor's entry bundle.
const AppLayout = defineAsyncComponent(() => import('@/layouts/AppLayout.vue'));
const AuthLayout = defineAsyncComponent(
    () => import('@/layouts/AuthLayout.vue'),
);
const SettingsLayout = defineAsyncComponent(
    () => import('@/layouts/settings/Layout.vue'),
);

let flashToastInitialized = false;

function ensureFlashToast(): void {
    if (flashToastInitialized) {
        return;
    }

    flashToastInitialized = true;
    import('@/lib/flashToast').then(({ initializeFlashToast }) =>
        initializeFlashToast(),
    );
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('site/'):
                return SiteLayout;
            case name.startsWith('auth/'):
                ensureFlashToast();

                return AuthLayout;
            case name.startsWith('settings/'):
                ensureFlashToast();

                return [AppLayout, SettingsLayout];
            default:
                ensureFlashToast();

                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

// This will track the previous page URL for the site's article back button...
initializePreviousUrlTracking();
