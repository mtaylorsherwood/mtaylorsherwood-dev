<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { PaginationLink } from '@/types';

defineProps<{
    links: PaginationLink[];
}>();
</script>

<template>
    <nav v-if="links.length > 3" class="flex items-center justify-center gap-1">
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="!link.url"
                class="px-3 py-1.5 text-sm text-muted-foreground/50"
                v-html="link.label"
            />
            <Button
                v-else-if="link.active"
                variant="default"
                size="sm"
                disabled
            >
                <span v-html="link.label" />
            </Button>
            <Button v-else as-child variant="outline" size="sm">
                <Link :href="link.url">
                    <span v-html="link.label" />
                </Link>
            </Button>
        </template>
    </nav>
</template>
