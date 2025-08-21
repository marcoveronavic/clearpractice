{{-- Demo-aware sidebar --}}
@php
    $prefix = request()->is('demo/*') ? '/demo' : '';
    $isActive = function (string $uri) {
        $p = trim($uri, '/');
        return request()->is($p) || request()->is($p.'/*')
            ? 'bg-gray-900 text-white'
            : 'text-gray-900 hover:bg-gray-100';
    };
@endphp

<nav class="w-56 border-r border-gray-200 p-3">
    <div class="font-semibold text-gray-900 mb-2 px-2">clearpractice</div>
    <ul class="space-y-1">
        <li><a href="{{ $prefix }}/ch"         class="block px-4 py-2 rounded-md {{ $isActive($prefix.'/ch') }}">CH Search</a></li>
        <li><a href="{{ $prefix }}/companies"  class="block px-4 py-2 rounded-md {{ $isActive($prefix.'/companies') }}">Companies</a></li>
        <li><a href="{{ $prefix }}/clients"    class="block px-4 py-2 rounded-md {{ $isActive($prefix.'/clients') }}">Clients</a></li>
        <li><a href="{{ $prefix }}/tasks"      class="block px-4 py-2 rounded-md {{ $isActive($prefix.'/tasks') }}">Tasks</a></li>
        <li><a href="{{ $prefix }}/users"      class="block px-4 py-2 rounded-md {{ $isActive($prefix.'/users') }}">Users</a></li>
        <li><a href="{{ $prefix }}/deadlines"  class="block px-4 py-2 rounded-md {{ $isActive($prefix.'/deadlines') }}">Deadlines</a></li>
    </ul>
</nav>
