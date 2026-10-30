<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const year = new Date().getFullYear();
</script>

<template>
    <!-- Atlant's sign-in box: a light grey panel near the top of a white page. -->
    <div class="min-h-svh bg-white px-4 pt-16 sm:pt-24">
        <div class="mx-auto w-full max-w-[400px]">
            <p class="mb-5 text-center text-[25px] text-[#888]">
                {{ page.props.name }}
            </p>
            <div
                class="bg-[#f7f7f7] p-5 [--background:#f7f7f7] [&_button[type=submit]]:bg-[#555] [&_button[type=submit]:hover]:bg-[#444] [&_input:not([type=checkbox])]:h-[42px] [&_input:not([type=checkbox])]:border-transparent [&_input:not([type=checkbox])]:bg-[rgba(180,180,180,0.2)]"
            >
                <h1 class="text-[19px] leading-7 font-light text-[#555]">
                    {{ title }}
                </h1>
                <p v-if="description" class="mb-4 text-[13px] text-[#777]">
                    {{ description }}
                </p>
                <slot />
            </div>
            <div class="flex justify-between p-2.5 text-xs text-[#555]">
                <span>&copy; {{ year }} {{ page.props.name }}</span>
                <Dialog>
                    <DialogTrigger class="hover:underline">About</DialogTrigger>
                    <DialogContent>
                        <DialogTitle>About {{ page.props.name }}</DialogTitle>
                        <DialogDescription
                            class="space-y-2 text-sm leading-relaxed"
                        >
                            <span class="block">
                                {{ page.props.name }} handles practice permits
                                for doctors, nurses, midwives, pharmacists and
                                student placements.
                            </span>
                            <span class="block">
                                Register, fill in your details and upload the
                                documents your profession needs, then apply for
                                each place you practise. The licensing office
                                reviews each application and issues a permit you
                                can print.
                            </span>
                        </DialogDescription>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </div>
</template>
