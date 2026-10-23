@php
    // PHP's tokenizer treats a literal "<?xml" at the top of this file as a
    // short open tag when short_open_tag is enabled, so it's built up here
    // rather than written directly.
    echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($staticUrls as $url)
    <url>
        <loc>{{ $url }}</loc>
    </url>
@endforeach
@foreach ($posts as $post)
    <url>
        <loc>{{ $post->url() }}</loc>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach ($recaps as $recap)
    <url>
        <loc>{{ $recap->url() }}</loc>
        <lastmod>{{ $recap->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
</urlset>
