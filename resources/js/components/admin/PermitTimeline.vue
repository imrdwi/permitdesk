<script setup lang="ts">
import { formatDateTime, humanize } from '@/lib/format';

defineProps<{
    events: {
        id: number;
        to_status: string;
        note: string | null;
        by: string;
        at: string;
    }[];
}>();
</script>

<template>
    <section>
        <h2 class="mb-3 font-medium">History</h2>
        <ol class="relative space-y-4 border-l pl-5">
            <li v-for="event in events" :key="event.id" class="relative">
                <span
                    class="absolute top-1.5 -left-[25px] size-2.5 rounded-full bg-primary"
                />
                <p class="text-sm font-medium">
                    {{ humanize(event.to_status) }}
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ event.by }} · {{ formatDateTime(event.at) }}
                </p>
                <p v-if="event.note" class="mt-1 text-sm">{{ event.note }}</p>
            </li>
        </ol>
    </section>
</template>
