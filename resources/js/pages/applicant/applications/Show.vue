<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationController from '@/actions/App/Http/Controllers/Applicant/ApplicationController';
import PermitPrintController from '@/actions/App/Http/Controllers/PermitPrintController';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import PermitTimeline from '@/components/admin/PermitTimeline.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { formatDate, formatDateTime } from '@/lib/format';

defineProps<{
    application: {
        id: number;
        is_temporary: boolean;
        practice_name: string;
        practice_address: string;
        practice_email: string | null;
        practice_phone: string | null;
        status: string;
        permit_number: string | null;
        rejection_reason: string | null;
        expires_on: string | null;
        submitted_at: string;
        events: {
            id: number;
            to_status: string;
            note: string | null;
            created_at: string;
            user: { name: string } | null;
        }[];
    };
    canEdit: boolean;
    canPrint: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My applications', href: ApplicationController.index() },
            { title: 'Application', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="application.practice_name" />

    <div class="flex max-w-3xl flex-1 flex-col gap-6 p-4">
        <PageHeader
            :title="application.practice_name"
            :description="
                application.is_temporary
                    ? 'Temporary practice location'
                    : 'Primary practice location'
            "
        >
            <StatusBadge :status="application.status" />
            <Button v-if="canPrint" as-child>
                <a
                    :href="PermitPrintController.url(application.id)"
                    target="_blank"
                    >Print permit</a
                >
            </Button>
            <template v-if="canEdit">
                <Button variant="outline" as-child>
                    <Link :href="ApplicationController.edit(application.id)"
                        >Edit</Link
                    >
                </Button>
                <DeleteButton
                    :action="ApplicationController.destroy.form(application.id)"
                    label="Withdraw"
                    description="Withdraw this application? You can apply again later."
                />
            </template>
        </PageHeader>

        <Alert v-if="application.status === 'rejected'" variant="destructive">
            <AlertTitle>Not approved</AlertTitle>
            <AlertDescription>{{
                application.rejection_reason
            }}</AlertDescription>
        </Alert>

        <dl
            class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 rounded-xl border border-sidebar-border/70 p-4 text-sm dark:border-sidebar-border"
        >
            <dt class="text-muted-foreground">Address</dt>
            <dd class="whitespace-pre-line">
                {{ application.practice_address }}
            </dd>
            <dt class="text-muted-foreground">Contact</dt>
            <dd>
                {{
                    [application.practice_email, application.practice_phone]
                        .filter(Boolean)
                        .join(' · ') || '—'
                }}
            </dd>
            <dt class="text-muted-foreground">Submitted</dt>
            <dd>{{ formatDateTime(application.submitted_at) }}</dd>
            <template v-if="application.permit_number">
                <dt class="text-muted-foreground">Permit number</dt>
                <dd class="font-mono">{{ application.permit_number }}</dd>
                <dt class="text-muted-foreground">Valid until</dt>
                <dd>{{ formatDate(application.expires_on) }}</dd>
            </template>
        </dl>

        <PermitTimeline
            :events="
                application.events.map((event) => ({
                    id: event.id,
                    to_status: event.to_status,
                    note: event.note,
                    by: event.user?.name ?? 'System',
                    at: event.created_at,
                }))
            "
        />
    </div>
</template>
