{{-- Training Tab --}}
<div class="space-y-6">
    {{-- Training Overview --}}
    <!-- <x-driver.info-card title="Training Overview" icon="graduation-cap">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Total Courses</label>
                <p class="text-2xl font-bold text-gray-900">{{ $driver->trainingRecords ? $driver->trainingRecords->count() : 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Completed</label>
                <p class="text-2xl font-bold text-green-600">{{ $driver->trainingRecords ? $driver->trainingRecords->where('status', 'completed')->count() : 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">In Progress</label>
                <p class="text-2xl font-bold text-blue-600">{{ $driver->trainingRecords ? $driver->trainingRecords->where('status', 'in_progress')->count() : 0 }}</p>
            </div>
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-500">Completion Rate</label>
                <p class="text-2xl font-bold text-gray-900">
                    @if($driver->trainingRecords && $driver->trainingRecords->count() > 0)
                        {{ round(($driver->trainingRecords->where('status', 'completed')->count() / $driver->trainingRecords->count()) * 100) }}%
                    @else
                        0%
                    @endif
                </p>
            </div>
        </div>
    </x-driver.info-card> -->

    {{-- Required Training --}}
    <!-- <x-driver.info-card title="Required Training" icon="shield-check">
        <div class="space-y-4">
            @php
                $requiredTraining = [
                    ['name' => 'DOT Safety Training', 'status' => $driver->dot_safety_training ?? 'pending', 'due_date' => $driver->dot_safety_due_date ?? null],
                    ['name' => 'Hazmat Training', 'status' => $driver->hazmat_training ?? 'pending', 'due_date' => $driver->hazmat_due_date ?? null],
                    ['name' => 'Defensive Driving', 'status' => $driver->defensive_driving ?? 'pending', 'due_date' => $driver->defensive_driving_due_date ?? null],
                    ['name' => 'First Aid/CPR', 'status' => $driver->first_aid_training ?? 'pending', 'due_date' => $driver->first_aid_due_date ?? null],
                ];
            @endphp

            @foreach($requiredTraining as $training)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @if($training['status'] === 'completed')
                                <x-base.lucide icon="check-circle" class="w-5 h-5 text-green-600" />
                            @elseif($training['status'] === 'in_progress')
                                <x-base.lucide icon="clock" class="w-5 h-5 text-blue-600" />
                            @else
                                <x-base.lucide icon="alert-circle" class="w-5 h-5 text-yellow-600" />
                            @endif
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900">{{ $training['name'] }}</h4>
                            @if($training['due_date'])
                            <p class="text-sm text-gray-500">
                                Due: {{ $training['due_date']->format('M d, Y') }}
                                @if($training['due_date']->isPast() && $training['status'] !== 'completed')
                                    <span class="text-red-600 font-medium">(Overdue)</span>
                                @endif
                            </p>
                            @endif
                        </div>
                    </div>
                    <x-ui.status-badge :status="$training['status']" />
                </div>
            </div>
            @endforeach
        </div>
    </x-driver.info-card> -->

    {{-- Training Schools --}}
    @if($driver->trainingSchools && $driver->trainingSchools->count() > 0)
    <x-driver.info-card title="Training Schools" icon="graduation-cap">
        <div class="space-y-4">
            @foreach($driver->trainingSchools as $school)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $school->school_name ?? 'N/A' }}</h4>
                        <p class="text-sm text-gray-600">
                            {{ $school->city ?? 'N/A' }}, {{ $school->state ?? 'N/A' }}
                            @if($school->date_start && $school->date_end)
                                <span class="mx-1">|</span>
                                {{ $school->date_start->format('M d, Y') }} - {{ $school->date_end->format('M d, Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        @if($school->completed)
                            <x-ui.status-badge status="completed" />
                        @else
                            <x-ui.status-badge status="in_progress" />
                        @endif
                    </div>
                </div>

                {{-- Graduation and Safety Information --}}
                <div class="grid grid-cols-1 md:grid-cols-1 gap-4 text-sm mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Graduation Status</label>
                        <p class="text-sm mt-1">
                            <span class="font-medium">Did you graduate from this program?</span>
                            <span class="ml-2 {{ $school->graduated ? 'text-green-600' : 'text-red-600' }}">
                                {{ $school->graduated ? 'Yes' : 'No' }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Safety Regulations</label>
                        <p class="text-sm mt-1">
                            <span class="font-medium">Subject to FMCSR?</span>
                            <span class="ml-2 {{ $school->subject_to_safety_regulations ? 'text-green-600' : 'text-red-600' }}">
                                {{ $school->subject_to_safety_regulations ? 'Yes' : 'No' }}
                            </span>
                        </p>
                        <p class="text-sm mt-1">
                            <span class="font-medium">Performed safety-sensitive functions?</span>
                            <span class="ml-2 {{ $school->performed_safety_functions ? 'text-green-600' : 'text-red-600' }}">
                                {{ $school->performed_safety_functions ? 'Yes' : 'No' }}
                            </span>
                        </p>
                    </div>
                </div>

                {{-- Training Skills --}}
                @if(isset($school->training_skills) && is_array($school->training_skills) && count($school->training_skills) > 0)
                <div class="mb-3">
                    <label class="text-xs font-medium text-gray-500 uppercase">Skills Trained</label>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($school->training_skills as $skill)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ $skill }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($school->description)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <label class="text-xs font-medium text-gray-500 uppercase">Description</label>
                    <p class="text-sm text-gray-900 mt-1">{{ $school->description }}</p>
                </div>
                @endif

                {{-- School Certificates --}}
                @if($school->getMedia('school_certificates') && $school->getMedia('school_certificates')->count() > 0)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <label class="text-xs font-medium text-gray-500 uppercase">Certificates</label>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($school->getMedia('school_certificates') as $certificate)
                        <x-ui.action-button 
                            href="{{ $certificate->getUrl() }}" 
                            icon="file-text" 
                            variant="secondary" 
                            size="sm"
                            target="_blank">
                            {{ Str::limit($certificate->file_name, 20) }}
                        </x-ui.action-button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </x-driver.info-card>
    @else
    <x-driver.info-card title="Training Schools" icon="graduation-cap">
        <div class="text-center py-8">
            <x-base.lucide icon="graduation-cap" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500">No training schools information available</p>
        </div>
    </x-driver.info-card>
    @endif

    {{-- Driver Courses --}}
    @if($driver->courses && $driver->courses->count() > 0)
    <x-driver.info-card title="Driver Courses" icon="book-open">
        <div class="space-y-4">
            @foreach($driver->courses as $course)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $course->organization_name ?? 'N/A' }}</h4>
                        <p class="text-sm text-gray-600">{{ $course->city ?? 'N/A' }}, {{ $course->state ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        @if($course->completed)
                            <x-ui.status-badge status="completed" />
                        @else
                            <x-ui.status-badge status="in_progress" />
                        @endif
                    </div>
                </div>

                {{-- Certification Details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Certification Details</label>
                        @if($course->certification_date)
                        <p class="text-sm mt-1">
                            <span class="font-medium">Certification Date:</span>
                            <span class="ml-2">{{ $course->certification_date->format('M d, Y') }}</span>
                        </p>
                        @endif
                        @if($course->expiration_date)
                        <p class="text-sm mt-1">
                            <span class="font-medium">Expiration Date:</span>
                            <span class="ml-2">{{ $course->expiration_date->format('M d, Y') }}</span>
                        </p>
                        @endif
                    </div>
                    <div>
                        @if($course->experience)
                        <label class="text-xs font-medium text-gray-500 uppercase">Experience</label>
                        <p class="text-sm mt-1">{{ $course->experience }}</p>
                        @endif
                    </div>
                </div>

                @if($course->description)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <label class="text-xs font-medium text-gray-500 uppercase">Description</label>
                    <p class="text-sm text-gray-900 mt-1">{{ $course->description }}</p>
                </div>
                @endif

                {{-- Course Certificates --}}
                @if($course->getMedia('course_certificates') && $course->getMedia('course_certificates')->count() > 0)
                <div class="mt-3 pt-3 border-t border-gray-200">
                    <label class="text-xs font-medium text-gray-500 uppercase">Certificates</label>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($course->getMedia('course_certificates') as $certificate)
                        <x-ui.action-button 
                            href="{{ $certificate->getUrl() }}" 
                            icon="file-text" 
                            variant="secondary" 
                            size="sm"
                            target="_blank">
                            {{ Str::limit($certificate->file_name, 20) }}
                        </x-ui.action-button>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </x-driver.info-card>
    @else
    <x-driver.info-card title="Driver Courses" icon="book-open">
        <div class="text-center py-8">
            <x-base.lucide icon="book-open" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500">No courses information available</p>
        </div>
    </x-driver.info-card>
    @endif
    {{-- Training Calendar --}}
    <!-- <x-driver.info-card title="Upcoming Training" icon="calendar-days">
        @php
            $upcomingTraining = collect([
                ['name' => 'Annual Safety Refresher', 'date' => now()->addDays(15), 'type' => 'Required'],
                ['name' => 'Defensive Driving Course', 'date' => now()->addDays(30), 'type' => 'Required'],
                ['name' => 'Customer Service Training', 'date' => now()->addDays(45), 'type' => 'Optional'],
            ]);
        @endphp

        @if($upcomingTraining->count() > 0)
        <div class="space-y-3">
            @foreach($upcomingTraining as $training)
            <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <x-base.lucide icon="calendar" class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $training['name'] }}</h4>
                        <p class="text-sm text-gray-600">{{ $training['date']->format('M d, Y') }} • {{ $training['type'] }}</p>
                    </div>
                </div>
                <x-ui.action-button 
                    href="#" 
                    icon="external-link" 
                    variant="primary" 
                    size="sm">
                    Register
                </x-ui.action-button>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <x-base.lucide icon="calendar-x" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
            <p class="text-gray-500">No upcoming training scheduled</p>
        </div>
        @endif
    </x-driver.info-card> -->
</div>