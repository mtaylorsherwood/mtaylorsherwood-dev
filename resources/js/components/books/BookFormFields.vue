<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
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
import { toDateInputValue } from '@/lib/formatDateInput';
import type { Book } from '@/types';

withDefaults(
    defineProps<{
        book?: Book;
        errors: Partial<Record<string, string>>;
        genres?: string[];
        series?: string[];
    }>(),
    {
        genres: () => [],
        series: () => [],
    },
);
</script>

<template>
    <div class="grid grid-cols-2 gap-4">
        <div class="grid gap-2">
            <Label for="title">Title</Label>
            <Input
                id="title"
                name="title"
                :default-value="book?.title"
                required
                placeholder="Book title"
            />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="author">Author</Label>
            <Input
                id="author"
                name="author"
                :default-value="book?.author"
                required
                placeholder="Author name"
            />
            <InputError :message="errors.author" />
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="grid gap-2">
            <Label for="category">Category</Label>
            <Select name="category" :default-value="book?.category">
                <SelectTrigger id="category" class="w-full">
                    <SelectValue placeholder="Select a category" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="fiction">Fiction</SelectItem>
                    <SelectItem value="non_fiction">Non-Fiction</SelectItem>
                </SelectContent>
            </Select>
            <InputError :message="errors.category" />
        </div>

        <div class="grid gap-2">
            <Label for="genre">Genre</Label>
            <Input
                id="genre"
                name="genre"
                list="genre-options"
                :default-value="book?.genre"
                required
                placeholder="Fantasy"
                autocomplete="off"
            />
            <datalist id="genre-options">
                <option v-for="genre in genres" :key="genre" :value="genre" />
            </datalist>
            <InputError :message="errors.genre" />
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="grid gap-2">
            <Label for="pages">Pages</Label>
            <Input
                id="pages"
                type="number"
                min="1"
                name="pages"
                :default-value="book?.pages"
                required
            />
            <InputError :message="errors.pages" />
        </div>

        <div class="grid gap-2">
            <Label for="series">Series (optional)</Label>
            <Input
                id="series"
                name="series"
                list="series-options"
                :default-value="book?.series ?? ''"
                placeholder="The Wheel of Time"
                autocomplete="off"
            />
            <datalist id="series-options">
                <option v-for="item in series" :key="item" :value="item" />
            </datalist>
            <InputError :message="errors.series" />
        </div>
    </div>

    <div class="grid grid-cols-2 items-start gap-4">
        <div class="grid gap-2">
            <Label for="started_at">Started</Label>
            <Input
                id="started_at"
                type="date"
                name="started_at"
                :default-value="toDateInputValue(book?.started_at)"
            />
            <p class="text-xs text-muted-foreground">
                Leave blank to add this to your to-be-read list.
            </p>
            <InputError :message="errors.started_at" />
        </div>

        <div class="grid gap-2">
            <Label for="finished_at">Finished</Label>
            <Input
                id="finished_at"
                type="date"
                name="finished_at"
                :default-value="toDateInputValue(book?.finished_at)"
            />
            <p class="text-xs text-muted-foreground">
                Leave blank if you’re still reading this one.
            </p>
            <InputError :message="errors.finished_at" />
        </div>
    </div>

    <div class="flex items-center gap-3">
        <Checkbox
            id="is_reread"
            name="is_reread"
            :default-value="book?.is_reread ?? false"
        />
        <Label for="is_reread">This is a re-read</Label>
        <InputError :message="errors.is_reread" />
    </div>

    <div class="grid gap-2">
        <Label for="review">Review (optional)</Label>
        <Textarea
            id="review"
            name="review"
            :default-value="book?.review ?? ''"
            rows="4"
        />
        <InputError :message="errors.review" />
    </div>
</template>
