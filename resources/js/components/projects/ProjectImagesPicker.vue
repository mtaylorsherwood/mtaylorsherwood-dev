<script setup lang="ts">
import { ref } from 'vue';
import ImageUploadButton from '@/components/ImageUploadButton.vue';
import type { UploadedImage } from '@/components/ImageUploadButton.vue';
import { Button } from '@/components/ui/button';
import type { ProjectImage } from '@/types';

const props = withDefaults(
    defineProps<{
        images?: ProjectImage[];
    }>(),
    {
        images: () => [],
    },
);

const images = ref<ProjectImage[]>([...props.images]);

function onUploaded(image: UploadedImage) {
    if (images.value.length >= 5) {
        return;
    }

    images.value.push({ id: image.id, url: image.url });
}

function remove(index: number) {
    images.value.splice(index, 1);
}
</script>

<template>
    <div class="grid gap-3">
        <input
            v-for="image in images"
            :key="image.id"
            type="hidden"
            name="image_ids[]"
            :value="image.id"
        />

        <div v-if="images.length > 0" class="grid grid-cols-5 gap-3">
            <div
                v-for="(image, index) in images"
                :key="image.id"
                class="relative aspect-square overflow-hidden rounded-md border"
            >
                <img
                    :src="image.url"
                    alt=""
                    class="h-full w-full object-cover"
                />
                <Button
                    type="button"
                    variant="destructive"
                    size="sm"
                    class="absolute top-1 right-1 h-6 px-2"
                    @click="remove(index)"
                >
                    Remove
                </Button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <ImageUploadButton
                v-if="images.length < 5"
                label="Upload image"
                @uploaded="onUploaded"
            />
            <p class="text-xs text-muted-foreground">
                {{ images.length }} / 5 images
            </p>
        </div>
    </div>
</template>
