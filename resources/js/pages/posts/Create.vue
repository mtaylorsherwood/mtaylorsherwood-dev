<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import postRoutes from '@/routes/dashboard/posts';
import type { PostProjectOption, RecapBookSummary } from '@/types';

defineProps<{
    books: RecapBookSummary[];
    projects: PostProjectOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Posts', href: postRoutes.index() },
            { title: 'New post', href: postRoutes.create() },
        ],
    },
});
</script>

<template>
    <Head title="New post" />

    <div class="max-w-3xl p-4">
        <Heading
            title="New post"
            description="Pick a type to get started, then fill in the fields that matter for it."
        />

        <Form
            v-bind="PostController.store.form()"
            class="mt-6 space-y-6"
            v-slot="{ errors, processing }"
        >
            <PostFormFields
                :errors="errors"
                :books="books"
                :projects="projects"
            />

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Save draft</Button>
                <Link
                    :href="postRoutes.index()"
                    class="text-sm text-muted-foreground hover:text-foreground"
                >
                    Cancel
                </Link>
            </div>
        </Form>
    </div>
</template>
