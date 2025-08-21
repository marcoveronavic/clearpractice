import AppLayout from '@/Layouts/AppLayout';
import { useMemo } from 'react';

function IndividualsIndex({ people = [] }) {
  const rows = useMemo(() => people ?? [], [people]);

  return (
    <div className="p-6 space-y-6">
      <h1 className="text-2xl font-semibold">Individuals</h1>

      <div className="overflow-x-auto border rounded-lg">
        <table className="min-w-full text-sm">
          <thead className="bg-gray-50">
            <tr>
              <th className="text-left px-4 py-2">ID</th>
              <th className="text-left px-4 py-2">First name</th>
              <th className="text-left px-4 py-2">Last name</th>
              <th className="text-left px-4 py-2">Email</th>
            </tr>
          </thead>
          <tbody>
            {rows.map(p => (
              <tr key={p.id} className="border-t">
                <td className="px-4 py-2">{p.id}</td>
                <td className="px-4 py-2">{p.first_name}</td>
                <td className="px-4 py-2">{p.last_name}</td>
                <td className="px-4 py-2">{p.email}</td>
              </tr>
            ))}
            {!rows.length && (
              <tr>
                <td className="px-4 py-6 text-gray-500" colSpan={4}>No individuals yet.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

IndividualsIndex.layout = page => <AppLayout children={page} />;
export default IndividualsIndex;
