@props([
    'steps' => [],
    'currentStep' => 1,
    'completedSteps' => [],
    'showLabels' => true,
    'size' => 'md' // sm, md, lg
])

@php
    $sizeClasses = [
        'sm' => [
            'container' => 'py-4',
            'step' => 'w-8 h-8 text-sm',
            'line' => 'h-0.5',
            'label' => 'text-xs mt-2'
        ],
        'md' => [
            'container' => 'py-6',
            'step' => 'w-10 h-10 text-base',
            'line' => 'h-1',
            'label' => 'text-sm mt-3'
        ],
        'lg' => [
            'container' => 'py-8',
            'step' => 'w-12 h-12 text-lg',
            'line' => 'h-1.5',
            'label' => 'text-base mt-4'
        ]
    ];
    
    $classes = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full ' . $classes['container']]) }}>
    <div class="flex items-center justify-between">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isCompleted = in_array($stepNumber, $completedSteps);
                $isCurrent = $stepNumber === $currentStep;
                $isUpcoming = $stepNumber > $currentStep && !$isCompleted;
            @endphp
            
            <div class="flex flex-col items-center flex-1">
                <!-- Step Circle -->
                <div class="relative flex items-center justify-center {{ $classes['step'] }} rounded-full border-2 transition-all duration-300
                    @if($isCompleted)
                        bg-gradient-to-r from-green-500 to-green-600 border-green-500 text-white shadow-lg
                    @elseif($isCurrent)
                        bg-gradient-to-r from-theme-1 to-theme-2 border-primary text-white shadow-lg ring-4 ring-primary/20
                    @else
                        bg-white border-slate-300 text-slate-400 hover:border-slate-400
                    @endif">
                    
                    @if($isCompleted)
                        <!-- Checkmark Icon -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <!-- Step Number -->
                        <span class="font-semibold">{{ $stepNumber }}</span>
                    @endif
                    
                    @if($isCurrent)
                        <!-- Pulse Animation -->
                        <div class="absolute inset-0 rounded-full bg-primary animate-ping opacity-25"></div>
                    @endif
                </div>
                
                @if($showLabels)
                    <!-- Step Label -->
                    <div class="{{ $classes['label'] }} font-medium text-center max-w-24 leading-tight
                        @if($isCompleted)
                            text-green-600
                        @elseif($isCurrent)
                            text-primary
                        @else
                            text-slate-500
                        @endif">
                        {{ $step['label'] ?? $step['title'] ?? "Step {$stepNumber}" }}
                    </div>
                    
                    @if(isset($step['description']) && ($isCurrent || $isCompleted))
                        <div class="text-xs text-slate-400 text-center mt-1 max-w-32">
                            {{ $step['description'] }}
                        </div>
                    @endif
                @endif
            </div>
            
            @if(!$loop->last)
                <!-- Connection Line -->
                <div class="flex-1 mx-4">
                    <div class="{{ $classes['line'] }} rounded-full transition-all duration-300
                        @if($stepNumber < $currentStep || $isCompleted)
                            bg-gradient-to-r from-green-500 to-green-600
                        @elseif($stepNumber === $currentStep)
                            bg-gradient-to-r from-primary to-theme-2
                        @else
                            bg-slate-200
                        @endif">
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    
    <!-- Progress Bar (Optional) -->
    @if(isset($showProgressBar) && $showProgressBar)
        <div class="mt-6">
            <div class="flex justify-between text-sm text-slate-600 mb-2">
                <span>Progress</span>
                <span>{{ round((count($completedSteps) / count($steps)) * 100) }}%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-theme-1 to-theme-2 h-2 rounded-full transition-all duration-500" 
                     style="width: {{ (count($completedSteps) / count($steps)) * 100 }}%"></div>
            </div>
        </div>
    @endif
</div>

<style>
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
    
    .animate-ping {
        animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    
    @keyframes ping {
        75%, 100% {
            transform: scale(2);
            opacity: 0;
        }
    }
</style>