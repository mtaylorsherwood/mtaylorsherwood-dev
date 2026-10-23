<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { cn } from '@/lib/utils';
import type { NavItem } from '@/types';

const props = defineProps<{
    items: NavItem[];
    class?: HTMLAttributes['class'];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <nav :class="props.class">
        <ul
            class="flex rounded-full bg-espresso-950/90 px-3 text-sm font-medium text-ash-grey-200 shadow-lg ring-1 ring-white/10 backdrop-blur-sm"
        >
            <li v-for="item in items" :key="item.title">
                <Link
                    :href="item.href"
                    :class="
                        cn(
                            'relative block px-3 py-2 transition',
                            isCurrentUrl(item.href)
                                ? 'text-spicy-paprika-400'
                                : 'hover:text-spicy-paprika-400',
                        )
                    "
                >
                    {{ item.title }}
                    <span
                        v-if="isCurrentUrl(item.href)"
                        class="absolute inset-x-1 -bottom-px h-px bg-linear-to-r from-spicy-paprika-400/0 via-spicy-paprika-400/40 to-spicy-paprika-400/0"
                    />
                </Link>
            </li>
        </ul>
    </nav>
</template>
