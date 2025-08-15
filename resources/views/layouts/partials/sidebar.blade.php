<button id="sidebar-toggle"
        class="fixed top-3 left-3 z-40 rounded-lg border px-3 py-1 bg-white"
        aria-label="Toggle menu">☰</button>

<aside id="sidebar"
       class="fixed top-0 left-0 h-full bg-white border-r z-30"
       style="width:260px; transform:translateX(-260px); transition:transform 200ms ease;">
  <div class="px-4 py-3 border-b font-semibold">Menu</div>
  <nav class="p-3 space-y-1 text-sm">
    <a href="{{ route('companies.index') }}" class="block px-4 py-2 rounded-md hover:bg-gray-100">Companies</a>
    <a href="/deadlines" class="block px-4 py-2 rounded-md hover:bg-gray-100">Deadlines</a>
    <a href="{{ route('individuals.index') }}" class="block px-4 py-2 rounded-md hover:bg-gray-100">Individuals</a> {{-- NEW --}}
  </nav>
</aside>

<script>
  (function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    const main = document.getElementById('app-main');

    function setOpen(open) {
      sidebar.style.transform = open ? 'translateX(0)' : 'translateX(-260px)';
      if (main) {
        main.style.transition = 'margin-left 200ms ease';
        main.style.marginLeft = open ? '260px' : '0px';
      }
      localStorage.setItem('sidebar-open', open ? '1' : '0');
    }

    toggle.addEventListener('click', () => {
      const isClosed = sidebar.style.transform.includes('(-260px)');
      setOpen(isClosed);
    });

    setOpen(localStorage.getItem('sidebar-open') === '1');
  })();
</script>
