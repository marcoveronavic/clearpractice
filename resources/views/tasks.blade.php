@extends('layouts.app')

@section('content')
  <h1>Tasks</h1>

  @if (session('success')) <div class="flash ok">{{ session('success') }}</div> @endif
  @if (session('error'))   <div class="flash err">{{ session('error') }}</div>   @endif

  <div class="card">
    {{-- Form: Title • Assigned by • Assign to • Due • Status --}}
    <form
      style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:10px;align-items:center"
      action="{{ route('tasks.store') }}" method="POST">
      @csrf
      <input type="text" name="title" placeholder="Task title" required>

      {{-- NEW: Assigned by (dropdown of users) --}}
      <select name="assigned_by_id">
        <option value="">— Assigned by —</option>
        @foreach (($users ?? []) as $u)
          <option value="{{ $u['id'] }}">{{ $u['name'] }}{{ !empty($u['surname']) ? ' '.$u['surname'] : '' }}{{ !empty($u['email']) ? ' ('.$u['email'].')' : '' }}</option>
        @endforeach
      </select>

      {{-- Existing: Assign to --}}
      <select name="assigned_to_id">
        <option value="">— Assign to —</option>
        @foreach (($users ?? []) as $u)
          <option value="{{ $u['id'] }}">{{ $u['name'] }}{{ !empty($u['surname']) ? ' '.$u['surname'] : '' }}{{ !empty($u['email']) ? ' ('.$u['email'].')' : '' }}</option>
        @endforeach
      </select>

      <input type="date" name="due_date" placeholder="dd/mm/yyyy">

      <select name="status">
        <option value="todo">To do</option>
        <option value="in-progress">In progress</option>
        <option value="done">Done</option>
      </select>

      <button class="btn" type="submit">Create task</button>
    </form>

    <table style="margin-top:10px">
      <thead>
        <tr>
          <th>Title</th>
          <th>Assigned by</th>
          <th>Assigned</th>
          <th>Due</th>
          <th>Status</th>
          <th>Created</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($tasks as $t)
        <tr>
          <td><strong>{{ $t['title'] }}</strong></td>

          {{-- NEW: show Assigned by --}}
          <td>
            @if (!empty($t['assigned_by']['name']))
              <span class="pill">{{ trim(($t['assigned_by']['name'] ?? '').' '.($t['assigned_by']['surname'] ?? '')) }}</span>
              <span class="muted">{{ $t['assigned_by']['email'] ?? '' }}</span>
            @else
              <span class="muted">—</span>
            @endif
          </td>

          {{-- Assigned to --}}
          <td>
            @if (!empty($t['assigned']['name']))
              <span class="pill">{{ trim(($t['assigned']['name'] ?? '').' '.($t['assigned']['surname'] ?? '')) }}</span>
              <span class="muted">{{ $t['assigned']['email'] ?? '' }}</span>
            @else
              <span class="muted">Unassigned</span>
            @endif
          </td>

          <td>{{ $t['due_date'] ?? '' }}</td>
          <td>{{ $t['status'] ?? '' }}</td>
          <td class="muted">{{ $t['created'] ?? '' }}</td>
          <td>
            <form action="{{ route('tasks.destroy', ['id' => $t['id']]) }}" method="POST" onsubmit="return confirm('Delete this task?');">
              @csrf @method('DELETE')
              <button class="btn danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="muted">No tasks yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
@endsection
