<script setup lang="ts">
import { Minus, TrendingDown, TrendingUp } from '@lucide/vue';
import type { Component } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { MetricComparison } from '@/types';

withDefaults(
    defineProps<{
        title: string;
        value: number;
        icon: Component;
        comparisons?: MetricComparison[];
    }>(),
    {
        comparisons: () => [],
    },
);

function trend(comparison: MetricComparison): 'up' | 'down' | 'flat' {
    if (comparison.current === comparison.previous) {
        return 'flat';
    }

    return comparison.current > comparison.previous ? 'up' : 'down';
}
</script>

<template>
    <Card>
        <CardHeader
            class="flex flex-row items-center justify-between gap-2 space-y-0 pb-2"
        >
            <CardTitle class="text-sm font-medium text-muted-foreground">
                {{ title }}
            </CardTitle>
            <component :is="icon" class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent class="space-y-3">
            <p class="text-3xl font-semibold">{{ value }}</p>

            <div
                v-for="comparison in comparisons"
                :key="comparison.label"
                class="flex items-center justify-between gap-2 text-xs text-muted-foreground"
            >
                <span
                    >{{ comparison.label }}
                    <span class="text-muted-foreground/70"
                        >({{ comparison.range }})</span
                    ></span
                >
                <span
                    class="inline-flex shrink-0 items-center gap-1 font-medium"
                    :class="{
                        'text-turf-green-400': trend(comparison) === 'up',
                        'text-spicy-paprika-400': trend(comparison) === 'down',
                    }"
                >
                    <TrendingUp
                        v-if="trend(comparison) === 'up'"
                        class="h-3 w-3"
                    />
                    <TrendingDown
                        v-else-if="trend(comparison) === 'down'"
                        class="h-3 w-3"
                    />
                    <Minus v-else class="h-3 w-3" />
                    {{ comparison.current }} vs {{ comparison.previous }}
                </span>
            </div>
        </CardContent>
    </Card>
</template>
