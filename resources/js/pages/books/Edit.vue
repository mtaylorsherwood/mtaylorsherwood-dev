<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import BookController from '@/actions/App/Http/Controllers/BookController';
import BookFormFields from '@/components/books/BookFormFields.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import bookRoutes from '@/routes/dashboard/books';
import type { Book } from '@/types';

defineProps<{
    book: Book;
    genres: string[];
    series: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Books', href: bookRoutes.index() },
        ],
    },
});
</script>

<template>
    <Head title="Edit book" />

    <div class="max-w-xl p-4">
        <Heading title="Edit book" :description="book.title" />

        <Form
            v-bind="BookController.update.form(book.id)"
            class="mt-6 space-y-6"
            v-slot="{ errors, processing }"
        >
            <BookFormFields
                :book="book"
                :errors="errors"
                :genres="genres"
                :series="series"
            />

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Save</Button>
                <Link
                    :href="bookRoutes.index()"
                    class="text-sm text-muted-foreground hover:text-foreground"
                >
                    Cancel
                </Link>
            </div>
        </Form>
    </div>
</template>
