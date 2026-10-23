<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { todayDateInputValue } from '@/lib/formatDateInput';
import type { RouteFormDefinition } from '@/wayfinder';

defineProps<{
    form: RouteFormDefinition<'post'>;
    field: string;
    triggerLabel: string;
    title: string;
    description: string;
    submitLabel: string;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">{{ triggerLabel }}</Button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="form"
                class="space-y-6"
                v-slot="{ errors, processing }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label :for="field">Date</Label>
                    <Input
                        :id="field"
                        type="date"
                        :name="field"
                        :default-value="todayDateInputValue()"
                        required
                    />
                    <InputError :message="errors[field]" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">Cancel</Button>
                    </DialogClose>
                    <Button type="submit" :disabled="processing">{{
                        submitLabel
                    }}</Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
