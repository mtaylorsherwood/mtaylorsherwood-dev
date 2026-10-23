<script setup lang="ts">
import { BookOpen, FolderGit2, NotebookPen, Sprout } from '@lucide/vue';
import { MdEditor } from 'md-editor-v3';
import type { ExposeParam } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import { computed, ref, useTemplateRef, watch } from 'vue';
import ImageUploadButton from '@/components/ImageUploadButton.vue';
import type { UploadedImage } from '@/components/ImageUploadButton.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { toDateInputValue } from '@/lib/formatDateInput';
import { generateBookListMarkdown } from '@/lib/recapBookList';
import { cn } from '@/lib/utils';
import type {
    Post,
    PostCategory,
    PostProjectOption,
    PostType,
    RecapBookSummary,
} from '@/types';

const props = withDefaults(
    defineProps<{
        post?: Post;
        errors: Partial<Record<string, string>>;
        books?: RecapBookSummary[];
        projects?: PostProjectOption[];
    }>(),
    {
        books: () => [],
        projects: () => [],
    },
);

const typeOptions: {
    value: PostType;
    label: string;
    description: string;
    icon: typeof NotebookPen;
}[] = [
    {
        value: 'post',
        label: 'Post',
        description: 'A standard blog post',
        icon: NotebookPen,
    },
    {
        value: 'recap',
        label: 'Reading Recap',
        description: 'What you read over a period',
        icon: BookOpen,
    },
    {
        value: 'garden_update',
        label: 'Garden Update',
        description: "What's growing right now",
        icon: Sprout,
    },
    {
        value: 'project_update',
        label: 'Project Update',
        description: 'News about a project',
        icon: FolderGit2,
    },
];

const type = ref<PostType>(props.post?.type ?? 'post');
const category = ref<PostCategory>(props.post?.category ?? 'general');
const projectId = ref<string | undefined>(props.post?.project_id?.toString());
const startsAt = ref(toDateInputValue(props.post?.starts_at));
const endsAt = ref(toDateInputValue(props.post?.ends_at));
const body = ref(props.post?.body ?? '');

const featuredImageId = ref<number | null>(
    props.post?.featured_image_id ?? null,
);
const featuredImagePreviewUrl = ref<string | null>(
    props.post?.featured_image?.url ?? null,
);

const editorRef = useTemplateRef<ExposeParam>('editor');

const isPost = computed(() => type.value === 'post');
const isRecap = computed(() => type.value === 'recap');
const isProjectUpdate = computed(() => type.value === 'project_update');

watch(type, (newType) => {
    if (newType !== 'post') {
        category.value = 'general';
    }

    if (newType !== 'project_update') {
        projectId.value = undefined;
    }

    if (newType !== 'recap') {
        startsAt.value = '';
        endsAt.value = '';
    }
});

function onFeaturedImageUploaded(image: UploadedImage) {
    featuredImageId.value = image.id;
    featuredImagePreviewUrl.value = image.url;
}

function removeFeaturedImage() {
    featuredImageId.value = null;
    featuredImagePreviewUrl.value = null;
}

function onBodyImageUploaded(image: UploadedImage) {
    const markdown = `![](${image.path})`;

    if (!editorRef.value) {
        body.value = body.value ? `${body.value}\n\n${markdown}\n` : markdown;

        return;
    }

    editorRef.value.insert(() => ({ targetValue: markdown, select: false }));
}

function generate() {
    if (!startsAt.value || !endsAt.value) {
        return;
    }

    const list = generateBookListMarkdown(
        props.books,
        startsAt.value,
        endsAt.value,
    );
    body.value = body.value ? `${list}${body.value}` : list;
}
</script>

