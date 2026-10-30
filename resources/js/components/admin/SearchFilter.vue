<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import { Input } from '@/components/ui/input';

const props = withDefaults(
    defineProps<{
        filters: Record<string, string | number | undefined>;
        placeholder?: string;
    }>(),
    { placeholder: 'Search…' },
);

const search = ref(String(props.filters.search ?? ''));

const apply = useDebounceFn(() => {
    router.get(
        window.location.pathname,
        { ...props.filters, search: search.value || undefined },
        { preserveState: true, replace: true },
    );
}, 300);
</script>

<template>
    <div class="relative w-full max-w-xs">
        <Search
            class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="search"
            type="search"
            class="pl-8"
            :placeholder="placeholder"
            :aria-label="placeholder"
            @update:model-value="apply"
        />
    </div>
</template>
