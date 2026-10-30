<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import ApplicationController from '@/actions/App/Http/Controllers/Applicant/ApplicationController';
import FormField from '@/components/admin/FormField.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    application: {
        id: number;
        is_temporary: boolean;
        practice_name: string;
        practice_address: string;
        practice_email: string | null;
        practice_phone: string | null;
        practice_fax: string | null;
    } | null;
    mustBeTemporary: boolean;
}>();

const title = props.application ? 'Edit application' : 'New application';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My applications', href: ApplicationController.index() },
            { title: 'Form', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex max-w-2xl flex-1 flex-col gap-6 p-4">
        <PageHeader
            :title="title"
            description="Where will you practise? Your details and documents are attached automatically."
        />

        <Form
            v-bind="
                application
                    ? ApplicationController.update.form(application.id)
                    : ApplicationController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <fieldset class="space-y-2">
                <legend class="text-sm font-medium">Permit type</legend>
                <input type="hidden" name="is_temporary" value="0" />
                <label class="flex items-start gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="is_temporary"
                        value="1"
                        class="mt-0.5 size-4 accent-primary"
                        :checked="application?.is_temporary || mustBeTemporary"
                        :disabled="mustBeTemporary"
                    />
                    <span>
                        Temporary practice location
                        <span class="block text-xs text-muted-foreground">
                            {{
                                mustBeTemporary
                                    ? 'You already have a primary permit or application, so further locations are temporary.'
                                    : 'Leave unticked for your primary practice location.'
                            }}
                        </span>
                    </span>
                </label>
                <input
                    v-if="mustBeTemporary"
                    type="hidden"
                    name="is_temporary"
                    value="1"
                />
                <InputError :message="errors.is_temporary" />
            </fieldset>

            <FormField
                label="Practice name"
                for="practice_name"
                :error="errors.practice_name"
                hint="Clinic, hospital, pharmacy or community health centre."
            >
                <Input
                    id="practice_name"
                    name="practice_name"
                    :default-value="application?.practice_name"
                    required
                />
            </FormField>
            <FormField
                label="Practice address"
                for="practice_address"
                :error="errors.practice_address"
            >
                <Textarea
                    id="practice_address"
                    name="practice_address"
                    :value="application?.practice_address ?? ''"
                    required
                />
            </FormField>
            <div class="grid gap-5 sm:grid-cols-3">
                <FormField
                    label="Email"
                    for="practice_email"
                    :error="errors.practice_email"
                >
                    <Input
                        id="practice_email"
                        name="practice_email"
                        type="email"
                        :default-value="application?.practice_email ?? ''"
                    />
                </FormField>
                <FormField
                    label="Phone"
                    for="practice_phone"
                    :error="errors.practice_phone"
                >
                    <Input
                        id="practice_phone"
                        name="practice_phone"
                        type="tel"
                        :default-value="application?.practice_phone ?? ''"
                    />
                </FormField>
                <FormField
                    label="Fax"
                    for="practice_fax"
                    :error="errors.practice_fax"
                >
                    <Input
                        id="practice_fax"
                        name="practice_fax"
                        :default-value="application?.practice_fax ?? ''"
                    />
                </FormField>
            </div>
            <div class="flex gap-2">
                <Button :disabled="processing">{{
                    application ? 'Save changes' : 'Submit application'
                }}</Button>
                <Button variant="ghost" as-child>
                    <Link
                        :href="
                            application
                                ? ApplicationController.show(application.id)
                                : ApplicationController.index()
                        "
                        >Cancel</Link
                    >
                </Button>
            </div>
        </Form>
    </div>
</template>
