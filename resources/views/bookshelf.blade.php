@php use Illuminate\Support\Carbon; @endphp
<x-frontend-layout>
    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold text-gray-900 dark:text-white">Books</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">A list of all the books I have read recently.</p>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="relative min-w-full divide-y divide-gray-300 dark:divide-white/15">
                            <thead>
                            <tr>
                                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0 dark:text-white">Title</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Author</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Finished</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Page Count</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach($currently_reading as $book)
                                <tr>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0 dark:text-white">{{ $book[0] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $book[1] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">Currently Reading</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $book[4] }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" class="text-right py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0 dark:text-white">Total Finished</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $number_of_books }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ number_format($number_of_pages) }}</td>
                            </tr>
                            @foreach($finished_books as $book)
                                <tr>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0 dark:text-white">{{ $book[0] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $book[1] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ date_format(Carbon::createFromFormat('Y-m-d', $book[3]), 'd-m-Y') }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $book[4] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-frontend-layout>
