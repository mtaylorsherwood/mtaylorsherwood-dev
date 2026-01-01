@php use Illuminate\Support\Carbon; @endphp
<x-frontend-layout>
    <div class="py-12">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold text-gray-900">Books</h1>
                    <p class="mt-2 text-sm text-gray-700">A list of all the books I have read recently.</p>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="relative min-w-full divide-y divide-gray-300">
                            <thead>
                            <tr>
                                <th scope="col" class="py-3.5 pr-3 pl-4 text-left text-sm font-semibold text-gray-900 sm:pl-0">Title</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Author</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Finished</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Page Count</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @foreach($currently_reading as $book)
                                @continue(Carbon::createFromFormat('Y-m-d', $book[2])->isFuture())
                                <tr>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">{{ $book[0] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $book[1] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">Currently Reading</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $book[4] }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" class="text-right py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">Total Finished</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $number_of_books }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ number_format($number_of_pages) }}</td>
                            </tr>
                            @foreach($finished_books as $book)
                                @if($loop->first)
                                    @php $year = 2025 @endphp
                                    <tr>
                                        @if(array_key_exists($year, $stats))
                                            <td class="py-4 pr-3 pl-4 text-sm whitespace-nowrap text-gray-900 sm:pl-0 text-justify">
                                                <p class="mb-2">Daily Target Reached: <strong>{{ $stats[$year]['days'] }}</strong> days ({{ $stats[$year]['percentage'] }}%)</p>
                                                <p>
                                                    <span class="mr-4">20 to 24: <strong>{{ $stats[$year]['minimum'] }}</strong>,</span>
                                                    <span class="mr-4">25 to 49: <strong>{{ $stats[$year]['bronze'] }}</strong>,</span>
                                                    <span class="mr-4">50 to 99: <strong>{{ $stats[$year]['silver'] }}</strong>,</span>
                                                    <span class="mr-4">100 to 199: <strong>{{ $stats[$year]['gold'] }}</strong>,</span>
                                                    200+: <strong>{{ $stats[$year]['platinum'] }}</strong>
                                                </p>
                                            </td>
                                            <td class="text-right py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">
                                            {{ $year }}
                                            </td>
                                        @else
                                            <td colspan="2" class="text-right py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">
                                                {{ $year }}
                                            </td>
                                        @endif

                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $finished_by_year['2025']['count'] }}</td>
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ number_format($finished_by_year['2025']['pages']) }}</td>
                                    </tr>
                                @endif
                                @if(intval(Carbon::createFromFormat('Y-m-d', $book[3])->format('Y')) < $year)
                                    @php $year = intval(Carbon::createFromFormat('Y-m-d', $book[3])->format('Y')) @endphp
                                    <tr>
                                        <td colspan="2"
                                            class="text-right py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">{{ Carbon::createFromFormat('Y-m-d', $book[3])->format('Y') }}</td>
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $finished_by_year['2025']['count'] }}</td>
                                        <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ number_format($finished_by_year['2025']['pages']) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">{{ $book[0] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $book[1] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ Carbon::createFromFormat('Y-m-d', $book[3])->format('d-m-Y') }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $book[4] }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2" class="text-right py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">To Be Read</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $number_to_be_read }}</td>
                                <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ number_format($number_of_to_be_read_pages) }}</td>
                            </tr>
                            @foreach($to_be_read as $book)
                                <tr>
                                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-0">{{ $book[0] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $book[1] }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ is_null($book[2]) ? '' : Carbon::createFromFormat('Y-m-d', $book[2])->format('M Y') }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ is_null($book[2]) ? '' : Carbon::createFromFormat('Y-m-d', $book[2])->format('M Y') }}</td>
                                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">{{ $book[4] }}</td>
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
