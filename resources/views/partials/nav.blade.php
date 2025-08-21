{{-- Demo-aware top nav --}}
@php
    $prefix = request()->is('demo/*') ? '/demo' : '';
    $isActive = function (string $uri) {
        $p = trim($uri, '/');
        return request()->is($p) || request()->is($p.'/*')
            ? 'font-semibold text-gray-900'
            : 'text-gray-600 hover:text-gray-900';
    };
@endphp

<nav class="flex items-center gap-4 px-4 py-2 border-b border-gray-200 bg-white">
    <a href="{{ $prefix }}/ch"         class="{{ $isActive($prefix.'/ch') }}">CH Search</a>
    <a href="{{ $prefix }}/companies"  class="{{ $isActive($prefix.'/companies') }}">Companies</a>
    <a href="{{ $prefix }}/clients"    class="{{ $isActive($prefix.'/clients') }}">Clients</a>
    <a href="{{ $prefix }}/tasks"      class="{{ $isActive($prefix.'/tasks') }}">Tasks</a>
    <a href="{{ $prefix }}/users"      class="{{ $isActive($prefix.'/users') }}">Users</a>
    <a href="{{ $prefix }}/deadlines"  class="{{ $isActive($prefix.'/deadlines') }}">Deadlines</a>
</nav>
