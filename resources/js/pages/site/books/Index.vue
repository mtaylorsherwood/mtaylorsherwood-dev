<script setup lang="ts">
import { computed, ref } from 'vue';
import BookCard from '@/components/books/BookCard.vue';
import BookFilters from '@/components/books/BookFilters.vue';
import ReadingStats from '@/components/books/ReadingStats.vue';
import RecentRecaps from '@/components/books/RecentRecaps.vue';
import SeoHead from '@/components/site/SeoHead.vue';
import SimpleLayout from '@/components/site/SimpleLayout.vue';
import { monthName } from '@/lib/months';
import type {
    Book,
    BookFilters as BookFiltersState,
    PublicRecapSummary,
} from '@/types';

const props = defineProps<{
    books: Book[];
    recaps: PublicRecapSummary[];
}>();

function yearOf(dateString: string): number {
    return new Date(dateString).getUTCFullYear();
}

function monthOf(dateString: string): number {
    return new Date(dateString).getUTCMonth() + 1;
}

function totalPages(books: Book[]): number {
    return books.reduce((sum, book) => sum + book.pages, 0);
}

const currentlyReading = computed(() =>
    props.books.filter((book) => book.finished_at === null),
);
const finishedBooks = computed(() =>
    props.books.filter((book) => book.finished_at !== null),
);

const filters = ref<BookFiltersState>({
    year: null,
    month: null,
    category: null,
    genre: null,
    series: null,
    author: null,
});

const availableYears = computed(() =>
    Array.from(
        new Set(finishedBooks.value.map((book) => yearOf(book.finished_at!))),
    ).sort((a, b) => b - a),
);

const availableMonths = computed(() =>
    Array.from({ length: 12 }, (_, i) => i + 1),
);

const availableGenres = computed(() =>
    Array.from(new Set(props.books.map((book) => book.genre))).sort(),
);

const availableSeries = computed(() =>
    Array.from(
        new Set(
            props.books
                .map((book) => book.series)
                .filter((s): s is string => s !== null),
        ),
    ).sort(),
);

const availableAuthors = computed(() =>
    Array.from(new Set(props.books.map((book) => book.author))).sort(),
);

function matchesFilters(book: Book): boolean {
    if (filters.value.category && book.category !== filters.value.category) {
        return false;
    }

    if (filters.value.genre && book.genre !== filters.value.genre) {
        return false;
    }

    if (filters.value.series && book.series !== filters.value.series) {
        return false;
    }

    if (filters.value.author && book.author !== filters.value.author) {
        return false;
    }

    return true;
}

const filteredCurrentlyReading = computed(() =>
    currentlyReading.value.filter(matchesFilters),
);

const filteredFinished = computed(() =>
    finishedBooks.value.filter((book) => {
        if (!matchesFilters(book)) {
            return false;
        }

        if (
            filters.value.year &&
            yearOf(book.finished_at!) !== filters.value.year
        ) {
            return false;
        }

        if (
            filters.value.month &&
            monthOf(book.finished_at!) !== filters.value.month
        ) {
            return false;
        }

        return true;
    }),
);

type MonthGroup = { month: number; books: Book[] };
type YearGroup = { year: number; books: Book[]; months: MonthGroup[] };

const groupedByYear = computed<YearGroup[]>(() => {
    const years = new Map<number, Map<number, Book[]>>();

    for (const book of filteredFinished.value) {
        const year = yearOf(book.finished_at!);
        const month = monthOf(book.finished_at!);

        if (!years.has(year)) {
            years.set(year, new Map());
        }

        const months = years.get(year)!;

        if (!months.has(month)) {
            months.set(month, []);
        }

        months.get(month)!.push(book);
    }

    return Array.from(years.entries())
        .sort(([a], [b]) => b - a)
        .map(([year, months]) => ({
            year,
            books: Array.from(months.values()).flat(),
            months: Array.from(months.entries())
                .sort(([a], [b]) => b - a)
                .map(([month, books]) => ({ month, books })),
        }));
});
</script>

<template>
    <SeoHead
        title="Books"
        description="A running log of what I've been reading, book by book, year by year."
    />

    <SimpleLayout
        title="What I’ve Been Reading."
        intro="A running log of the books I’ve read, split out by year and month. The recaps give an idea of my thoughts on the books."
    >
        <div class="flex flex-col gap-16">
            <div class="grid grid-cols-1 gap-16 lg:grid-cols-2">
                <section>
                    <h2
                        class="text-sm font-semibold tracking-wide text-ash-grey-400 uppercase"
                    >
                        Currently Reading
                    </h2>
                    <div class="mt-4">
                        <template v-if="filteredCurrentlyReading.length > 0">
                            <BookCard
                                v-for="book in filteredCurrentlyReading"
                                :key="book.id"
                                :book="book"
                            />
                        </template>
                        <p v-else class="text-sm text-ash-grey-400">
                            I’ve not currently got a book on the go.
                        </p>
                    </div>
                </section>

                <RecentRecaps :recaps="recaps" />
            </div>

            <section>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <BookFilters
                        v-model="filters"
                        :years="availableYears"
                        :months="availableMonths"
                        :genres="availableGenres"
                        :series="availableSeries"
                        :authors="availableAuthors"
                    />
                    <p class="text-sm whitespace-nowrap text-ash-grey-400">
                        <ReadingStats
                            :books="filteredFinished.length"
                            :pages="totalPages(filteredFinished)"
                        />
                    </p>
                </div>

                <p
                    v-if="groupedByYear.length === 0"
                    class="mt-8 text-sm text-ash-grey-400"
                >
                    No books match those filters.
                </p>

                <div
                    v-for="yearGroup in groupedByYear"
                    :key="yearGroup.year"
                    class="mt-10"
                >
                    <div
                        class="flex items-baseline justify-between border-b border-ash-grey-700/40 pb-2"
                    >
                        <h2 class="text-2xl font-bold text-ash-grey-100">
                            {{ yearGroup.year }}
                        </h2>
                        <span class="text-sm text-ash-grey-400">
                            <ReadingStats
                                :books="yearGroup.books.length"
                                :pages="totalPages(yearGroup.books)"
                            />
                        </span>
                    </div>

                    <div
                        v-for="monthGroup in yearGroup.months"
                        :key="monthGroup.month"
                        class="mt-6"
                    >
                        <div class="flex items-baseline justify-between">
                            <h3 class="text-sm font-semibold text-ash-grey-300">
                                {{ monthName(monthGroup.month) }}
                            </h3>
                            <span class="text-xs text-ash-grey-500">
                                <ReadingStats
                                    :books="monthGroup.books.length"
                                    :pages="totalPages(monthGroup.books)"
                                />
                            </span>
                        </div>
                        <BookCard
                            v-for="book in monthGroup.books"
                            :key="book.id"
                            :book="book"
                        />
                    </div>
                </div>
            </section>
        </div>
    </SimpleLayout>
</template>
