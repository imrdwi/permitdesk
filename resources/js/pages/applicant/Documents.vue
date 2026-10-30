<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { CircleCheck, CircleDashed } from '@lucide/vue';
import DocumentController from '@/actions/App/Http/Controllers/Applicant/DocumentController';
import DocumentDownloadController from '@/actions/App/Http/Controllers/DocumentDownloadController';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDateTime } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My documents', href: DocumentController.index() },
        ],
    },
});

defineProps<{
    documents: {
        type: string;
        label: string;
        uploaded: {
            id: number;
            original_name: string;
            size: number;
            uploaded_at: string;
        } | null;
    }[];
}>();

const kilobytes = (bytes: number) => `${Math.round(bytes / 1024)} KB`;
</script>

<template>
    <Head title="My documents" />

    <div class="flex max-w-3xl flex-1 flex-col gap-6 p-4">
        <PageHeader
            title="My documents"
            description="Upload each document once; it is attached to all your applications. PDF, JPG or PNG, up to 5 MB."
        />

        <ul
            class="divide-y rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <li
                v-for="document in documents"
                :key="document.type"
                class="flex flex-col gap-3 p-4"
            >
                <div class="flex min-w-0 items-start gap-2">
                    <CircleCheck
                        v-if="document.uploaded"
                        class="mt-0.5 size-4 shrink-0 text-emerald-600"
                    />
                    <CircleDashed
                        v-else
                        class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                    />
                    <div class="min-w-0">
                        <p class="font-medium">{{ document.label }}</p>
                        <p
                            v-if="document.uploaded"
                            class="truncate text-xs text-muted-foreground"
                        >
                            <a
                                :href="
                                    DocumentDownloadController.url(
                                        document.uploaded.id,
                                    )
                                "
                                target="_blank"
                                class="underline"
                                >{{ document.uploaded.original_name }}</a
                            >
                            · {{ kilobytes(document.uploaded.size) }} ·
                            {{ formatDateTime(document.uploaded.uploaded_at) }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">
                            Not uploaded
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 pl-6">
                    <Form
                        v-bind="DocumentController.store.form()"
                        class="flex items-center gap-2"
                        v-slot="{ errors, processing }"
                    >
                        <input
                            type="hidden"
                            name="type"
                            :value="document.type"
                        />
                        <Input
                            name="file"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-56"
                            :aria-label="`File for ${document.label}`"
                            required
                        />
                        <Button
                            size="sm"
                            variant="outline"
                            :disabled="processing"
                            >{{
                                document.uploaded ? 'Replace' : 'Upload'
                            }}</Button
                        >
                        <InputError :message="errors.file" />
                    </Form>
                    <DeleteButton
                        v-if="document.uploaded"
                        :action="
                            DocumentController.destroy.form(
                                document.uploaded.id,
                            )
                        "
                        label="Remove"
                        :description="`Remove your ${document.label}?`"
                    />
                </div>
            </li>
        </ul>
    </div>
</template>
