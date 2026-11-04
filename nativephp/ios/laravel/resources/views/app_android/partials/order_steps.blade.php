@php
    /** @var array<int, array{key:string,label:string,icon:string,color:string,state:string,date:?string,notes:?string}> $steps */
    $steps = $steps ?? [];
@endphp

@if (count($steps))
    <div class="order-steps" dir="rtl">
        @foreach ($steps as $index => $step)
            @php
                $state = $step['state'];
                $dotColor = [
                    'done' => 'bg-emerald-500 text-white',
                    'current' => 'bg-purple-600 text-white ring-4 ring-purple-100',
                    'rejected' => 'bg-red-500 text-white',
                    'pending' => 'bg-gray-200 text-gray-400',
                ][$state] ?? 'bg-gray-200 text-gray-400';

                $labelClass = [
                    'done' => 'text-emerald-700',
                    'current' => 'text-purple-700 font-bold',
                    'rejected' => 'text-red-600 font-bold',
                    'pending' => 'text-gray-400',
                ][$state] ?? 'text-gray-400';
            @endphp

            <div class="flex gap-3 {{ $index < count($steps) - 1 ? 'pb-4' : '' }}">
                {{-- الخط الواصل --}}
                <div class="flex flex-col items-center flex-shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $dotColor }} transition">
                        <i class="fas {{ $state === 'done' ? 'fa-check' : $step['icon'] }} text-xs"></i>
                    </div>
                    @if ($index < count($steps) - 1)
                        <div class="w-0.5 flex-1 mt-1 {{ in_array($steps[$index + 1]['state'], ['done', 'current'], true) ? 'bg-purple-200' : 'bg-gray-100' }}"></div>
                    @endif
                </div>

                {{-- النص --}}
                <div class="flex-1 min-w-0 pb-1">
                    <div class="text-sm {{ $labelClass }}">{{ $step['label'] }}</div>
                    @if (! empty($step['date']))
                        <div class="text-[11px] text-gray-400 mt-0.5">
                            <i class="far fa-clock ml-1"></i>{{ $step['date'] }}
                        </div>
                    @endif
                    @if (! empty($step['notes']) && $state !== 'pending')
                        <div class="text-[11px] text-gray-500 mt-1 bg-gray-50 rounded-lg px-2 py-1">{{ $step['notes'] }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
