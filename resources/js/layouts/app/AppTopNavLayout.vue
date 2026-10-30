<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ChevronRight } from '@lucide/vue';
import LogoutDialog from '@/components/LogoutDialog.vue';
import { Toaster } from '@/components/ui/sonner';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useNavigation } from '@/composables/useNavigation';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(defineProps<{ breadcrumbs?: BreadcrumbItem[] }>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const items = useNavigation();
const { isCurrentOrParentUrl, isCurrentUrl } = useCurrentUrl();

// Atlant's trail always starts at Home.
const trail = computed<BreadcrumbItem[]>(() =>
    isCurrentUrl(dashboard())
        ? [{ title: 'Home', href: dashboard() }]
        : [{ title: 'Home', href: dashboard() }, ...props.breadcrumbs],
);
</script>

<template>
    <div class="min-h-svh bg-page">
        <nav class="bg-white" aria-label="Main">
            <div class="mx-auto flex max-w-[1140px] flex-wrap items-stretch">
                <Link
                    :href="dashboard()"
                    class="flex h-[50px] w-[200px] items-center justify-center bg-[#888] text-[25px] text-white hover:bg-[#7a7a7a]"
                >
                    {{ page.props.name }}
                </Link>
                <Link
                    v-for="item in items"
                    :key="item.title"
                    :href="item.href"
                    :class="
                        cn(
                            'flex h-[50px] items-center gap-2 px-4 text-[13px] text-[#444] hover:bg-[#f5f5f5]',
                            isCurrentOrParentUrl(item.href) &&
                                'bg-[#f5f5f5] text-[#1caf9a]',
                        )
                    "
                >
                    <component :is="item.icon" class="size-4" />
                    {{ item.title }}
                </Link>
                <div class="ml-auto">
                    <LogoutDialog />
                </div>
            </div>
        </nav>

        <div class="bg-[#e8e8e8]">
            <ol
                class="mx-auto flex max-w-[1140px] items-center gap-1.5 px-4 py-[7px] text-[11px] xl:px-0"
            >
                <template v-for="(crumb, index) in trail" :key="index">
                    <li v-if="index > 0" aria-hidden="true">
                        <ChevronRight class="size-3 text-[#999]" />
                    </li>
                    <li
                        v-if="index === trail.length - 1"
                        class="text-[#434a54]"
                        aria-current="page"
                    >
                        {{ crumb.title }}
                    </li>
                    <li v-else>
                        <Link
                            :href="crumb.href"
                            class="text-[#999] hover:text-[#434a54]"
                            >{{ crumb.title }}</Link
                        >
                    </li>
                </template>
            </ol>
        </div>

        <main class="mx-auto max-w-[1140px] px-4 py-5 xl:px-0">
            <div class="bg-white shadow-[0_1px_1px_0_rgba(0,0,0,0.2)]">
                <slot />
            </div>
        </main>
        <Toaster />
    </div>
</template>
