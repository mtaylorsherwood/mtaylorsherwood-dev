<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import projectRoutes from '@/routes/dashboard/projects';
import type { Project } from '@/types';

defineProps<{
    projects: Project[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Projects', href: projectRoutes.index() },
        ],
    },
});

function statusLabel(project: Project): string {
    return project.status === 'ongoing' ? 'Ongoing' : 'Dormant';
}

function destroy(project: Project) {
    if (!confirm(`Delete "${project.title}"? This can't be undone.`)) {
        return;
    }

    router.delete(projectRoutes.destroy(project.id).url);
}
</script>

<template>
    <Head title="Projects" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                title="Projects"
                description="Things you've built, and their current status."
            />
            <Button as-child>
                <Link :href="projectRoutes.create()">New project</Link>
            </Button>
        </div>

        <p v-if="projects.length === 0" class="text-sm text-muted-foreground">
            You haven’t added any projects yet.
        </p>

        <div v-else class="divide-y divide-border rounded-lg border">
            <div
                v-for="project in projects"
                :key="project.id"
                class="flex flex-wrap items-center justify-between gap-4 p-4"
            >
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="truncate font-medium">{{ project.title }}</p>
                        <Badge
                            :variant="
                                project.status === 'ongoing'
                                    ? 'default'
                                    : 'secondary'
                            "
                        >
                            {{ statusLabel(project) }}
                        </Badge>
                    </div>
                    <p class="mt-1 truncate text-sm text-muted-foreground">
                        {{ project.blurb }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button as-child variant="outline" size="sm">
                        <Link :href="projectRoutes.edit(project.id)">
                            Edit
                        </Link>
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        @click="destroy(project)"
                        >Delete</Button
                    >
                </div>
            </div>
        </div>
    </div>
</template>
