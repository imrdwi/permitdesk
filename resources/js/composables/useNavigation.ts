import { usePage } from '@inertiajs/vue3';
import {
    ClipboardCheck,
    FileStack,
    FileText,
    NotebookPen,
    Settings,
    UserCog,
    UserRound,
} from '@lucide/vue';
import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import ApplicationController from '@/actions/App/Http/Controllers/Applicant/ApplicationController';
import DocumentController from '@/actions/App/Http/Controllers/Applicant/DocumentController';
import ProfileController from '@/actions/App/Http/Controllers/Applicant/ProfileController';
import OfficeApplicationController from '@/actions/App/Http/Controllers/Office/ApplicationController';
import ReportController from '@/actions/App/Http/Controllers/Office/ReportController';
import StaffController from '@/actions/App/Http/Controllers/Office/StaffController';
import type { NavItem } from '@/types';

/** The top menu for the signed-in role. The office name links home. */
export function useNavigation(): ComputedRef<NavItem[]> {
    const page = usePage();

    return computed(() => {
        const role = page.props.auth.user.role;
        const items: NavItem[] = [];

        if (role === 'applicant') {
            items.push(
                {
                    title: 'My details',
                    href: ProfileController.edit(),
                    icon: UserRound,
                },
                {
                    title: 'My documents',
                    href: DocumentController.index(),
                    icon: FileStack,
                },
                {
                    title: 'My applications',
                    href: ApplicationController.index(),
                    icon: FileText,
                },
            );
        } else {
            items.push({
                title: 'Applications',
                href: OfficeApplicationController.index(),
                icon: ClipboardCheck,
            });
        }

        if (role === 'head') {
            items.push(
                {
                    title: 'Monthly report',
                    href: ReportController.index(),
                    icon: NotebookPen,
                },
                {
                    title: 'Office staff',
                    href: StaffController.index(),
                    icon: UserCog,
                },
            );
        }

        // /settings redirects to the profile page and prefixes every settings page.
        items.push({ title: 'Settings', href: '/settings', icon: Settings });

        return items;
    });
}
