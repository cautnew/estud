import { Link } from '@inertiajs/react';
import {
    BarChart3,
    BookOpen,
    CalendarDays,
    FileBarChart,
    FolderGit2,
    GraduationCap,
    LayoutGrid,
    NotebookPen,
    RotateCcw,
    Sparkles,
    Target,
    Timer,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as goalsIndex } from '@/routes/goals';
import { index as questionLogsIndex } from '@/routes/question-logs';
import { index as recommendationsIndex } from '@/routes/recommendations';
import { index as reportsIndex } from '@/routes/reports';
import { index as reviewsIndex } from '@/routes/reviews';
import { index as schedulesIndex } from '@/routes/schedules';
import { index as studyPlansIndex } from '@/routes/study-plans';
import { index as studySessionsIndex } from '@/routes/study-sessions';
import { index as subjectsIndex } from '@/routes/subjects';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Recomendações',
        href: recommendationsIndex(),
        icon: Sparkles,
    },
    {
        title: 'Disciplinas',
        href: subjectsIndex(),
        icon: GraduationCap,
    },
    {
        title: 'Planos de Estudo',
        href: studyPlansIndex(),
        icon: NotebookPen,
    },
    {
        title: 'Cronogramas',
        href: schedulesIndex(),
        icon: CalendarDays,
    },
    {
        title: 'Sessões de Estudo',
        href: studySessionsIndex(),
        icon: Timer,
    },
    {
        title: 'Revisões',
        href: reviewsIndex(),
        icon: RotateCcw,
    },
    {
        title: 'Questões',
        href: questionLogsIndex(),
        icon: BarChart3,
    },
    {
        title: 'Metas',
        href: goalsIndex(),
        icon: Target,
    },
    {
        title: 'Relatórios',
        href: reportsIndex(),
        icon: FileBarChart,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
