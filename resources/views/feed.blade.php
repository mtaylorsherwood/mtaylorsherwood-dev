@php
    // PHP's tokenizer treats a literal "<?xml" at the top of this file as a
    // short open tag when short_open_tag is enabled, so it's built up here
    // rather than written directly.
    echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
@endphp
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        <description>A place for random thoughts, book reviews, garden updates and maybe a sprinkling of software engineering.</description>
        <language>en-gb</language>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ $post->url() }}</link>
            <guid isPermaLink="true">{{ $post->url() }}</guid>
            <pubDate>{{ $post->published_at->format('D, d M Y H:i:s O') }}</pubDate>
            <description>{{ $post->excerpt() }}</description>
            <content:encoded><![CDATA[{!! $post->toHtml() !!}]]></content:encoded>
        </item>
@endforeach
    </channel>
</rss>
