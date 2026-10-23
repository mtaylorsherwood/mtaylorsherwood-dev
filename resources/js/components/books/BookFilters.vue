<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { monthName } from '@/lib/months';
import type { BookCategory, BookFilters } from '@/types';

const props = defineProps<{
    years: number[];
    months: number[];
    genres: string[];
    series: string[];
    authors: string[];
}>();

const filters = defineModel<BookFilters>({ required: true });

const categories: { value: BookCategory; label: string }[] = [
    { value: 'fiction', label: 'Fiction' },
    { value: 'non_fiction', label: 'Non-Fiction' },
];

const hasActiveFilters = computed(() =>
    Object.values(filters.value).some((value) => value !== null),
);

function clear() {
    filters.value = {
        year: null,
        month: null,
        category: null,
        genre: null,
        series: null,
        author: null,
    };
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Select
            :model-value="filters.year ? String(filters.year) : 'all'"
            @update:model-value="
                (v) => (filters.year = v === 'all' ? null : Number(v))
            "
        >
            <SelectTrigger size="sm" class="w-[8.5rem]">
                <SelectValue placeholder="Year" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">All years</SelectItem>
                <SelectItem
                    v-for="year in props.years"
                    :key="year"
                    :value="String(year)"
                >
                    {{ year }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            :model-value="filters.month ? String(filters.month) : 'all'"
            @update:model-value="
                (v) => (filters.month = v === 'all' ? null : Number(v))
            "
        >
            <SelectTrigger size="sm" class="w-[8.5rem]">
                <SelectValue placeholder="Month" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">All months</SelectItem>
                <SelectItem
                    v-for="month in props.months"
                    :key="month"
                    :value="String(month)"
                >
                    {{ monthName(month) }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            :model-value="filters.category ?? 'all'"
            @update:model-value="
                (v) =>
                    (filters.category =
                        v === 'all' ? null : (v as BookCategory))
            "
        >
            <SelectTrigger size="sm" class="w-[9.5rem]">
                <SelectValue placeholder="Category" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">All categories</SelectItem>
                <SelectItem
                    v-for="category in categories"
                    :key="category.value"
                    :value="category.value"
                >
                    {{ category.label }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            :model-value="filters.genre ?? 'all'"
            @update:model-value="
                (v) => (filters.genre = v === 'all' ? null : (v as string))
            "
        >
            <SelectTrigger size="sm" class="w-[9.5rem]">
                <SelectValue placeholder="Genre" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">All genres</SelectItem>
                <SelectItem
                    v-for="genre in props.genres"
                    :key="genre"
                    :value="genre"
                >
                    {{ genre }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            v-if="props.series.length > 0"
            :model-value="filters.series ?? 'all'"
            @update:model-value="
                (v) => (filters.series = v === 'all' ? null : (v as string))
            "
        >
            <SelectTrigger size="sm" class="w-[9.5rem]">
                <SelectValue placeholder="Series" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">All series</SelectItem>
                <SelectItem
                    v-for="item in props.series"
                    :key="item"
                    :value="item"
                >
                    {{ item }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Select
            v-if="props.authors.length > 0"
            :model-value="filters.author ?? 'all'"
            @update:model-value="
                (v) => (filters.author = v === 'all' ? null : (v as string))
            "
        >
            <SelectTrigger size="sm" class="w-[9.5rem]">
                <SelectValue placeholder="Author" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">All authors</SelectItem>
                <SelectItem
                    v-for="author in props.authors"
                    :key="author"
                    :value="author"
                >
                    {{ author }}
                </SelectItem>
            </SelectContent>
        </Select>

        <Button
            v-if="hasActiveFilters"
            variant="ghost"
            size="sm"
            @click="clear"
        >
            Clear filters
        </Button>
    </div>
</template>
