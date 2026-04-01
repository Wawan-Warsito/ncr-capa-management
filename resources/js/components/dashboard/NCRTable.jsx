import React from 'react';
import { Link } from 'react-router-dom';
import NCRStatusBadge from '../NCRStatusBadge';

const NCRTable = ({ ncrs }) => {
  const getValue = (obj, candidates, fallback = '-') => {
    for (const c of candidates) {
      if (obj && obj[c] !== undefined && obj[c] !== null && obj[c] !== '') return obj[c];
    }
    return fallback;
  };

  const getDeptName = (dept) => {
    if (!dept) return null;
    return dept.department_name || dept.departmentName || dept.name || null;
  };

  const formatDate = (value) => {
    if (!value) return '-';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '-';
    return d.toLocaleDateString();
  };

  return (
    <div className="bg-white shadow overflow-hidden sm:rounded-lg">
      <div className="px-4 py-5 sm:px-6 flex justify-between items-center">
        <h3 className="text-lg leading-6 font-medium text-gray-900">Recent NCRs</h3>
        <Link to="/ncrs" className="text-sm font-medium text-blue-600 hover:text-blue-500">
          View all
        </Link>
      </div>
      <div className="border-t border-gray-200">
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  NCR No
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Defect
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Department
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" className="relative px-6 py-3">
                  <span className="sr-only">View</span>
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {ncrs && ncrs.length > 0 ? (
                ncrs.map((ncr) => (
                  <tr key={ncr.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {getValue(ncr, ['ncrNumber', 'ncr_number'])}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">
                      {getValue(ncr, ['defectDescription', 'defect_description'])}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {(() => {
                        const finderDept = getDeptName(ncr.finderDepartment || ncr.finder_department);
                        const receiverDept = getDeptName(ncr.receiverDepartment || ncr.receiver_department);
                        if (finderDept && receiverDept) return `${finderDept} → ${receiverDept}`;
                        return (
                          finderDept ||
                          receiverDept ||
                          getDeptName(ncr.department) ||
                          getDeptName(ncr.department_data) ||
                          'N/A'
                        );
                      })()}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {formatDate(getValue(ncr, ['issuedDate', 'issued_date', 'dateFound', 'date_found', 'createdAt', 'created_at'], null))}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <NCRStatusBadge status={getValue(ncr, ['status'], '')} />
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <Link to={`/ncrs/${ncr.id}`} className="text-blue-600 hover:text-blue-900">
                        View
                      </Link>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan="6" className="px-6 py-4 text-center text-sm text-gray-500">
                    No recent NCRs found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default NCRTable;
