<script setup lang="ts">
import { Layers, RotateCcw } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { Book } from '@/types';

const props = defineProps<{
    book: Book;
}>();

const categoryLabel = computed(() =>
    props.book.category === 'fiction' ? 'Fiction' : 'Non-Fiction',
);
</script>

<template>
    <article class="border-b border-ash-grey-700/40 py-4 last:border-0">
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1">
            <div>
                <h3 class="font-medium text-ash-grey-100">
                    {{ book.title }}
                </h3>
                <p class="text-sm text-ash-grey-400">by {{ book.author }}</p>
            </div>
        </div>
        <div
            class="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-ash-grey-400"
        >
            <Badge variant="secondary">{{ categoryLabel }}</Badge>
            <Badge variant="outline">{{ book.genre }}</Badge>
            <Badge
                v-if="book.series"
                class="gap-1 border-baltic-blue-900 bg-baltic-blue-950 text-baltic-blue-300"
            >
                <Layers class="size-3" />
                {{ book.series }}
            </Badge>
            <Badge
                v-if="book.is_reread"
                class="gap-1 border-baltic-blue-900 bg-baltic-blue-950 text-baltic-blue-300"
            >
                <RotateCcw class="size-3" />
                Re-read
            </Badge>
            <span>{{ book.pages }} pages</span>
        </div>
        <p v-if="book.review" class="mt-2 text-sm text-ash-grey-400">
            {{ book.review }}
        </p>
    </article>
</template>
