import { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function Sidebar({ width = 260 }) {
  const [open, setOpen] = useState(false);
  const { url } = usePage(); // current path (e.g. /companies)

  useEffect(() => {
    const main = document.getElementById('app-main');
    if (!main) return;
    main.style.transition = 'margin-left 200ms ease';
    main.style.marginLeft = open ? `${width}px` : '0px';
  }, [open, width]);

  const NavLink = ({ href, children }) => {
    const active = url.startsWith(href);
    return (
      <Link
        href={href}
        className={`block px-4 py-2 rounded-md ${active ? 'bg-gray-200 font-medium' : 'hover:bg-gray-100'}`}
        onClick={() => setOpen(false)}
      >
        {children}
      </Link>
    );
  };

  return (
    <>
      <button
        type="button"
        aria-label="Toggle menu"
        className="fixed top-3 left-3 z-40 rounded-lg border px-3 py-1 bg-white"
        onClick={() => setOpen(o => !o)}
      >
        ☰
      </button>

      <aside
        className="fixed top-0 left-0 h-full bg-white border-r z-30"
        style={{
          width: `${width}px`,
          transform: open ? 'translateX(0)' : `translateX(-${width}px)`,
          transition: 'transform 200ms ease',
        }}
      >
        <div className="px-4 py-3 border-b font-semibold">Menu</div>
        <nav className="p-3 space-y-1 text-sm">
          <NavLink href="/companies">Companies</NavLink>
          <NavLink href="/deadlines">Deadlines</NavLink>
          <NavLink href="/individuals">Individuals</NavLink> {/* NEW */}
        </nav>
      </aside>
    </>
  );
}
