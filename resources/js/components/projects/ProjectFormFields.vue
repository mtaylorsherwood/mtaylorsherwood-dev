<script setup lang="ts">
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import ProjectImagesPicker from '@/components/projects/ProjectImagesPicker.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { ProjectStatus, ProjectWithImages } from '@/types';

const props = defineProps<{
    project?: ProjectWithImages;
    errors: Partial<Record<string, string>>;
}>();

const status = ref<ProjectStatus>(props.project?.status ?? 'ongoing');
</script>

<template>
    <div class="grid gap-2">
        <Label for="title">Title</Label>
        <Input
            id="title"
            name="title"
            :default-value="project?.title"
            required
            placeholder="Project title"
        />
        <InputError :message="errors.title" />
    </div>

    <div class="grid gap-2">
        <Label for="status">Status</Label>
        <Select v-model="status" name="status">
            <SelectTrigger id="status" class="w-full">
                <SelectValue placeholder="Select a status" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="ongoing">Ongoing</SelectItem>
                <SelectItem value="dormant">Dormant</SelectItem>
            </SelectContent>
        </Select>
        <InputError :message="errors.status" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="grid gap-2">
            <Label for="website_url">Website URL (optional)</Label>
            <Input
                id="website_url"
                type="url"
                name="website_url"
                :default-value="project?.website_url ?? ''"
                placeholder="https://example.com"
            />
            <InputError :message="errors.website_url" />
        </div>

        <div class="grid gap-2">
            <Label for="github_url">GitHub URL (optional)</Label>
            <Input
                id="github_url"
                type="url"
                name="github_url"
                :default-value="project?.github_url ?? ''"
                placeholder="https://github.com/you/project"
            />
            <InputError :message="errors.github_url" />
        </div>
    </div>

    <div class="grid gap-2">
        <Label for="blurb">Blurb</Label>
        <Textarea
            id="blurb"
            name="blurb"
            :default-value="project?.blurb ?? ''"
            rows="3"
            placeholder="A short summary shown on the projects page."
            required
        />
        <InputError :message="errors.blurb" />
    </div>

    <div class="grid gap-2">
        <Label for="body">Description (Markdown)</Label>
        <Textarea
            id="body"
            name="body"
            :default-value="project?.body ?? ''"
            rows="16"
            class="font-mono text-sm"
            required
        />
        <InputError :message="errors.body" />
    </div>

    <div class="grid gap-2">
        <Label>Images (up to 5)</Label>
        <ProjectImagesPicker :images="project?.images" />
        <InputError :message="errors.image_ids" />
    </div>
</template>
