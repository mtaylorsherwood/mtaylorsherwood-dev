<script setup lang="ts">
import { computed, ref } from 'vue';
import Container from '@/components/site/Container.vue';
import DesktopNavigation from '@/components/site/DesktopNavigation.vue';
import MobileNavigation from '@/components/site/MobileNavigation.vue';
import { siteNavItems } from '@/components/site/navigation';
import SiteAvatar from '@/components/site/SiteAvatar.vue';
import SiteAvatarContainer from '@/components/site/SiteAvatarContainer.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useSpotlightHeader } from '@/composables/useSpotlightHeader';
import { home } from '@/routes';

const headerRef = ref<HTMLElement | null>(null);
const avatarRef = ref<HTMLElement | null>(null);

const { isCurrentUrl } = useCurrentUrl();
const isHomePage = computed(() => isCurrentUrl(home()));

useSpotlightHeader(headerRef, avatarRef, isHomePage);
</script>

<template>
    <header
        class="pointer-events-none relative z-50 flex flex-none flex-col"
        style="height: var(--header-height); margin-bottom: var(--header-mb)"
    >
        <template v-if="isHomePage">
            <div
                ref="avatarRef"
                class="order-last mt-[calc(--spacing(16)-(--spacing(3)))]"
            />
            <Container
                class="top-0 order-last -mb-3 pt-3"
                style="position: var(--header-position)"
            >
                <div
                    class="top-(--avatar-top,--spacing(3)) w-full"
                    style="position: var(--header-inner-position)"
                >
                    <div class="relative">
                        <SiteAvatarContainer
                            class="absolute top-3 left-0 origin-left transition-opacity"
                            style="
                                opacity: var(--avatar-border-opacity, 0);
                                transform: var(--avatar-border-transform);
                            "
                        />
                        <SiteAvatar
                            large
                            class="block h-16 w-16 origin-left"
                            style="transform: var(--avatar-image-transform)"
                        />
                    </div>
                </div>
            </Container>
        </template>
        <div
            ref="headerRef"
            class="top-0 z-10 h-16 pt-6"
            style="position: var(--header-position)"
        >
            <Container
                class="top-(--header-top,--spacing(6)) w-full"
                style="position: var(--header-inner-position)"
            >
                <div class="relative flex gap-4">
                    <div class="flex flex-1">
                        <SiteAvatarContainer v-if="!isHomePage">
                            <SiteAvatar />
                        </SiteAvatarContainer>
                    </div>
                    <div class="flex flex-1 justify-end md:justify-center">
                        <MobileNavigation
                            :items="siteNavItems"
                            class="pointer-events-auto md:hidden"
                        />
                        <DesktopNavigation
                            :items="siteNavItems"
                            class="pointer-events-auto hidden md:block"
                        />
                    </div>
                    <div class="flex justify-end md:flex-1" />
                </div>
            </Container>
        </div>
    </header>
    <div
        v-if="isHomePage"
        class="flex-none"
        style="height: var(--content-offset)"
    />
</template>
