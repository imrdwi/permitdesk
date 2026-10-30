<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import DocumentDownloadController from '@/actions/App/Http/Controllers/DocumentDownloadController';
import ApplicationController from '@/actions/App/Http/Controllers/Office/ApplicationController';
import DecisionController from '@/actions/App/Http/Controllers/Office/DecisionController';
import PermitPrintController from '@/actions/App/Http/Controllers/PermitPrintController';
import DeleteButton from '@/components/admin/DeleteButton.vue';
import FormField from '@/components/admin/FormField.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import PermitTimeline from '@/components/admin/PermitTimeline.vue';
import StatusBadge from '@/components/admin/StatusBadge.vue';
import Textarea from '@/components/admin/Textarea.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate, formatDateTime, humanize } from '@/lib/format';

const props = defineProps<{
    application: {
        id: number;
        is_temporary: boolean;
        practice_name: string;
        practice_address: string;
        practice_email: string | null;
        practice_phone: string | null;
        practice_fax: string | null;
        status: string;
        permit_number: string | null;
        rejection_reason: string | null;
        expires_on: string | null;
        submitted_at: string;
        decided_at: string | null;
        decider: string | null;
        events: {
            id: number;
            to_status: string;
            note: string | null;
            by: string;
            at: string;
        }[];
    };
    applicant: {
        email: string;
        profession: string;
        licenseLabel: string | null;
        licenseExpired: boolean;
        profile: {
            full_name: string;
            address: string;
            birth_place: string;
            birth_date: string;
            gender: string;
            graduation_year: number;
            license_number: string | null;
            license_expires_on: string | null;
            university: string | null;
            mobile: string;
            phone: string | null;
        } | null;
        documents: { id: number; label: string; original_name: string }[];
        missingDocuments: string[];
    };
}>();

