<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import ProfileController from '@/actions/App/Http/Controllers/Applicant/ProfileController';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import Textarea from '@/components/admin/Textarea.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { humanize, toDateInput } from '@/lib/format';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'My details', href: ProfileController.edit() }],
    },
});

defineProps<{
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
        phone: string | null;
        mobile: string;
    } | null;
    profession: { value: string; label: string; licenseLabel: string | null };
    genders: string[];
}>();
</script>

<template>
    <Head title="My details" />

    <div class="flex max-w-3xl flex-1 flex-col gap-6 p-4">
        <PageHeader
            title="My details"
            :description="`Applying as: ${profession.label}. These details appear on every permit.`"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField
                    label="Full name (with titles)"
                    for="full_name"
                    :error="errors.full_name"
                    class="sm:col-span-2"
                >
                    <Input
                        id="full_name"
                        name="full_name"
                        :default-value="profile?.full_name"
                        required
                    />
                </FormField>
                <FormField
                    label="Place of birth"
                    for="birth_place"
                    :error="errors.birth_place"
                >
                    <Input
                        id="birth_place"
                        name="birth_place"
                        :default-value="profile?.birth_place"
                        required
                    />
                </FormField>
                <FormField
                    label="Date of birth"
                    for="birth_date"
                    :error="errors.birth_date"
                >
                    <Input
                        id="birth_date"
                        name="birth_date"
                        type="date"
                        :default-value="toDateInput(profile?.birth_date)"
                        required
                    />
                </FormField>
                <FormField label="Gender" for="gender" :error="errors.gender">
                    <NativeSelect id="gender" name="gender" required>
                        <option
                            v-for="gender in genders"
                            :key="gender"
                            :value="gender"
                            :selected="gender === profile?.gender"
                        >
                            {{ humanize(gender) }}
                        </option>
                    </NativeSelect>
                </FormField>
                <FormField
                    label="Graduation year"
                    for="graduation_year"
                    :error="errors.graduation_year"
                >
                    <Input
                        id="graduation_year"
                        name="graduation_year"
                        type="number"
                        min="1950"
                        :default-value="profile?.graduation_year"
                        required
                    />
                </FormField>
                <template v-if="profession.licenseLabel">
                    <FormField
                        :label="`${profession.licenseLabel} number`"
                        for="license_number"
                        :error="errors.license_number"
                    >
                        <Input
                            id="license_number"
                            name="license_number"
                            :default-value="profile?.license_number ?? ''"
                            required
                        />
                    </FormField>
                    <FormField
                        :label="`${profession.licenseLabel} valid until`"
                        for="license_expires_on"
                        :error="errors.license_expires_on"
                        hint="Your permit will be valid until this date."
                    >
                        <Input
                            id="license_expires_on"
                            name="license_expires_on"
                            type="date"
                            :default-value="
                                toDateInput(profile?.license_expires_on)
                            "
                            required
                        />
                    </FormField>
                </template>
                <FormField
                    v-else
                    label="University"
                    for="university"
                    :error="errors.university"
                    class="sm:col-span-2"
                >
                    <Input
                        id="university"
                        name="university"
                        :default-value="profile?.university ?? ''"
                        required
                    />
                </FormField>
                <FormField label="Mobile" for="mobile" :error="errors.mobile">
                    <Input
                        id="mobile"
                        name="mobile"
                        type="tel"
                        :default-value="profile?.mobile"
                        required
                    />
                </FormField>
                <FormField
                    label="Phone (optional)"
                    for="phone"
                    :error="errors.phone"
                >
                    <Input
                        id="phone"
                        name="phone"
                        type="tel"
                        :default-value="profile?.phone ?? ''"
                    />
                </FormField>
                <FormField
                    label="Home address"
                    for="address"
                    :error="errors.address"
                    class="sm:col-span-2"
                >
                    <Textarea
                        id="address"
                        name="address"
                        :value="profile?.address ?? ''"
                        required
                    />
                </FormField>
            </div>
            <Button :disabled="processing">Save details</Button>
        </Form>
    </div>
</template>
