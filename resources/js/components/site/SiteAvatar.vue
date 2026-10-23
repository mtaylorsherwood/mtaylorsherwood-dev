<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';
import avatarImage from '@/images/avatar.jpg';
import { cn } from '@/lib/utils';
import { home } from '@/routes';

const props = withDefaults(
    defineProps<{
        large?: boolean;
        class?: HTMLAttributes['class'];
    }>(),
    {
        large: false,
    },
);
</script>

<template>
    <Link
        :href="home()"
        aria-label="Home"
        :class="cn(props.class, 'pointer-events-auto')"
    >
        <img
            :src="avatarImage"
            alt=""
            :class="
                cn(
                    'rounded-full bg-espresso-900 object-cover',
                    large ? 'h-16 w-16' : 'h-9 w-9',
                )
            "
        />
    </Link>
</template>