const decisionError = computed(
    () => (usePage().props.errors as Record<string, string>).decision,
);
const canPrint = computed(() =>
    ['approved', 'expired'].includes(props.application.status),
);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Applications', href: ApplicationController.index() },
            { title: 'Review', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Review: ${application.practice_name}`" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <PageHeader
            :title="applicant.profile?.full_name ?? 'Applicant'"
            :description="`${applicant.profession} · ${application.practice_name}${application.is_temporary ? ' (temporary)' : ''}`"
        >
            <StatusBadge :status="application.status" />
            <Button v-if="canPrint" variant="outline" as-child>
                <a
                    :href="PermitPrintController.url(application.id)"
                    target="_blank"
                    >Print permit</a
                >
            </Button>
            <DeleteButton
                :action="ApplicationController.destroy.form(application.id)"
                description="Delete this application? Its history is kept in the archive."
            />
        </PageHeader>

        <Alert v-if="decisionError" variant="destructive">
            <AlertTitle>That step is not possible</AlertTitle>
            <AlertDescription>{{ decisionError }}</AlertDescription>
        </Alert>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                <section
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <h2 class="mb-3 font-medium">Applicant</h2>
                    <dl
                        v-if="applicant.profile"
                        class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm"
                    >
                        <dt class="text-muted-foreground">Born</dt>
                        <dd>
                            {{ applicant.profile.birth_place }},
                            {{ formatDate(applicant.profile.birth_date) }} ·
                            {{ humanize(applicant.profile.gender) }}
                        </dd>
                        <dt class="text-muted-foreground">Address</dt>
                        <dd class="whitespace-pre-line">
                            {{ applicant.profile.address }}
                        </dd>
                        <dt class="text-muted-foreground">Contact</dt>
                        <dd>
                            {{ applicant.email }} ·
                            {{ applicant.profile.mobile }}
                        </dd>
                        <dt class="text-muted-foreground">Graduated</dt>
                        <dd>{{ applicant.profile.graduation_year }}</dd>
                        <template v-if="applicant.licenseLabel">
                            <dt class="text-muted-foreground">
                                {{ applicant.licenseLabel }}
                            </dt>
                            <dd>
                                <span class="font-mono">{{
                                    applicant.profile.license_number
                                }}</span
                                >, valid until
                                {{
                                    formatDate(
                                        applicant.profile.license_expires_on,
                                    )
                                }}
                                <span
                                    v-if="applicant.licenseExpired"
                                    class="font-medium text-destructive"
                                    >· expired</span
                                >
                            </dd>
                        </template>
                        <template v-else>
                            <dt class="text-muted-foreground">University</dt>
                            <dd>{{ applicant.profile.university }}</dd>
                        </template>
                    </dl>
                    <p v-else class="text-sm text-muted-foreground">
                        The applicant has not filled in their details.
                    </p>
                </section>

                <section
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <h2 class="mb-3 font-medium">Practice location</h2>
                    <dl
                        class="grid grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-sm"
                    >
                        <dt class="text-muted-foreground">Address</dt>
                        <dd class="whitespace-pre-line">
                            {{ application.practice_address }}
                        </dd>
                        <dt class="text-muted-foreground">Contact</dt>
                        <dd>
                            {{
                                [
                                    application.practice_email,
                                    application.practice_phone,
                                    application.practice_fax,
                                ]
                                    .filter(Boolean)
                                    .join(' · ') || '—'
                            }}
                        </dd>
                        <dt class="text-muted-foreground">Submitted</dt>
                        <dd>{{ formatDateTime(application.submitted_at) }}</dd>
                        <template v-if="application.permit_number">
                            <dt class="text-muted-foreground">Permit</dt>
                            <dd>
                                <span class="font-mono">{{
                                    application.permit_number
                                }}</span
                                >, valid until
                                {{ formatDate(application.expires_on) }}
                            </dd>
                        </template>
                        <template v-if="application.rejection_reason">
                            <dt class="text-muted-foreground">
                                Rejection reason
                            </dt>
                            <dd>{{ application.rejection_reason }}</dd>
                        </template>
                    </dl>
                </section>

                <section
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <h2 class="mb-3 font-medium">Documents</h2>
                    <ul class="space-y-1 text-sm">
                        <li
                            v-for="document in applicant.documents"
                            :key="document.id"
                        >
                            <a
                                :href="
                                    DocumentDownloadController.url(document.id)
                                "
                                target="_blank"
                                class="underline"
                                >{{ document.label }}</a
                            >
                            <span class="text-muted-foreground">
                                · {{ document.original_name }}</span
                            >
                        </li>
                    </ul>
                    <p
                        v-if="applicant.missingDocuments.length"
                        class="mt-2 text-sm text-destructive"
                    >
                        Missing: {{ applicant.missingDocuments.join(', ') }}
                    </p>
                </section>
            </div>

            <div class="space-y-4">
                <section
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <h2 class="mb-3 font-medium">Decision</h2>

                    <Form
                        v-if="application.status === 'pending'"
                        v-bind="DecisionController.review.form(application.id)"
                        v-slot="{ processing }"
                    >
                        <p class="mb-3 text-sm text-muted-foreground">
                            Start the review to approve or reject. The applicant
                            can no longer edit it after this.
                        </p>
                        <Button :disabled="processing">Start review</Button>
                    </Form>

                    <div
                        v-else-if="application.status === 'in_review'"
                        class="space-y-6"
                    >
                        <Form
                            v-bind="
                                DecisionController.approve.form(application.id)
                            "
                            class="space-y-3"
                            v-slot="{ errors, processing }"
                        >
                            <FormField
                                label="Permit number"
                                for="permit_number"
                                :error="errors.permit_number"
                            >
                                <Input
                                    id="permit_number"
                                    name="permit_number"
                                    class="font-mono"
                                    placeholder="503/SIP/0001/2026"
                                    required
                                />
                            </FormField>
                            <Button
                                :disabled="
                                    processing || applicant.licenseExpired
                                "
                                class="w-full"
                                >Approve and issue permit</Button
                            >
                            <p
                                v-if="applicant.licenseExpired"
                                class="text-xs text-destructive"
                            >
                                The applicant's license has expired, so no
                                permit can be issued.
                            </p>
                        </Form>
                        <Form
                            v-bind="
                                DecisionController.reject.form(application.id)
                            "
                            class="space-y-3 border-t pt-4"
                            v-slot="{ errors, processing }"
                        >
                            <FormField
                                label="Reason for rejection"
                                for="reason"
                                hint="Sent to the applicant."
                            >
                                <Textarea
                                    id="reason"
                                    name="reason"
                                    required
                                    minlength="10"
                                />
                            </FormField>
                            <InputError :message="errors.reason" />
                            <Button
                                variant="destructive"
                                :disabled="processing"
                                class="w-full"
                                >Reject</Button
                            >
                        </Form>
                    </div>

                    <p v-else class="text-sm text-muted-foreground">
                        Decided {{ formatDateTime(application.decided_at)
                        }}<template v-if="application.decider">
                            by {{ application.decider }}</template
                        >.
                    </p>
                </section>

                <section
                    class="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <PermitTimeline :events="application.events" />
                </section>
            </div>
        </div>
    </div>
</template>
