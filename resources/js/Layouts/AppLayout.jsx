import Sidebar from '@/components/Sidebar';

export default function AppLayout({ children }) {
  return (
    <div>
      <Sidebar />
      <main id="app-main" className="min-h-screen bg-white">{children}</main>
    </div>
  );
}
