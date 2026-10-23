<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Card, CardCta, CardEyebrow, CardTitle } from '@/components/site/card';
import { formatDateRange } from '@/lib/formatDateRange';
import recapRoutes from '@/routes/recaps';
import type { PublicRecapSummary } from '@/types';

defineProps<{
    recaps: PublicRecapSummary[];
}>();
</script>

<template>
    <section v-if="recaps.length > 0">
        <h2
            class="text-sm font-semibold tracking-wide text-ash-grey-400 uppercase"
        >
            Recent Reading Recaps
        </h2>
        <div class="mt-4 flex flex-col gap-8">
            <Card v-for="recap in recaps" :key="recap.id" as="article">
                <CardTitle as="h3" :href="recapRoutes.show(recap.slug)">
                    {{ recap.title }}
                </CardTitle>
                <CardEyebrow as="time" decorate>
                    {{ formatDateRange(recap.starts_at, recap.ends_at) }}
                </CardEyebrow>
                <CardCta>Read reading recap</CardCta>
            </Card>
        </div>
        <Link
            :href="recapRoutes.index()"
            class="mt-6 inline-block text-sm font-medium text-spicy-paprika-400 transition hover:text-spicy-paprika-300"
        >
            View all reading recaps &rarr;
        </Link>
    </section>
</template>
