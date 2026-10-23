<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ProjectController from '@/actions/App/Http/Controllers/ProjectController';
import Heading from '@/components/Heading.vue';
import ProjectFormFields from '@/components/projects/ProjectFormFields.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import projectRoutes from '@/routes/dashboard/projects';
import type { ProjectWithImages } from '@/types';

defineProps<{
    project: ProjectWithImages;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Projects', href: projectRoutes.index() },
        ],
    },
});
</script>

<template>
    <Head title="Edit project" />

    <div class="max-w-2xl p-4">
        <Heading title="Edit project" :description="project.title" />

        <Form
            v-bind="ProjectController.update.form(project.id)"
            class="mt-6 space-y-6"
            v-slot="{ errors, processing }"
        >
            <ProjectFormFields :project="project" :errors="errors" />

            <div class="flex items-center gap-4">
                <Button :disabled="processing">Save</Button>
                <Link
                    :href="projectRoutes.index()"
                    class="text-sm text-muted-foreground hover:text-foreground"
                >
                    Cancel
                </Link>
            </div>
        </Form>
    </div>
</template>
