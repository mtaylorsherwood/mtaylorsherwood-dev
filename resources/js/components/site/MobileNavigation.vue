<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    DialogClose,
    DialogContent,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
    DialogTrigger,
} from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { ref } from 'vue';
import { ChevronDownIcon, CloseIcon } from '@/components/site/icons';
import { cn } from '@/lib/utils';
import type { NavItem } from '@/types';

const props = defineProps<{
    items: NavItem[];
    class?: HTMLAttributes['class'];
}>();

const open = ref(false);
</script>

<template>
    <DialogRoot v-model:open="open">
        <DialogTrigger
            :class="
                cn(
                    'group flex items-center rounded-full bg-espresso-950/90 px-4 py-2 text-sm font-medium text-ash-grey-200 shadow-lg ring-1 ring-white/10 backdrop-blur-sm hover:ring-white/20',
                    props.class,
                )
            "
        >
            Menu
            <ChevronDownIcon
                class="ml-3 h-auto w-2 stroke-ash-grey-500 group-hover:stroke-ash-grey-400"
            />
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay
                class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs duration-150 data-[state=closed]:opacity-0 data-[state=closed]:ease-in data-[state=open]:ease-out"
            />
            <DialogContent
                class="fixed inset-x-4 top-8 z-50 origin-top rounded-3xl bg-espresso-950 p-8 ring-1 ring-ash-grey-800 duration-150 data-[state=closed]:scale-95 data-[state=closed]:opacity-0 data-[state=closed]:ease-in data-[state=open]:ease-out"
            >
                <div class="flex flex-row-reverse items-center justify-between">
                    <DialogClose aria-label="Close menu" class="-m-1 p-1">
                        <CloseIcon class="h-6 w-6 text-ash-grey-400" />
                    </DialogClose>
                    <DialogTitle class="text-sm font-medium text-ash-grey-400">
                        Navigation
                    </DialogTitle>
                </div>
                <nav class="mt-6">
                    <ul
                        class="-my-2 divide-y divide-ash-grey-100/5 text-base text-ash-grey-300"
                    >
                        <li v-for="item in items" :key="item.title">
                            <Link
                                :href="item.href"
                                class="block py-2"
                                @click="open = false"
                            >
                                {{ item.title }}
                            </Link>
                        </li>
                    </ul>
                </nav>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