<template>
    <div class="grid gap-2">
        <Label>Type</Label>
        <input type="hidden" name="type" :value="type" />
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <button
                v-for="option in typeOptions"
                :key="option.value"
                type="button"
                :aria-pressed="type === option.value"
                :class="
                    cn(
                        'flex flex-col items-start gap-1.5 rounded-lg border p-3 text-left transition-colors hover:bg-accent',
                        type === option.value
                            ? 'border-primary bg-accent'
                            : 'border-border',
                    )
                "
                @click="type = option.value"
            >
                <component
                    :is="option.icon"
                    class="h-5 w-5 text-muted-foreground"
                />
                <span class="text-sm font-medium">{{ option.label }}</span>
                <span class="text-xs text-muted-foreground">{{
                    option.description
                }}</span>
            </button>
        </div>
        <InputError :message="errors.type" />
    </div>

    <div class="grid gap-2">
        <Label for="title">Title</Label>
        <Input
            id="title"
            name="title"
            :default-value="post?.title"
            required
            placeholder="Post title"
        />
        <InputError :message="errors.title" />
    </div>

    <div class="grid gap-2">
        <Label>Featured image (optional)</Label>
        <input
            type="hidden"
            name="featured_image_id"
            :value="featuredImageId ?? ''"
        />
        <img
            v-if="featuredImagePreviewUrl"
            :src="featuredImagePreviewUrl"
            alt=""
            class="h-32 w-auto rounded-md border object-cover"
        />
        <div class="flex items-center gap-2">
            <ImageUploadButton
                label="Upload featured image"
                @uploaded="onFeaturedImageUploaded"
            />
            <Button
                v-if="featuredImageId"
                type="button"
                variant="ghost"
                size="sm"
                @click="removeFeaturedImage"
            >
                Remove
            </Button>
        </div>
        <InputError :message="errors.featured_image_id" />
    </div>

    <div v-if="isPost" class="grid gap-2">
        <Label for="category">Category</Label>
        <Select v-model="category" name="category">
            <SelectTrigger id="category" class="w-full">
                <SelectValue placeholder="Select a category" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="general">General</SelectItem>
                <SelectItem value="life">Life</SelectItem>
                <SelectItem value="tech">Tech</SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.category" />
    </div>

    <div v-if="isProjectUpdate" class="grid gap-2">
        <Label for="project_id">Project</Label>
        <Select v-model="projectId" name="project_id">
            <SelectTrigger id="project_id" class="w-full">
                <SelectValue placeholder="Select a project" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem
                    v-for="project in projects"
                    :key="project.id"
                    :value="project.id.toString()"
                >
                    {{ project.title }}
                </SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.project_id" />
    </div>

    <template v-if="isRecap">
        <div class="grid grid-cols-2 items-start gap-4">
            <div class="grid gap-2">
                <Label for="starts_at">Starts</Label>
                <Input
                    id="starts_at"
                    v-model="startsAt"
                    type="date"
                    name="starts_at"
                    required
                />
                <InputError :message="errors.starts_at" />
            </div>

            <div class="grid gap-2">
                <Label for="ends_at">Ends</Label>
                <Input
                    id="ends_at"
                    v-model="endsAt"
                    type="date"
                    name="ends_at"
                    required
                />
                <InputError :message="errors.ends_at" />
            </div>
        </div>

        <div class="flex items-center justify-between gap-4">
            <p class="text-xs text-muted-foreground">
                Adds a markdown list of books finished in the date range above
                to the top of the body.
            </p>
            <Button
                type="button"
                variant="outline"
                size="sm"
                class="shrink-0"
                @click="generate"
            >
                Generate book list
            </Button>
        </div>
    </template>

    <div class="grid gap-2">
        <div class="flex items-center justify-between">
            <Label>Body (Markdown)</Label>
            <ImageUploadButton
                label="Insert image"
                @uploaded="onBodyImageUploaded"
            />
        </div>
        <input type="hidden" name="body" :value="body" />
        <MdEditor
            ref="editor"
            v-model="body"
            language="en-US"
            theme="dark"
            :toolbars-exclude="[
                'image',
                'github',
                'save',
                'mermaid',
                'katex',
                'htmlPreview',
            ]"
            :no-mermaid="true"
            :no-katex="true"
            :no-echarts="true"
            style="height: 520px"
        />
        <InputError :message="errors.body" />
    </div>
</template>
