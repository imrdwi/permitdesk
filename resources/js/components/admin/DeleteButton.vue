<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import type { RouteFormDefinition } from '@/wayfinder';
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

withDefaults(
    defineProps<{
        action: RouteFormDefinition<'post'>;
        title?: string;
        description?: string;
        label?: string;
    }>(),
    {
        title: 'Are you sure?',
        description: 'This cannot be undone from here.',
        label: 'Delete',
    },
);
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button
                variant="ghost"
                size="sm"
                class="text-destructive hover:text-destructive"
            >
                {{ label }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <Form v-bind="action" v-slot="{ processing }" class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" type="button"
                            >Cancel</Button
                        >
                    </DialogClose>
                    <Button
                        variant="destructive"
                        type="submit"
                        :disabled="processing"
                    >
                        {{ label }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
