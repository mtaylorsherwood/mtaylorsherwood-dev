import { about, books, home, projects } from '@/routes';
import blog from '@/routes/blog';
import type { NavItem } from '@/types';

/** Primary navigation for the public site, shown in the header. */
export const siteNavItems: NavItem[] = [
    { title: 'Home', href: home() },
    { title: 'About', href: about() },
    { title: 'Blog', href: blog.index() },
    { title: 'Books', href: books() },
    { title: 'Projects', href: projects() },
];

/** Footer navigation, which omits the blog. */
export const siteFooterNavItems: NavItem[] = [
    { title: 'Home', href: home() },
    { title: 'About', href: about() },
    { title: 'Books', href: books() },
    { title: 'Projects', href: projects() },
];
