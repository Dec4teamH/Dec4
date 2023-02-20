

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div>
                  <table border="1">
                    <thead>
                      <tr>
                        @foreach ($weeks as $week)
                            <th>{{ $week }}　</th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        @foreach ($ratios as $ratio)
                            <td style="text-align: center;">{{ $ratio }}%</td>
                        @endforeach
                      </tr>
                    </tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

