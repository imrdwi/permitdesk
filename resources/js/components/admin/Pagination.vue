<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Paginated } from '@/types';

defineProps<{
    page: Paginated<unknown>;
}>();
</script>

<template>
    <nav
        v-if="page.total > 0"
        class="flex flex-wrap items-center justify-between gap-2 text-sm"
        aria-label="Pagination"
    >
        <p class="text-muted-foreground">
            Showing {{ page.from }}–{{ page.to }} of {{ page.total }}
        </p>
        <div v-if="page.links.length > 3" class="flex flex-wrap gap-1">
            <template v-for="link in page.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="rounded-md border px-2.5 py-1 hover:bg-accent"
                    :class="{
                        'bg-primary text-primary-foreground hover:bg-primary':
                            link.active,
                    }"
                    :aria-current="link.active ? 'page' : undefined"
                    ><span v-html="link.label"
                /></Link>
                <span
                    v-else
                    class="rounded-md border px-2.5 py-1 text-muted-foreground opacity-50"
                    v-html="link.label"
                />
            </template>
        </div>
    </nav>
</template>
