<x-frontend-layout>
    <div class="py-12">
        @php /** @var \App\Models\Post $post */ @endphp
        @foreach($posts as $post)
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="bg-white px-6 py-16 lg:px-8">
                        <div class="mx-auto max-w-3xl text-base leading-7 text-gray-700">
                            <p class="text-base font-semibold leading-7 text-indigo-600">Published: {{ \Carbon\Carbon::parse($post->getAttribute('published_at'))->setTimezone('Europe/London')->format('jS F Y') }}</p>
                            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $post->getAttribute('title') }}</h1>
                            <div class="mt-6 max-w-2xl">
                                <p>
                                    {{ $post->getAttribute('post_content') }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>
</x-frontend-layout>
