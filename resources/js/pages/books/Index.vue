<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import BookDateActionDialog from '@/components/books/BookDateActionDialog.vue';
import BookRow from '@/components/books/BookRow.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import bookRoutes from '@/routes/dashboard/books';
import type { Book } from '@/types';

const props = defineProps<{
    books: Book[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Books', href: bookRoutes.index() },
        ],
    },
});

function destroy(book: Book) {
    if (!confirm(`Delete "${book.title}"? This can't be undone.`)) {
        return;
    }

    router.delete(bookRoutes.destroy(book.id).url);
}

const toBeRead = computed(() =>
    props.books
        .filter((book) => book.started_at === null)
        .sort((a, b) => a.title.localeCompare(b.title)),
);
const currentlyReading = computed(() =>
    props.books.filter(
        (book) => book.started_at !== null && book.finished_at === null,
    ),
);
const finishedBooks = computed(() =>
    props.books.filter((book) => book.finished_at !== null),
);
</script>

<template>
    <Head title="Books" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Books"
                description="Everything you’ve logged in your reading list."
            />
            <Button as-child>
                <Link :href="bookRoutes.create()">Add book</Link>
            </Button>
        </div>

        <p v-if="books.length === 0" class="text-sm text-muted-foreground">
            You haven’t logged any books yet.
        </p>

        <template v-else>
            <section v-if="toBeRead.length > 0" class="flex flex-col gap-3">
                <h2 class="text-sm font-semibold text-muted-foreground">
                    To Be Read
                </h2>
                <div class="divide-y divide-border rounded-lg border">
                    <BookRow
                        v-for="book in toBeRead"
                        :key="book.id"
                        :book="book"
                    >
                        <BookDateActionDialog
                            :form="bookRoutes.start.form(book.id)"
                            field="started_at"
                            trigger-label="Start reading"
                            title="Start reading"
                            description="Set the date you started reading this book."
                            submit-label="Start reading"
                        />
                        <Button as-child variant="outline" size="sm">
                            <Link :href="bookRoutes.edit(book.id)">Edit</Link>
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="destroy(book)"
                            >Delete</Button
                        >
                    </BookRow>
                </div>
            </section>

            <section
                v-if="currentlyReading.length > 0"
                class="flex flex-col gap-3"
            >
                <h2 class="text-sm font-semibold text-muted-foreground">
                    Currently Reading
                </h2>
                <div class="divide-y divide-border rounded-lg border">
                    <BookRow
                        v-for="book in currentlyReading"
                        :key="book.id"
                        :book="book"
                    >
                        <BookDateActionDialog
                            :form="bookRoutes.finish.form(book.id)"
                            field="finished_at"
                            trigger-label="Finish"
                            title="Finish book"
                            description="Set the date you finished reading this book."
                            submit-label="Finish"
                        />
                        <Button as-child variant="outline" size="sm">
                            <Link :href="bookRoutes.edit(book.id)">Edit</Link>
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="destroy(book)"
                            >Delete</Button
                        >
                    </BookRow>
                </div>
            </section>

            <section
                v-if="finishedBooks.length > 0"
                class="flex flex-col gap-3"
            >
                <h2 class="text-sm font-semibold text-muted-foreground">
                    Finished
                </h2>
                <div class="divide-y divide-border rounded-lg border">
                    <BookRow
                        v-for="book in finishedBooks"
                        :key="book.id"
                        :book="book"
                    >
                        <Button as-child variant="outline" size="sm">
                            <Link :href="bookRoutes.edit(book.id)">Edit</Link>
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="destroy(book)"
                            >Delete</Button
                        >
                    </BookRow>
                </div>
            </section>
        </template>
    </div>
</template>
