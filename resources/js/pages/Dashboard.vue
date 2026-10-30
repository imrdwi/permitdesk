<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CircleCheck, CircleDashed } from '@lucide/vue';
import ApplicationController from '@/actions/App/Http/Controllers/Applicant/ApplicationController';
import OfficeApplicationController from '@/actions/App/Http/Controllers/Office/ApplicationController';
import PageHeader from '@/components/admin/PageHeader.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import { dashboard } from '@/routes';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] },
});

defineProps<{
    mode: 'applicant' | 'office';
    checklist?: { label: string; done: boolean; href: string }[];
    applications?: {
        id: number;
        practice_name: string;
        status: string;
        expires_on: string | null;
        is_temporary: boolean;
    }[];
    stats?: { status: string; label: string; value: number }[];
    expiring?: {
        id: number;
        practice_name: string;
        applicant: string | null;
        expires_on: string;
    }[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <template v-if="mode === 'applicant'">
            <PageHeader
                title="Your practice permits"
                description="Complete the checklist, then apply for each location where you practise."
            >
                <Button as-child>
                    <Link :href="ApplicationController.create()"
                        >New application</Link
                    >
                </Button>
            </PageHeader>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-3 font-medium">Before you apply</h2>
                <ul class="space-y-2">
                    <li
                        v-for="item in checklist"
                        :key="item.label"
                        class="flex items-center gap-2 text-sm"
                    >
                        <CircleCheck
                            v-if="item.done"
                            class="size-4 text-emerald-600"
                        />
                        <CircleDashed
                            v-else
                            class="size-4 text-muted-foreground"
                        />
                        <Link :href="item.href" class="hover:underline">{{
                            item.label
                        }}</Link>
                        <span v-if="!item.done" class="text-muted-foreground"
                            >· still to do</span
                        >
                    </li>
                </ul>
            </section>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-3 font-medium">Recent applications</h2>
                <p
                    v-if="!applications?.length"
                    class="text-sm text-muted-foreground"
                >
                    You have not applied yet.
                </p>
                <ul class="divide-y text-sm">
                    <li
                        v-for="application in applications"
                        :key="application.id"
                        class="flex items-center justify-between gap-4 py-2"
                    >
                        <Link
                            :href="ApplicationController.show(application.id)"
                            class="hover:underline"
                        >
                            {{ application.practice_name }}
                            <span
                                v-if="application.is_temporary"
                                class="text-muted-foreground"
                                >· temporary</span
                            >
                        </Link>
                        <span class="flex items-center gap-3">
                            <span
                                v-if="application.expires_on"
                                class="text-muted-foreground"
                                >until
                                {{ formatDate(application.expires_on) }}</span
                            >
                            <StatusBadge :status="application.status" />
                        </span>
                    </li>
                </ul>
            </section>
        </template>

        <template v-else>
            <PageHeader
                title="Licensing office"
                description="Applications waiting on you, and permits about to lapse."
            />

            <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <Link
                    v-for="stat in stats"
                    :key="stat.status"
                    :href="
                        OfficeApplicationController.index({
                            query: { status: stat.status },
                        })
                    "
                    class="rounded-xl border border-sidebar-border/70 p-4 hover:bg-muted/40 dark:border-sidebar-border"
                >
                    <p class="text-sm text-muted-foreground">
                        {{ stat.label }}
                    </p>
                    <p class="mt-1 text-3xl font-semibold tabular-nums">
                        {{ stat.value }}
                    </p>
                </Link>
            </div>

            <section
                class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
            >
                <h2 class="mb-1 font-medium">Expiring in the next 30 days</h2>
                <p class="mb-3 text-xs text-muted-foreground">
                    Holders are emailed a reminder automatically.
                </p>
                <p
                    v-if="!expiring?.length"
                    class="text-sm text-muted-foreground"
                >
                    No permits expire in the next 30 days.
                </p>
                <ul class="divide-y text-sm">
                    <li
                        v-for="permit in expiring"
                        :key="permit.id"
                        class="flex justify-between gap-4 py-2"
                    >
                        <Link
                            :href="OfficeApplicationController.show(permit.id)"
                            class="hover:underline"
                        >
                            {{ permit.applicant }} · {{ permit.practice_name }}
                        </Link>
                        <span class="text-muted-foreground">{{
                            formatDate(permit.expires_on)
                        }}</span>
                    </li>
                </ul>
            </section>
        </template>
    </div>
</template>
