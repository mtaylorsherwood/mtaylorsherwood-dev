<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    books: number;
    pages: number;
}>();

const formattedPages = computed(() =>
    new Intl.NumberFormat('en-US').format(props.pages),
);
</script>

<template>
    <span>
        {{ books }} {{ books === 1 ? 'book' : 'books' }} · {{ formattedPages }}
        {{ pages === 1 ? 'page' : 'pages' }}
    </span>
</template>
