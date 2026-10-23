<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { site } from '@/lib/site';
import { feed } from '@/routes';

const props = withDefaults(
    defineProps<{
        title: string;
        description: string;
        image?: string | null;
        article?: { publishedAt: string; updatedAt?: string | null } | null;
        noindex?: boolean;
    }>(),
    {
        image: null,
        article: null,
        noindex: false,
    },
);

const page = usePage();

/** Absolute URL for the current page, built from the shared app URL plus Inertia's own tracked path+query. */
const canonicalUrl = computed(() => `${page.props.appUrl}${page.url}`);

const ogType = computed(() => (props.article ? 'article' : 'website'));

const jsonLd = computed(() => {
    if (!props.article) {
        return null;
    }

    return JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'BlogPosting',
        headline: props.title,
        description: props.description,
        ...(props.image ? { image: props.image } : {}),
        datePublished: props.article.publishedAt,
        dateModified: props.article.updatedAt ?? props.article.publishedAt,
        url: canonicalUrl.value,
        author: {
            '@type': 'Person',
            name: site.author,
        },
    });
});
</script>

<template>
    <Head>
        <title>{{ title }}</title>
        <meta
            head-key="description"
            name="description"
            :content="description"
        />
        <meta
            v-if="noindex"
            head-key="robots"
            name="robots"
            content="noindex"
        />

        <link head-key="canonical" rel="canonical" :href="canonicalUrl" />

        <link
            head-key="alternate-rss"
            rel="alternate"
            type="application/rss+xml"
            :title="`${page.props.name} — RSS feed`"
            :href="feed().url"
        />

        <meta head-key="og:type" property="og:type" :content="ogType" />
        <meta
            head-key="og:site_name"
            property="og:site_name"
            :content="page.props.name"
        />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta
            head-key="og:description"
            property="og:description"
            :content="description"
        />
        <meta head-key="og:url" property="og:url" :content="canonicalUrl" />
        <meta
            v-if="image"
            head-key="og:image"
            property="og:image"
            :content="image"
        />

        <meta
            head-key="twitter:card"
            name="twitter:card"
            :content="image ? 'summary_large_image' : 'summary'"
        />
        <meta head-key="twitter:title" name="twitter:title" :content="title" />
        <meta
            head-key="twitter:description"
            name="twitter:description"
            :content="description"
        />
        <meta
            v-if="image"
            head-key="twitter:image"
            name="twitter:image"
            :content="image"
        />

        <meta
            v-if="article"
            head-key="article:published_time"
            property="article:published_time"
            :content="article.publishedAt"
        />
        <meta
            v-if="article?.updatedAt"
            head-key="article:modified_time"
            property="article:modified_time"
            :content="article.updatedAt"
        />

        <!--
            Deliberately not a literal <script> tag: Vue's compiler parses
            <script> as HTML "raw text" (no interpolation) and Inertia's Head
            serializer only reads element *children*, not v-html/innerHTML —
            so neither `{{ jsonLd }}` nor v-html render anything inside one.
            `:is="'script'"` sidesteps the raw-text parsing (the compiler
            doesn't know it's a script tag until runtime), letting the
            interpolation below compile normally as a real text child.
        -->
        <component
            v-if="jsonLd"
            :is="'script'"
            head-key="ld-json"
            type="application/ld+json"
            >{{ jsonLd }}</component
        >
    </Head>
</template>
