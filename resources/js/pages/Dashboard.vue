<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BookMarked, BookOpenCheck, NotebookPen } from '@lucide/vue';
import MetricCard from '@/components/dashboard/MetricCard.vue';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { dashboard } from '@/routes';
import type { DashboardMetrics } from '@/types';

defineProps<{
    metrics: DashboardMetrics;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <MetricCard
                title="Posts"
                :value="metrics.posts.total"
                :icon="NotebookPen"
                :comparisons="metrics.posts.comparisons"
            />
            <MetricCard
                title="Books read"
                :value="metrics.books_read.total"
                :icon="BookOpenCheck"
                :comparisons="metrics.books_read.comparisons"
            />
            <MetricCard
                title="To be read"
                :value="metrics.books_tbr.total"
                :icon="BookMarked"
                :comparisons="metrics.books_tbr.comparisons"
            />
        </div>
        <div
            class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
        >
            <PlaceholderPattern />
        </div>
    </div>
</template>
