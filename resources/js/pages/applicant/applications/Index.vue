<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ApplicationController from '@/actions/App/Http/Controllers/Applicant/ApplicationController';
import DocumentController from '@/actions/App/Http/Controllers/Applicant/DocumentController';
import ProfileController from '@/actions/App/Http/Controllers/Applicant/ProfileController';
import AdminTable from '@/components/admin/AdminTable.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My applications', href: ApplicationController.index() },
        ],
    },
});

defineProps<{
    applications: {
        id: number;
        practice_name: string;
        is_temporary: boolean;
        status: string;
        permit_number: string | null;
        submitted_at: string;
        expires_on: string | null;
    }[];
    readiness: {
        ready: boolean;
        hasProfile: boolean;
        missingDocuments: string[];
        hasActivePrimary: boolean;
    };
}>();
</script>

<template>
    <Head title="My applications" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="My applications"
            description="One primary practice location, plus any temporary ones."
        >
            <Button v-if="readiness.ready" as-child>
                <Link :href="ApplicationController.create()"
                    >New application</Link
                >
            </Button>
        </PageHeader>

        <Alert v-if="!readiness.ready">
            <AlertTitle>Before you can apply</AlertTitle>
            <AlertDescription>
                <ul class="list-disc pl-5">
                    <li v-if="!readiness.hasProfile">
                        Fill in
                        <Link :href="ProfileController.edit()" class="underline"
                            >your details</Link
                        >.
                    </li>
                    <li v-if="readiness.missingDocuments.length">
                        Upload
                        <Link
                            :href="DocumentController.index()"
                            class="underline"
                            >your documents</Link
                        >: {{ readiness.missingDocuments.join(', ') }}.
                    </li>
                </ul>
            </AlertDescription>
        </Alert>

        <AdminTable
            :empty="applications.length === 0"
            empty-text="No applications yet."
        >
            <template #head>
                <tr>
                    <th>Practice location</th>
                    <th>Type</th>
                    <th>Submitted</th>
                    <th>Permit number</th>
                    <th>Valid until</th>
                    <th>Status</th>
                </tr>
            </template>
            <tr v-for="application in applications" :key="application.id">
                <td>
                    <Link
                        :href="ApplicationController.show(application.id)"
                        class="font-medium hover:underline"
                        >{{ application.practice_name }}</Link
                    >
                </td>
                <td>
                    {{ application.is_temporary ? 'Temporary' : 'Primary' }}
                </td>
                <td>{{ formatDate(application.submitted_at) }}</td>
                <td class="font-mono text-xs">
                    {{ application.permit_number ?? '—' }}
                </td>
                <td>{{ formatDate(application.expires_on) }}</td>
                <td><StatusBadge :status="application.status" /></td>
            </tr>
        </AdminTable>
    </div>
</template>
