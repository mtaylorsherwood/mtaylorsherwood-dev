<script setup lang="ts">
import { Card, CardCta, CardEyebrow, CardTitle } from '@/components/site/card';
import SeoHead from '@/components/site/SeoHead.vue';
import SimpleLayout from '@/components/site/SimpleLayout.vue';
import { formatDateRange } from '@/lib/formatDateRange';
import { show as showRecap } from '@/routes/recaps';
import type { PublicRecapSummary } from '@/types';

defineProps<{
    recaps: PublicRecapSummary[];
}>();
</script>

<template>
    <SeoHead
        title="Reading Recaps"
        description="Periodic reading recaps of what I’ve been reading."
    />

    <SimpleLayout
        title="Reading Recaps"
        intro="Little blurbs of what I’ve been reading, likely compressed into a quarter or a month."
    >
        <div class="md:border-l md:border-ash-grey-700/40 md:pl-6">
            <div class="flex max-w-3xl flex-col space-y-16">
                <article
                    v-for="recap in recaps"
                    :key="recap.id"
                    class="md:grid md:grid-cols-4 md:items-baseline"
                >
                    <Card class="md:col-span-3">
                        <CardTitle :href="showRecap(recap.slug)">
                            {{ recap.title }}
                        </CardTitle>
                        <CardEyebrow as="time" class="md:hidden" decorate>
                            {{
                                formatDateRange(recap.starts_at, recap.ends_at)
                            }}
                        </CardEyebrow>
                        <CardCta>Read reading recap</CardCta>
                    </Card>
                    <CardEyebrow as="time" class="mt-1 max-md:hidden">
                        {{ formatDateRange(recap.starts_at, recap.ends_at) }}
                    </CardEyebrow>
                </article>
            </div>
        </div>
    </SimpleLayout>
</template>
