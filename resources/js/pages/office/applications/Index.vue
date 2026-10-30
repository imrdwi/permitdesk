<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationController from '@/actions/App/Http/Controllers/Office/ApplicationController';
import AdminTable from '@/components/admin/AdminTable.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Pagination from '@/components/admin/Pagination.vue';
import SearchFilter from '@/components/admin/SearchFilter.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { formatDate } from '@/lib/format';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Applications', href: ApplicationController.index() },
        ],
    },
});

type Row = {
    id: number;
    applicant: string | null;
    profession: string | null;
    practice_name: string;
    is_temporary: boolean;
    status: string;
    permit_number: string | null;
    submitted_at: string;
    expires_on: string | null;
};

const props = defineProps<{
    applications: Paginated<Row>;
    filters: { search?: string; status?: string; profession?: string };
    statuses: { value: string; label: string }[];
    professions: { value: string; label: string }[];
}>();

function filter(key: 'status' | 'profession', event: Event) {
    const value = (event.target as HTMLSelectElement).value;
    router.get(
        ApplicationController.index().url,
        { ...props.filters, [key]: value || undefined },
        { preserveState: true },
    );
}
</script>

<template>
    <Head title="Applications" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <PageHeader
            title="Applications"
            description="Pending and in-review applications are listed first."
        />

        <div class="flex flex-wrap gap-2">
            <SearchFilter
                :filters="filters"
                placeholder="Search applicant, location or permit number"
            />
            <NativeSelect
                class="w-48"
                aria-label="Filter by status"
                @change="filter('status', $event)"
            >
                <option value="">All statuses</option>
                <option
                    v-for="status in statuses"
                    :key="status.value"
                    :value="status.value"
                    :selected="status.value === filters.status"
                >
                    {{ status.label }}
                </option>
                <option
                    value="expiring"
                    :selected="filters.status === 'expiring'"
                >
                    Expiring in 30 days
                </option>
            </NativeSelect>
            <NativeSelect
                class="w-48"
                aria-label="Filter by profession"
                @change="filter('profession', $event)"
            >
                <option value="">All professions</option>
                <option
                    v-for="profession in professions"
                    :key="profession.value"
                    :value="profession.value"
                    :selected="profession.value === filters.profession"
                >
                    {{ profession.label }}
                </option>
            </NativeSelect>
        </div>

        <AdminTable
            :empty="applications.data.length === 0"
            empty-text="No applications match these filters."
        >
            <template #head>
                <tr>
                    <th>Applicant</th>
                    <th>Profession</th>
                    <th>Practice location</th>
                    <th>Submitted</th>
                    <th>Permit / valid until</th>
                    <th>Status</th>
                </tr>
            </template>
            <tr v-for="row in applications.data" :key="row.id">
                <td>
                    <Link
                        :href="ApplicationController.show(row.id)"
                        class="font-medium hover:underline"
                        >{{ row.applicant }}</Link
                    >
                </td>
                <td>{{ row.profession }}</td>
                <td>
                    {{ row.practice_name }}
                    <span
                        v-if="row.is_temporary"
                        class="text-xs text-muted-foreground"
                        >· temporary</span
                    >
                </td>
                <td>{{ formatDate(row.submitted_at) }}</td>
                <td>
                    <template v-if="row.permit_number">
                        <span class="font-mono text-xs">{{
                            row.permit_number
                        }}</span>
                        <span class="block text-xs text-muted-foreground"
                            >until {{ formatDate(row.expires_on) }}</span
                        >
                    </template>
                    <template v-else>—</template>
                </td>
                <td><StatusBadge :status="row.status" /></td>
            </tr>
        </AdminTable>

        <Pagination :page="applications" />
    </div>
</template>
