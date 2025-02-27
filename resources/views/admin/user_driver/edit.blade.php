@extends('../themes/' . $activeTheme)
@section('title', 'Edit User Driver')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers', 'url' => route('admin.carrier.user_drivers.index', $carrier->slug)],
        ['label' => 'Edit Driver', 'active' => true],
    ];

    // Misma configuración de pasos que en create
    $steps = [
        \App\Services\Admin\DriverStepService::STEP_GENERAL => ['label' => 'general', 'title' => 'General Information'],
        \App\Services\Admin\DriverStepService::STEP_LICENSES => ['label' => 'licenses', 'title' => 'Licenses & Experience'],
        \App\Services\Admin\DriverStepService::STEP_MEDICAL => ['label' => 'medical', 'title' => 'Medical Information'],
        \App\Services\Admin\DriverStepService::STEP_TRAINING => ['label' => 'training', 'title' => 'Training History'],
        \App\Services\Admin\DriverStepService::STEP_TRAFFIC => ['label' => 'traffic', 'title' => 'Traffic Record'],
        \App\Services\Admin\DriverStepService::STEP_ACCIDENT => ['label' => 'accident', 'title' => 'Accident History'],
    ];

    // Obtener estado de los pasos
    $stepsStatus = app(\App\Services\Admin\DriverStepService::class)->getStepsStatus($userDriverDetail);
    
    // Obtener el paso actual
    $currentStep = $userDriverDetail->current_step ?: \App\Services\Admin\DriverStepService::STEP_GENERAL;
    
    // Calcular completitud
    $completionPercentage = app(\App\Services\Admin\DriverStepService::class)->calculateCompletionPercentage($userDriverDetail);
    
    // Determinar la pestaña activa (puede venir de la URL)
    $activeTab = request()->query('active_tab', $steps[$currentStep]['label']);
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">
                    <div class="box-body">
                        <form action="{{ route('admin.carrier.user_drivers.update', ['carrier' => $carrier, 'userDriverDetail' => $userDriverDetail]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <x-validation-errors class="my-4" />

                            {{-- Contenedor Alpine --}}
                            <div x-data="{
                                activeTab: '{{ $activeTab }}',
                                submissionType: 'partial',
                                // Resto de la lógica Alpine...
                            }">

                                {{-- Progress Bar and Steps Indicator --}}
                                <div class="mb-8">
                                    <div class="flex justify-between items-center mb-2">
                                        <h2 class="text-lg font-semibold">Driver Registration Progress</h2>
                                        <span class="text-sm text-gray-600">{{ $completionPercentage }}% Complete</span>
                                    </div>

                                    {{-- Progress Bar --}}
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                                        <div class="bg-primary h-2.5 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                                    </div>

                                    {{-- Steps Indicators --}}
                                    <div class="grid grid-cols-3 md:grid-cols-6 gap-4">
                                        @foreach ($steps as $step => $details)
                                            <x-driver.step-indicator 
                                                :status="$stepsStatus[$step]" 
                                                :step="$step" 
                                                :activeStep="$currentStep"
                                                :label="$details['label']" 
                                            />
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Hidden Inputs --}}
                                <input type="hidden" name="active_tab" :value="activeTab">
                                <input type="hidden" name="submission_type" :value="submissionType">
                                <input type="hidden" name="user_id" value="{{ $userDriverDetail->user_id }}">

                                {{-- El resto de la vista similar a create --}}
                                {{-- ... --}}
                                
                                {{-- Botones de navegación y submit --}}
                                <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-between mt-6">
                                    <div>
                                        <button type="button" @click="activeTab = getPreviousTab()"
                                            x-show="!isFirstTab()"
                                            class="border border-gray-300 px-4 py-2 rounded text-gray-600 hover:bg-gray-100">
                                            <i class="fas fa-arrow-left mr-1"></i> Previous
                                        </button>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.carrier.user_drivers.index', $carrier) }}"
                                            class="border border-gray-300 px-4 py-2 rounded text-gray-600 hover:bg-gray-100">
                                            Cancel
                                        </a>
                                        
                                        <button type="submit" @click="submissionType = 'partial'"
                                            class="border border-primary/50 px-4 py-2 rounded text-primary hover:text-white hover:bg-primary transition">
                                            <span x-show="isLastTab()">Save</span>
                                            <span x-show="!isLastTab()">Save & Continue <i class="fas fa-arrow-right ml-1"></i></span>
                                        </button>
                                        
                                        <button type="submit" @click="submissionType = 'complete'"
                                            class="border border-success px-4 py-2 rounded text-success hover:text-white hover:bg-success transition">
                                            Save & Finish
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function initDriverForm() {
    return {
        activeTab: '{{ $activeTab }}',
        submissionType: 'partial',
        
        // Funciones de navegación entre tabs
        isFirstTab() {
            return this.activeTab === 'general';
        },
        
        isLastTab() {
            return this.activeTab === 'accident';
        },
        
        getPreviousTab() {
            const tabs = ['general', 'licenses', 'medical', 'training', 'traffic', 'accident'];
            const currentIndex = tabs.indexOf(this.activeTab);
            if (currentIndex > 0) {
                return tabs[currentIndex - 1];
            }
            return 'general';
        },
        
        getNextTab() {
            const tabs = ['general', 'licenses', 'medical', 'training', 'traffic', 'accident'];
            const currentIndex = tabs.indexOf(this.activeTab);
            if (currentIndex < tabs.length - 1) {
                return tabs[currentIndex + 1];
            }
            return 'accident';
        }
    }
}
    </script>
@endpush