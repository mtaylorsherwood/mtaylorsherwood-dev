<script setup lang="ts">
import Container from '@/components/site/Container.vue';
import SeoHead from '@/components/site/SeoHead.vue';
import SpotlightButton from '@/components/site/SpotlightButton.vue';
import { home } from '@/routes';
</script>

<template>
    <SeoHead
        title="Page not found"
        description="Sorry, we couldn’t find the page you’re looking for."
        noindex
    />

    <Container class="flex h-full items-center pt-16 sm:pt-32">
        <div class="flex flex-col items-center">
            <p class="text-base font-semibold text-ash-grey-500">404</p>
            <h1
                class="mt-4 text-4xl font-bold tracking-tight text-ash-grey-100 sm:text-5xl"
            >
                Page not found
            </h1>
            <p class="mt-4 text-base text-ash-grey-400">
                Sorry, we couldn’t find the page you’re looking for.
            </p>
            <SpotlightButton :href="home()" variant="secondary" class="mt-4">
                Go back home
            </SpotlightButton>
        </div>
    </Container>
</template>
