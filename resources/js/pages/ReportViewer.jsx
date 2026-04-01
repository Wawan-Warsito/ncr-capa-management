import React, { useState, useEffect } from 'react';
import { useParams, useSearchParams } from 'react-router-dom';
import Breadcrumb from '../components/Breadcrumb';
import api from '../services/api';

const ReportViewer = () => {
  const { type } = useParams();
  const [searchParams] = useSearchParams();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      try {
        setError('');
        const params = new URLSearchParams(searchParams.toString());
        if (params.has('start_date') && !params.has('date_from')) {
          params.set('date_from', params.get('start_date'));
          params.delete('start_date');
        }
        if (params.has('end_date') && !params.has('date_to')) {
          params.set('date_to', params.get('end_date'));
          params.delete('end_date');
        }

        let endpoint = '';
        if (type === 'ncr-summary') endpoint = '/reports/ncr';
        else if (type === 'capa-effectiveness') endpoint = '/reports/capa';
        else if (type === 'department-performance') endpoint = '/reports/department-performance';
        else if (type === 'pareto-analysis') endpoint = '/reports/pareto';
        else endpoint = '/reports/summary';

        const response = await api.get(`${endpoint}?${params.toString()}`);
        setData(response.data?.data ?? response.data);
      } catch (err) {
        const message = err?.response?.data?.message || err?.message || 'Failed to generate report.';
        setError(message);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [type, searchParams]);

  const formatDate = (value) => {
    if (!value) return '-';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '-';
    return d.toLocaleDateString('en-GB');
  };

  const renderTable = () => {
    if (!data) return <div className="p-4 text-center">No data found matching criteria.</div>;

    if (type === 'ncr-summary') {
      if (!Array.isArray(data) || data.length === 0) return <div className="p-4 text-center">No data found matching criteria.</div>;
      return (
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NCR No</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {data.map((row, idx) => (
              <tr key={idx}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{row.ncr_number || row.ncrNumber || '-'}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{formatDate(row.issued_date || row.date_found || row.created_at || row.createdAt)}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {row.finder_department?.department_name || row.finderDepartment?.department_name || row.receiver_department?.department_name || row.receiverDepartment?.department_name || '-'}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{row.status || '-'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      );
    }

    if (type === 'capa-effectiveness') {
      if (!Array.isArray(data) || data.length === 0) return <div className="p-4 text-center">No data found matching criteria.</div>;
      return (
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CAPA ID</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NCR No</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Effectiveness</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {data.map((row, idx) => (
              <tr key={idx}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{row.id ?? '-'}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{row.ncr?.ncr_number || row.ncr?.ncrNumber || '-'}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{row.current_status || row.currentStatus || '-'}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{row.effectiveness_verified ? 'Verified' : '-'}</td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{formatDate(row.created_at || row.createdAt)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      );
    }

    if (type === 'department-performance') {
      const dept = data.department || {};
      const ncr = data.ncr || {};
      const capa = data.capa || {};
      return (
        <div className="p-6 space-y-6">
          <div className="text-sm text-gray-700">
            <div className="font-medium text-gray-900">{dept.code ? `${dept.code} - ${dept.name}` : (dept.name || 'Department')}</div>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div className="border border-gray-200 rounded-md p-4">
              <div className="text-sm font-medium text-gray-900 mb-2">NCR</div>
              <div className="text-sm text-gray-700">Total: {ncr.total ?? 0}</div>
              <div className="text-sm text-gray-700">Open: {ncr.open ?? 0}</div>
              <div className="text-sm text-gray-700">Closed: {ncr.closed ?? 0}</div>
            </div>
            <div className="border border-gray-200 rounded-md p-4">
              <div className="text-sm font-medium text-gray-900 mb-2">CAPA</div>
              <div className="text-sm text-gray-700">Total: {capa.total ?? 0}</div>
              <div className="text-sm text-gray-700">Closed: {capa.closed ?? 0}</div>
              <div className="text-sm text-gray-700">Verified: {capa.verified ?? 0}</div>
              <div className="text-sm text-gray-700">Effectiveness Rate: {capa.effectiveness_rate ?? 0}%</div>
            </div>
          </div>
        </div>
      );
    }

    if (type === 'pareto-analysis') {
      const total = data.total_ncr ?? 0;
      const byCategory = Array.isArray(data.by_category) ? data.by_category : [];
      const byMode = Array.isArray(data.by_mode) ? data.by_mode : [];
      if (total === 0) return <div className="p-4 text-center">No data found matching criteria.</div>;
      return (
        <div className="p-4 space-y-8">
          <div className="text-sm text-gray-700">Total NCR: {total}</div>

          <div>
            <div className="text-sm font-medium text-gray-900 mb-2">Top Defect Categories</div>
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {byCategory.map((row, idx) => (
                  <tr key={idx}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{row.code ? `${row.code} - ${row.name}` : row.name}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{row.count}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{row.percentage}%</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div>
            <div className="text-sm font-medium text-gray-900 mb-2">Top Defect Modes</div>
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Count</th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {byMode.map((row, idx) => (
                  <tr key={idx}>
                    <td className="px-6 py-4 text-sm text-gray-900">{row.name}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{row.count}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">{row.percentage}%</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      );
    }
    
    // Default table
     return (
        <div className="p-4">Raw data view: {JSON.stringify(data).substring(0, 100)}...</div>
     );
  };

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="bg-white shadow sm:rounded-lg overflow-hidden">
        <div className="px-4 py-5 sm:px-6 flex justify-between items-center">
          <h3 className="text-lg font-medium text-gray-900">Report Results: {type}</h3>
          <button onClick={() => window.print()} className="text-blue-600 hover:text-blue-800">Print</button>
        </div>
        
        {loading ? (
          <div className="p-10 text-center">Generating Report...</div>
        ) : error ? (
          <div className="p-10 text-center text-red-600">{error}</div>
        ) : (
          <div className="overflow-x-auto">
            {renderTable()}
          </div>
        )}
      </div>
    </div>
  );
};

export default ReportViewer;
