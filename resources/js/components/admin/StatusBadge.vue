<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    status: string;
}>();

// Atlant's solid labels. Dark text where white would fall below 4.5:1.
const styles: Record<string, string> = {
    pending: 'bg-[#fea223] text-[#1f2329]',
    in_review: 'bg-[#3fbae4] text-[#1f2329]',
    approved: 'bg-[#95b75d] text-[#1f2329]',
    rejected: 'bg-[#e04b4a] text-white',
    expired: 'bg-[#999] text-white',
};

const label = computed(() =>
    props.status
        .replace(/_/g, ' ')
        .replace(/^\w/, (letter) => letter.toUpperCase()),
);
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex rounded-sm px-1.5 py-0.5 text-[11px] font-semibold whitespace-nowrap',
                styles[status] ?? styles.expired,
            )
        "
    >
        {{ label }}
    </span>
</template>
