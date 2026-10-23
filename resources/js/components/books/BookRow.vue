<script setup lang="ts">
import { Layers, RotateCcw } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import type { Book } from '@/types';

defineProps<{
    book: Book;
}>();

function categoryLabel(category: Book['category']): string {
    return category === 'fiction' ? 'Fiction' : 'Non-Fiction';
}
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-4 p-4">
        <div class="min-w-0">
            <p class="truncate font-medium">{{ book.title }}</p>
            <p class="text-sm text-muted-foreground">by {{ book.author }}</p>
            <div
                class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground"
            >
                <Badge variant="secondary">{{
                    categoryLabel(book.category)
                }}</Badge>
                <Badge variant="outline">{{ book.genre }}</Badge>
                <Badge
                    v-if="book.series"
                    class="border-baltic-blue-900 bg-baltic-blue-950 text-baltic-blue-300"
                >
                    <Layers class="size-3" />
                    {{ book.series }}
                </Badge>
                <Badge
                    v-if="book.is_reread"
                    class="border-baltic-blue-900 bg-baltic-blue-950 text-baltic-blue-300"
                >
                    <RotateCcw class="size-3" />
                    Re-read
                </Badge>
                <span>{{ book.pages }} pages</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <slot />
        </div>
    </div>
</template>
