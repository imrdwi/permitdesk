<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import StaffController from '@/actions/App/Http/Controllers/Office/StaffController';
import FormField from '@/components/admin/FormField.vue';
import NativeSelect from '@/components/admin/NativeSelect.vue';
import PageHeader from '@/components/admin/PageHeader.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    staffMember: {
        id: number;
        name: string;
        email: string;
        role: string;
    } | null;
}>();

const isSelf = props.staffMember?.id === usePage().props.auth.user.id;
const title = props.staffMember
    ? `Edit ${props.staffMember.name}`
    : 'New office account';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Office staff', href: StaffController.index() },
            { title: 'Form', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex max-w-xl flex-1 flex-col gap-6 p-4">
        <PageHeader :title="title" />

        <Form
            v-bind="
                staffMember
                    ? StaffController.update.form(staffMember.id)
                    : StaffController.store.form()
            "
            class="space-y-5"
            v-slot="{ errors, processing }"
        >
            <FormField label="Name" for="name" :error="errors.name">
                <Input
                    id="name"
                    name="name"
                    :default-value="staffMember?.name"
                    required
                />
            </FormField>
            <FormField label="Email" for="email" :error="errors.email">
                <Input
                    id="email"
                    name="email"
                    type="email"
                    :default-value="staffMember?.email"
                    required
                />
            </FormField>
            <FormField
                label="Role"
                for="role"
                :error="errors.role"
                :hint="isSelf ? 'You cannot change your own role.' : undefined"
            >
                <NativeSelect id="role" name="role" :disabled="isSelf">
                    <option
                        value="staff"
                        :selected="staffMember?.role !== 'head'"
                    >
                        Staff
                    </option>
                    <option
                        value="head"
                        :selected="staffMember?.role === 'head'"
                    >
                        Head of office
                    </option>
                </NativeSelect>
                <input
                    v-if="isSelf"
                    type="hidden"
                    name="role"
                    :value="staffMember?.role"
                />
            </FormField>
            <FormField
                label="Password"
                for="password"
                :error="errors.password"
                :hint="
                    staffMember
                        ? 'Leave blank to keep the current password.'
                        : undefined
                "
            >
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    :required="!staffMember"
                />
            </FormField>
            <div class="flex gap-2">
                <Button :disabled="processing">Save</Button>
                <Button variant="ghost" as-child>
                    <Link :href="StaffController.index()">Cancel</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>
