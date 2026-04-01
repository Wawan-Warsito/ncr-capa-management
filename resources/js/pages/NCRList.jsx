import React, { useState, useEffect, useMemo } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import api from '../services/api';
import NCRStatusBadge from '../components/NCRStatusBadge';
import { Download, Upload } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

const NCRList = () => {
    const { user } = useAuth();
    const roleName = (user?.role?.role_name || user?.role?.name || user?.role || '').toString();
    const [searchParams] = useSearchParams();
    const [ncrs, setNcrs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [pagination, setPagination] = useState({});
    const [importing, setImporting] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [perPage, setPerPage] = useState(15);
    const [sortBy, setSortBy] = useState('issued_date');
    const [sortOrder, setSortOrder] = useState('desc');

    const statusParam = useMemo(() => {
        return (searchParams.get('status') || '').toString();
    }, [searchParams]);

    useEffect(() => {
        // Debounce search
        const timer = setTimeout(() => {
            fetchNCRs(1);
        }, 500);
        return () => clearTimeout(timer);
    }, [searchTerm]);

    useEffect(() => {
        fetchNCRs(1);
    }, [statusParam]);

    const fetchNCRs = async (page = 1) => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            params.set('page', String(page));
            params.set('per_page', String(perPage));
            params.set('search', searchTerm);
            params.set('sort_by', sortBy);
            params.set('sort_order', sortOrder);
            if (statusParam) params.set('status', statusParam);

            const response = await api.get(`/ncrs?${params.toString()}`);
            // Check if response structure is { data: [...], meta: { ... } } or { data: { data: [...], ... } }
            const responseData = response.data;
            
            // Standard Laravel Resource Collection structure: { data: [...], links: ..., meta: ... }
            if (Array.isArray(responseData.data)) {
                setNcrs(responseData.data);
                if (responseData.meta) {
                    setPagination({
                        current_page: responseData.meta.current_page,
                        last_page: responseData.meta.last_page,
                        total: responseData.meta.total,
                        from: responseData.meta.from,
                        to: responseData.meta.to
                    });
                } else {
                    // Fallback if meta is missing or structure is different
                    setPagination({});
                }
            } else if (responseData.data && Array.isArray(responseData.data.data)) {
                // Nested structure: { data: { data: [...], current_page: ... } }
                const result = responseData.data;
                setNcrs(result.data);
                setPagination({
                    current_page: result.current_page,
                    last_page: result.last_page,
                    total: result.total,
                    from: result.from,
                    to: result.to
                });
            } else {
                console.warn('Unexpected API response structure:', responseData);
                setNcrs([]);
            }
        } catch (error) {
            console.error('Error fetching NCRs:', error);
            // Fallback for dev/demo if API fails
            setNcrs([
                { id: 1, ncrNumber: 'NCR-2025-001', finderDepartment: { department_name: 'Production' }, defectDescription: 'Defective material found in batch A1', status: 'Submitted', dateFound: '2025-02-10' },
                { id: 2, ncrNumber: 'NCR-2025-002', finderDepartment: { department_name: 'Quality Check' }, defectDescription: 'Measurement out of tolerance', status: 'Draft', dateFound: '2025-02-09' },
            ]);
        } finally {
            setLoading(false);
        }
    };

    const handleImport = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        setImporting(true);
        try {
            await api.post('/ncrs/import', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            alert('NCR data imported successfully!');
            fetchNCRs(); // Refresh list
        } catch (error) {
            console.error('Import failed:', error);
            const message = error.response?.data?.message || 'Failed to import data. Please check the file format.';
            alert(`Import Error: ${message}`);
        } finally {
            setImporting(false);
            e.target.value = ''; // Reset input
        }
    };

    const handleDownloadTemplate = async () => {
        try {
            const res = await api.get('/ncrs/import-template', { responseType: 'blob' });
            const blob = new Blob([res.data], { type: res.headers['content-type'] || 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            const cd = res.headers['content-disposition'] || '';
            const match = cd.match(/filename="?([^"]+)"?/i);
            a.href = url;
            a.download = match?.[1] || 'ncr_import_template.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        } catch (error) {
            const headers = [
                'ncr_no','line_no','date_found','issued_date','finder_dept','receiver_dept','finder','finder_manager','project_name','project_sn','part_name','order_no','po_no','customer','dwg_doc_no','defect_area','subcont_supplier_name','defect_group','defect_mode','defect_description','severity','disposition','corrective_action','assigned_pic','receiver_comments','mh_used','mh_rate','labor_cost','material_cost','subcont_cost','engineering_cost','other_cost','total_cost','root_cause','preventive_action','evaluation_of_effectiveness','ca_finish_date','status'
            ];
            const sample = [
                '25.P00-QC-01','1','2026-03-04','2026-03-04','QC','PROD','Muchsin','Wahono Adisuranto','Project ABC','25.P00-ABC-01','Jacket Shell','PO25-001125','PO25-001125','Santen Pharmaceutical','D0200002357','Workshop','Mitra Teguh Steel','Welding','RC - Root concavity','Terjadi defect welding pada area jacket shell root concavity','Major','Repaired','Containment done','','','12','1','12','2','0','0','0','20','Root cause text','Preventive action text','Preventive action verified no more same issue after 3 months period','2026-03-20','Draft'
            ];
            const csv = ['sep=,', headers.join(','), sample.map(x => `"${String(x).replace(/"/g, '""')}"`).join(',')].join('\r\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'ncr_import_template_v2.csv';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        }
    };

    const handleDelete = async (id) => {
        if (!window.confirm('Delete this NCR?')) return;
        try {
            await api.delete(`/ncrs/${id}`);
            fetchNCRs(pagination.current_page || 1);
        } catch (error) {
            const message = error.response?.data?.message || error.message;
            alert('Failed to delete: ' + message);
        }
    };

    const handlePurgeAll = async () => {
        if (!window.confirm('Delete ALL NCRs? This cannot be undone.')) return;
        try {
            await api.delete('/admin/ncrs');
            fetchNCRs(1);
        } catch (error) {
            const message = error.response?.data?.message || error.message;
            alert('Failed to purge: ' + message);
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">NCR Management</h1>
                <div className="flex space-x-3">
                    <input
                        type="text"
                        placeholder="Search NCR..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                    />
                    <select
                        value={sortBy}
                        onChange={(e) => { setSortBy(e.target.value); fetchNCRs(1); }}
                        className="border border-gray-300 rounded-md text-sm p-2"
                        title="Sort Field"
                    >
                        <option value="issued_date">Issued Date</option>
                        <option value="date_found">Date Found</option>
                        <option value="ncr_number">NCR Number</option>
                        <option value="created_at">Created At</option>
                    </select>
                    <select
                        value={sortOrder}
                        onChange={(e) => { setSortOrder(e.target.value); fetchNCRs(1); }}
                        className="border border-gray-300 rounded-md text-sm p-2"
                        title="Order"
                    >
                        <option value="desc">Desc</option>
                        <option value="asc">Asc</option>
                    </select>
                    <button
                        type="button"
                        onClick={handleDownloadTemplate}
                        className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                    >
                        <Download className="w-4 h-4 mr-2" />
                        Template
                    </button>
                    {['Administrator','Super Admin'].includes(roleName) && (
                        <button
                            type="button"
                            onClick={handlePurgeAll}
                            className="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none"
                        >
                            Delete All
                        </button>
                    )}
                    <label className={`inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none cursor-pointer ${importing ? 'opacity-50' : ''}`}>
                        <Upload className="w-4 h-4 mr-2" />
                        {importing ? 'Importing...' : 'Import Excel'}
                        <input type="file" className="hidden" accept=".xlsx,.xls,.csv" onChange={handleImport} disabled={importing} />
                    </label>
                    <Link
                        to="/ncrs/create"
                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Create New NCR
                    </Link>
                </div>
            </div>

            <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                {loading ? (
                    <div className="p-12 flex justify-center">
                        <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                    </div>
                ) : (
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NCR Number
                                </th>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Process / Department
                                </th>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date Found
                                </th>
                                <th scope="col" className="relative px-6 py-3">
                                    <span className="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {ncrs.length > 0 ? (
                                ncrs.map((ncr) => (
                                    <tr key={ncr.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                            <Link to={`/ncrs/${ncr.id}`}>{ncr.ncrNumber}</Link>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {ncr.finderDepartment?.department_name || ncr.processName || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                            {ncr.defectDescription || ncr.issueDescription || '-'}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <NCRStatusBadge status={ncr.status} />
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {ncr.dateFound ? ncr.dateFound.split('T')[0].split('-').reverse().join('-') : '-'}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link to={`/ncrs/${ncr.id}`} className="text-blue-600 hover:text-blue-900 mr-4">
                                                View
                                            </Link>
                                            {['Administrator','Super Admin'].includes(roleName) && (
                                                <button onClick={() => handleDelete(ncr.id)} className="text-red-600 hover:text-red-900">
                                                    Delete
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="6" className="px-6 py-12 text-center text-gray-500">
                                        No NCRs found. Create one to get started.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                )}
                {/* Pagination */}
                {pagination.last_page > 1 && (
                    <div className="bg-white px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6">
                        <div className="flex items-center gap-3">
                            <span className="text-sm text-gray-600">Page {pagination.current_page} of {pagination.last_page}</span>
                            <select
                                value={perPage}
                                onChange={(e) => { setPerPage(Number(e.target.value)); fetchNCRs(1); }}
                                className="border border-gray-300 rounded-md text-sm p-1"
                            >
                                <option value={10}>10</option>
                                <option value={15}>15</option>
                                <option value={25}>25</option>
                                <option value={50}>50</option>
                            </select>
                        </div>
                        <div className="flex-1 flex justify-end">
                            <button 
                                onClick={() => fetchNCRs(pagination.current_page - 1)}
                                disabled={pagination.current_page === 1}
                                className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                            >
                                Previous
                            </button>
                            <button 
                                onClick={() => fetchNCRs(pagination.current_page + 1)}
                                disabled={pagination.current_page === pagination.last_page}
                                className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default NCRList;
