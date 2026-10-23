<script setup lang="ts">
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
</script>

<template>
    <div class="flex min-h-full w-full bg-ash-grey-950">
        <a
            href="#main"
            class="sr-only rounded-md bg-espresso-950 px-4 py-2 text-sm font-medium text-ash-grey-100 focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:outline-2 focus:outline-offset-2 focus:outline-spicy-paprika-500"
        >
            Skip to content
        </a>
        <div class="fixed inset-0 flex justify-center sm:px-8">
            <div class="flex w-full max-w-7xl lg:px-8">
                <div class="w-full bg-espresso-950 ring-1 ring-ash-grey-800" />
            </div>
        </div>
        <div class="relative flex w-full flex-col">
            <SiteHeader />
            <main id="main" class="flex-auto">
                <slot />
            </main>
            <SiteFooter />
        </div>
    </div>
</template>
