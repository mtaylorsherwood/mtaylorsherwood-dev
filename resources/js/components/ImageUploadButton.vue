<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { useTemplateRef } from 'vue';
import ImageController from '@/actions/App/Http/Controllers/ImageController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';

export type UploadedImage = {
    id: number;
    path: string;
    width: number;
    height: number;
    url: string;
};

withDefaults(
    defineProps<{
        label?: string;
    }>(),
    {
        label: 'Upload image',
    },
);

const emit = defineEmits<{
    uploaded: [image: UploadedImage];
}>();

const fileInput = useTemplateRef('fileInput');
const http = useHttp<{ image: File | null }>({ image: null });

function pick() {
    fileInput.value?.click();
}

async function onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    http.image = file;

    try {
        const image = await http.post(ImageController.store().url);
        emit('uploaded', image as UploadedImage);
    } catch {
        // Validation/network errors surface via http.errors below.
    } finally {
        input.value = '';
    }
}
</script>

<template>
    <div>
        <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/webp,image/gif"
            class="hidden"
            @change="onFileSelected"
        />
        <Button
            type="button"
            variant="outline"
            size="sm"
            :disabled="http.processing"
            @click="pick"
        >
            {{ http.processing ? 'Uploading…' : label }}
        </Button>
        <InputError :message="http.errors.image" />
    </div>
</template>
