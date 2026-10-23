<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-spicy-paprika-300">
            {{ message }}
        </p>
    </div>
</template>
