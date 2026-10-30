<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import ReportController from '@/actions/App/Http/Controllers/Office/ReportController';
import AdminTable from '@/components/admin/AdminTable.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Monthly report', href: ReportController.index() },
        ],
    },
});

defineProps<{
    month: string;
    monthLabel: string;
    applications: {
        id: number;
        applicant: string | null;
        profession: string | null;
        practice_name: string;
        is_temporary: boolean;
        status: string;
        permit_number: string | null;
        submitted_at: string;
        decided_at: string | null;
    }[];
    totals: Record<string, number>;
}>();

function printReport() {
    window.print();
}

function changeMonth(event: Event) {
    router.get(
        ReportController.index().url,
        { month: (event.target as HTMLInputElement).value },
        { preserveState: true },
    );
}
</script>

<template>
    <Head :title="`Report ${monthLabel}`" />

    <div class="flex flex-1 flex-col gap-4 p-4 print:p-0">
        <PageHeader
            :title="`Permit report · ${monthLabel}`"
            description="Applications submitted or decided in the month."
        >
            <Input
                type="month"
                :default-value="month"
                class="w-44 print:hidden"
                aria-label="Report month"
                @change="changeMonth"
            />
            <Button variant="outline" class="print:hidden" @click="printReport"
                >Print</Button
            >
        </PageHeader>

        <dl class="flex flex-wrap gap-6 text-sm">
            <div v-for="(count, label) in totals" :key="label">
                <dt class="text-muted-foreground">{{ label }}</dt>
                <dd class="text-xl font-semibold tabular-nums">{{ count }}</dd>
            </div>
        </dl>

        <AdminTable
            :empty="applications.length === 0"
            empty-text="Nothing happened this month."
        >
            <template #head>
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Profession</th>
                    <th>Practice location</th>
                    <th>Submitted</th>
                    <th>Decided</th>
                    <th>Status</th>
                    <th>Permit</th>
                </tr>
            </template>
            <tr v-for="(row, index) in applications" :key="row.id">
                <td class="tabular-nums">{{ index + 1 }}</td>
                <td>{{ row.applicant }}</td>
                <td>{{ row.profession }}</td>
                <td>
                    {{ row.practice_name
                    }}<span
                        v-if="row.is_temporary"
                        class="text-muted-foreground"
                    >
                        (temp.)</span
                    >
                </td>
                <td>{{ formatDate(row.submitted_at) }}</td>
                <td>{{ formatDate(row.decided_at) }}</td>
                <td>{{ row.status }}</td>
                <td class="font-mono text-xs">
                    {{ row.permit_number ?? '—' }}
                </td>
            </tr>
        </AdminTable>
    </div>
</template>
