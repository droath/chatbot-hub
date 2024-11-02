@props([
    'icon' => 'heroicon-o-user-circle',
    'position' => 'left',
])

<li class="m-4 flex items-start gap-x-2 sm:gap-x-4">
    @if ($icon)
        <div
            @class([
                'h-7 w-7 flex-shrink-0',
                'order-last' => $position == 'right',
            ])
        >
            @svg($icon, ['class' => 'h-full w-full'])
        </div>
    @endif

    <div
        @class([
            $position == 'right' ? 'ms-auto bg-blue-600' : 'bg-gray-600',
            'text-md max-w-lg space-y-3 overflow-x-scroll rounded-2xl p-4 text-white',
        ])
    >
        {{ $slot }}
    </div>
</li>
