{{-- Testing Tab --}}
<div class="space-y-6">
    {{-- Testing Overview --}}
    <x-driver.info-card title="Testing Overview" icon="clipboard-check">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Total Tests</label>
                <p class="text-2xl font-bold text-gray-900">{{ $driver->testings ? $driver->testings->count() : 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Negative</label>
                <p class="text-2xl font-bold text-green-600">{{ $driver->testings ? $driver->testings->where('test_result', 'negative')->count() : 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Positive</label>
                <p class="text-2xl font-bold text-red-600">{{ $driver->testings ? $driver->testings->where('test_result', 'positive')->count() : 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Pass Rate</label>
                <p class="text-2xl font-bold text-gray-900">
                    @if($driver->testings && $driver->testings->count() > 0)
                        {{ round(($driver->testings->where('test_result', 'negative')->count() / $driver->testings->count()) * 100) }}%
                    @else
                        0%
                    @endif
                </p>
            </div>
        </div>
    </x-driver.info-card>



    {{-- Testing Summary --}}
    <x-driver.info-card title="Testing Summary" icon="clipboard-list">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Drug Testing Summary --}}
            <div class="space-y-4">
                <h4 class="font-medium text-gray-900 flex items-center">
                    <x-base.lucide icon="flask-conical" class="w-4 h-4 mr-2 text-blue-600" />
                    Drug Testing
                </h4>
                
                @php
                    // Filter drug tests using actual database values
                    $drugTests = $driver->testings ? $driver->testings->filter(function($test) {
                        return str_contains(strtolower($test->test_type), 'drug');
                    }) : collect();
                    $lastDrugTest = $drugTests->sortByDesc('test_date')->first();
                @endphp
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Last Test Date</span>
                        <span class="text-sm text-gray-900">{{ $lastDrugTest && $lastDrugTest->test_date ? $lastDrugTest->test_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Last Result</span>
                        @if($lastDrugTest && $lastDrugTest->test_result)
                            @php
                                $result = strtolower($lastDrugTest->test_result);
                                $isNegative = in_array($result, ['negative', 'passed']);
                                $isPositive = in_array($result, ['positive', 'failed']);
                            @endphp
                            <span class="@if($isNegative) text-green-600 @elseif($isPositive) text-red-600 @else text-yellow-600 @endif font-medium">
                                {{ ucfirst($lastDrugTest->test_result) }}
                            </span>
                        @else
                            <span class="text-gray-500">N/A</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Tests</span>
                        <span class="text-sm text-gray-900">{{ $drugTests->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Random Pool</span>
                        <div class="flex items-center space-x-2">
                            @if($driver->in_random_pool ?? true)
                                <x-base.lucide icon="check-circle" class="w-4 h-4 text-green-600" />
                                <span class="text-sm text-green-600">Active</span>
                            @else
                                <x-base.lucide icon="x-circle" class="w-4 h-4 text-red-600" />
                                <span class="text-sm text-red-600">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alcohol Testing Summary --}}
            <div class="space-y-4">
                <h4 class="font-medium text-gray-900 flex items-center">
                    <x-base.lucide icon="wine" class="w-4 h-4 mr-2 text-purple-600" />
                    Alcohol Testing
                </h4>
                
                @php
                    // Filter alcohol tests using actual database values
                    $alcoholTests = $driver->testings ? $driver->testings->filter(function($test) {
                        return str_contains(strtolower($test->test_type), 'alcohol');
                    }) : collect();
                    $lastAlcoholTest = $alcoholTests->sortByDesc('test_date')->first();
                @endphp
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Last Test Date</span>
                        <span class="text-sm text-gray-900">{{ $lastAlcoholTest && $lastAlcoholTest->test_date ? $lastAlcoholTest->test_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Last Result</span>
                        @if($lastAlcoholTest && $lastAlcoholTest->test_result)
                            @php
                                $result = strtolower($lastAlcoholTest->test_result);
                                $isNegative = in_array($result, ['negative', 'passed']);
                                $isPositive = in_array($result, ['positive', 'failed']);
                            @endphp
                            <span class="@if($isNegative) text-green-600 @elseif($isPositive) text-red-600 @else text-yellow-600 @endif font-medium">
                                {{ ucfirst($lastAlcoholTest->test_result) }}
                            </span>
                        @else
                            <span class="text-gray-500">N/A</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Total Tests</span>
                        <span class="text-sm text-gray-900">{{ $alcoholTests->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Testing Frequency</span>
                        <span class="text-sm text-gray-900">{{ $driver->alcohol_testing_frequency ?? 'As Required' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Testing History --}}
        @if($driver->testings && $driver->testings->count() > 0)
        <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-4">Recent Testing History</h4>
            <div class="space-y-3">
                @foreach($driver->testings->sortByDesc('test_date')->take(5) as $test)
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if($test->test_type === 'drug')
                                    <x-base.lucide icon="flask-conical" class="w-4 h-4 text-blue-600" />
                                @elseif($test->test_type === 'alcohol')
                                    <x-base.lucide icon="wine" class="w-4 h-4 text-purple-600" />
                                @else
                                    <x-base.lucide icon="shield-alert" class="w-4 h-4 text-gray-600" />
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    @if(isset(\App\Models\Admin\Driver\DriverTesting::getTestTypes()[$test->test_type]))
                                        {{ \App\Models\Admin\Driver\DriverTesting::getTestTypes()[$test->test_type] }}
                                    @else
                                        {{ ucfirst($test->test_type ?? 'N/A') }} Test
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $test->test_date ? $test->test_date->format('M d, Y g:i A') : 'N/A' }}</p>
                                @if($test->administered_by)
                                <p class="text-xs text-gray-500">by {{ $test->administered_by }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                @if($test->test_result)
                                    <span class="@if($test->test_result == 'negative') text-green-600 @elseif($test->test_result == 'positive') text-red-600 @else text-red-600 @endif font-medium text-sm">
                                        {{ ucfirst($test->test_result) }}
                                    </span>
                                @else
                                    <span class="text-yellow-600 text-sm">Pending</span>
                                @endif
                            </div>
                            <div class="flex space-x-1">
                                @if($test->getMedia('drug_test_pdf')->count() > 0)
                                <x-ui.action-button 
                                    href="{{ $test->getMedia('drug_test_pdf')->first()->getUrl() }}" 
                                    icon="file-text" 
                                    variant="primary" 
                                    size="xs"
                                    target="_blank">
                                    Report
                                </x-ui.action-button>
                                @else
                                <x-ui.action-button 
                                    href="#" 
                                    icon="file-text" 
                                    variant="outline" 
                                    size="xs"
                                    onclick="alert('Report will be available once generated')">
                                    Report
                                </x-ui.action-button>
                                @endif
                                
                                @if($test->getMedia('test_results')->count() > 0)
                                <x-ui.action-button 
                                    href="{{ $test->getMedia('test_results')->first()->getUrl() }}" 
                                    icon="download" 
                                    variant="secondary" 
                                    size="xs"
                                    target="_blank">
                                    Results
                                </x-ui.action-button>
                                @else
                                <x-ui.action-button 
                                    href="{{ route('admin.driver-testings.show', $test->id) }}" 
                                    icon="download" 
                                    variant="outline" 
                                    size="xs">
                                    Results
                                </x-ui.action-button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </x-driver.info-card>

    {{-- Road Tests --}}
    @if($driver->roadTests && $driver->roadTests->count() > 0)
    <!-- <x-driver.info-card title="Road Tests" icon="car">
        <div class="space-y-4">
            @foreach($driver->roadTests as $roadTest)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $roadTest->test_type ?? 'Road Test' }}</h4>
                        <p class="text-sm text-gray-600">{{ $roadTest->test_date ? $roadTest->test_date->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <x-ui.status-badge :status="$roadTest->result ?? 'pending'" />
                        @if($roadTest->score)
                        <p class="text-sm text-gray-500 mt-1">Score: {{ $roadTest->score }}%</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Examiner</label>
                        <p class="text-gray-900">{{ $roadTest->examiner ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Vehicle Type</label>
                        <p class="text-gray-900">{{ $roadTest->vehicle_type ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Duration</label>
                        <p class="text-gray-900">{{ $roadTest->duration ?? 'N/A' }} minutes</p>
                    </div>
                </div>

                @if($roadTest->notes)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <label class="text-xs font-medium text-gray-500">Examiner Notes</label>
                    <p class="text-sm text-gray-900">{{ $roadTest->notes }}</p>
                </div>
                @endif

                @if($roadTest->areas_for_improvement)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <label class="text-xs font-medium text-gray-500">Areas for Improvement</label>
                    <p class="text-sm text-gray-900">{{ $roadTest->areas_for_improvement }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </x-driver.info-card> -->
    @endif

    {{-- Knowledge Tests --}}
    @if($driver->knowledgeTests && $driver->knowledgeTests->count() > 0)
    <!-- <x-driver.info-card title="Knowledge Tests" icon="brain">
        <div class="space-y-4">
            @foreach($driver->knowledgeTests as $knowledgeTest)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $knowledgeTest->test_name ?? 'Knowledge Test' }}</h4>
                        <p class="text-sm text-gray-600">{{ $knowledgeTest->test_date ? $knowledgeTest->test_date->format('M d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <x-ui.status-badge :status="$knowledgeTest->result ?? 'pending'" />
                        @if($knowledgeTest->score)
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="text-sm text-gray-500">{{ $knowledgeTest->score }}%</span>
                            <x-ui.progress-bar :percentage="$knowledgeTest->score" size="sm" class="w-16" />
                        </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Questions</label>
                        <p class="text-gray-900">{{ $knowledgeTest->total_questions ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Correct</label>
                        <p class="text-gray-900">{{ $knowledgeTest->correct_answers ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Time Taken</label>
                        <p class="text-gray-900">{{ $knowledgeTest->time_taken ?? 'N/A' }} min</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Attempts</label>
                        <p class="text-gray-900">{{ $knowledgeTest->attempt_number ?? 1 }}</p>
                    </div>
                </div>

                @if($knowledgeTest->weak_areas)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <label class="text-xs font-medium text-gray-500">Areas Needing Improvement</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach(explode(',', $knowledgeTest->weak_areas) as $area)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ trim($area) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </x-driver.info-card> -->
    @endif

    {{-- Upcoming Tests --}}
    <!-- <x-driver.info-card title="Upcoming Tests" icon="calendar-clock">
        @php
            $upcomingTests = collect([
                ['name' => 'Annual Drug Test', 'date' => now()->addDays(7), 'type' => 'Drug Test', 'required' => true],
                ['name' => 'Safety Knowledge Test', 'date' => now()->addDays(21), 'type' => 'Knowledge Test', 'required' => true],
                ['name' => 'Defensive Driving Assessment', 'date' => now()->addDays(35), 'type' => 'Road Test', 'required' => false],
            ]);
        @endphp

        @if($upcomingTests->count() > 0)
        <div class="space-y-3">
            @foreach($upcomingTests as $test)
            <div class="flex items-center justify-between p-3 {{ $test['required'] ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-200' }} border rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        @if($test['type'] === 'Drug Test')
                            <x-base.lucide icon="flask-conical" class="w-5 h-5 {{ $test['required'] ? 'text-red-600' : 'text-blue-600' }}" />
                        @elseif($test['type'] === 'Knowledge Test')
                            <x-base.lucide icon="brain" class="w-5 h-5 {{ $test['required'] ? 'text-red-600' : 'text-blue-600' }}" />
                        @else
                            <x-base.lucide icon="car" class="w-5 h-5 {{ $test['required'] ? 'text-red-600' : 'text-blue-600' }}" />
                        @endif
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $test['name'] }}</h4>
                        <p class="text-sm text-gray-600">
                            {{ $test['date']->format('M d, Y') }} • 
                            {{ $test['required'] ? 'Required' : 'Optional' }}
                        </p>
                    </div>
                </div>
                <x-ui.action-button 
                    href="#" 
                    icon="calendar-plus" 
                    variant="{{ $test['required'] ? 'primary' : 'secondary' }}" 
                    size="sm">
                    Schedule
                </x-ui.action-button>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <x-base.lucide icon="calendar-x" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500">No upcoming tests scheduled</p>
        </div>
        @endif
    </x-driver.info-card> -->
</div>