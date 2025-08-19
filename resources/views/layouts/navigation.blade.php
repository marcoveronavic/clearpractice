@php
  // Practice context set after Add Users / Accept Invite
  $practiceSlug  = session('practice_slug');          // e.g. "fidcorp-ltd"
  $practiceToken = session('practice_token');         // used to open Add Users
  $base          = $practiceSlug ? "/practice/{$practiceSlug}" : null;

  // small helpers for active state (optional)
  $isActive = function(string $pattern): string {
      return request()->is($pattern) ? 'font-semibold' : '';
  };
@endphp

<aside class="sidebar">
  <ul class="space-y-2 px-3 py-4">
    <li>
      <a href="/ch" class="block py-2">CH Search</a>
    </li>

    <li>
      <a href="{{ $base ? $base.'/companies' : '/landing/companies' }}"
         class="block py-2 {{ $isActive('practice/*/companies') }}">
        Companies
      </a>
    </li>

    <li>
      <!-- Practice Clients page not built yet -> go to practice home if exists, else old page -->
      <a href="{{ $base ?: '/landing/clients' }}"
         class="block py-2 {{ $isActive('practice/*') }}">
        Clients
      </a>
    </li>

    <li>
      <!-- Practice Tasks page not built yet -> go to practice home if exists, else old page -->
      <a href="{{ $base ?: '/landing/tasks' }}" class="block py-2">Tasks</a>
    </li>

    <li>
      <!-- Users = Add Users screen of current practice; fallback to old page if no practice yet -->
      <a href="{{ $practiceToken ? url('/lead/add-users?t='.$practiceToken) : '/landing/users' }}"
         class="block py-2">
        Users
      </a>
    </li>

    <li>
      <!-- Deadlines not built yet -> practice home if exists, else old page -->
      <a href="{{ $base ?: '/landing/deadlines' }}" class="block py-2">Deadlines</a>
    </li>
  </ul>
</aside>
