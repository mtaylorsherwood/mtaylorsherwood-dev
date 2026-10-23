<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/Heading.vue';
import PostFormFields from '@/components/posts/PostFormFields.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    isoToLocalDateTimeInput,
    localDateTimeInputToIso,
} from '@/lib/formatDateTimeLocal';
import { dashboard } from '@/routes';
import postRoutes from '@/routes/dashboard/posts';
import type { Post, PostProjectOption, RecapBookSummary } from '@/types';

const props = defineProps<{
    post: Post;
    books: RecapBookSummary[];
    projects: PostProjectOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Posts', href: postRoutes.index() },
        ],
    },
});

const publishAt = ref(
    props.post.published_at
        ? isoToLocalDateTimeInput(props.post.published_at)
        : isoToLocalDateTimeInput(new Date().toISOString()),
);

const isScheduled = computed(
    () =>
        props.post.status === 'published' &&
        props.post.published_at !== null &&
        new Date(props.post.published_at) > new Date(),
);

const statusLabel = computed(() => {
    if (props.post.status !== 'published') {
        return 'Draft';
    }

    return isScheduled.value ? 'Scheduled' : 'Published';
});

function publish() {
    router.post(postRoutes.publish(props.post.id).url, {
        published_at: localDateTimeInputToIso(publishAt.value),
    });
}
</script>

<template>
    <Head title="Edit post" />

    <div class="max-w-3xl p-4">
        <div class="flex items-center justify-between">
            <Heading title="Edit post" :description="post.title" />
            <Badge
                :variant="statusLabel === 'Published' ? 'default' : 'secondary'"
            >
                {{ statusLabel }}
            </Badge>
        </div>

        <Form
            v-bind="PostController.update.form(post.id)"
            class="mt-6 space-y-6"
            v-slot="{ errors, processing }"
        >
            <PostFormFields
                :post="post"
                :errors="errors"
                :books="books"
                :projects="projects"
            />

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Save</Button>
                <Link
                    :href="postRoutes.index()"
                    class="text-sm text-muted-foreground hover:text-foreground"
                >
                    Cancel
                </Link>
            </div>
        </Form>

        <div class="mt-8 rounded-lg border p-4">
            <Label for="publish_at">Publish date &amp; time</Label>
            <p class="mt-1 text-xs text-muted-foreground">
                Leave this as now to publish immediately, or pick a future date
                to schedule it — the post stays hidden on the site until then.
            </p>
            <div class="mt-3 flex items-center gap-3">
                <Input
                    id="publish_at"
                    v-model="publishAt"
                    type="datetime-local"
                    class="max-w-xs"
                />
                <Button type="button" @click="publish">
                    {{
                        statusLabel === 'Draft'
                            ? 'Publish'
                            : 'Update publish date'
                    }}
                </Button>
            </div>
        </div>
    </div>
</template>
