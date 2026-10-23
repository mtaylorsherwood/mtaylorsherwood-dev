<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Container from '@/components/site/Container.vue';
import { GitHubIcon, LinkedInIcon } from '@/components/site/icons';
import SeoHead from '@/components/site/SeoHead.vue';
import portraitImage from '@/images/portrait.jpg';
import { site } from '@/lib/site';

const socialLinks = [
    { href: site.social.github, label: 'Follow on GitHub', icon: GitHubIcon },
    {
        href: site.social.linkedIn,
        label: 'Follow on LinkedIn',
        icon: LinkedInIcon,
    },
];
</script>

<template>
    <SeoHead
        title="About"
        description="I'm Martyn, a software engineer, living in Yorkshire, England."
    />

    <Container class="mt-16 sm:mt-32">
        <div
            class="grid grid-cols-1 gap-y-16 lg:grid-cols-2 lg:grid-rows-[auto_1fr] lg:gap-y-12"
        >
            <div class="lg:pl-20">
                <div class="max-w-xs px-2.5 lg:max-w-none">
                    <img
                        :src="portraitImage"
                        :alt="`Portrait of ${site.author} taken in Delft, Netherlands for a trip titled 'The Manniversary'`"
                        class="aspect-square rotate-3 rounded-2xl bg-espresso-900 object-cover"
                    />
                </div>
            </div>
            <div class="lg:order-first lg:row-span-2">
                <h1
                    class="text-2xl font-bold tracking-tight text-ash-grey-100 sm:text-4xl"
                >
                    {{ site.author }}
                </h1>
                <div class="mt-6 space-y-7 text-base text-ash-grey-400">
                    <p>
                        Hello, my name is Martyn. I guess you knew that... and it says it just above this paragraph.
                    </p>
                    <p>
                        I'm a software engineer, living in Yorkshire, England. I've got a smaller version (yet growing rapidly) of myself at home, along with my better half, who keeps me in check.
                    </p>
                    <p>
                        I'm an avid reader, mainly of Fantasy and Science Fiction, along with probably a few too many books about <span style="text-decoration: line-through;">self-help</span> smart thinking.
                    </p>
                    <p>
                        Also, since I've now hit that age, I find myself pottering around the garden, trying to grow vegetables and an unhealthy number of very spicy chillies.
                    </p>
                    <p>
                        I am currently available for work, be it permanent or freelance. I'm looking for a new challenge, and would love to hear from you. You can find more information on my LinkedIn profile.
                    </p>
                </div>
            </div>
            <div class="lg:pl-20">
                <ul role="list">
                    <li
                        v-for="(link, index) in socialLinks"
                        :key="link.label"
                        :class="['flex', index > 0 && 'mt-4']"
                    >
                        <Link
                            :href="link.href"
                            class="group flex text-sm font-medium text-ash-grey-200 transition hover:text-spicy-paprika-400"
                        >
                            <component
                                :is="link.icon"
                                class="h-6 w-6 flex-none fill-ash-grey-500 transition group-hover:fill-spicy-paprika-400"
                            />
                            <span class="ml-4">{{ link.label }}</span>
                        </Link>
                    </li>
                </ul>
            </div>
        </div>
    </Container>
</template>
