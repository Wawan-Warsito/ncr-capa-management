import React, { useState, useEffect, useMemo } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import Table from '../components/Table';
import Pagination from '../components/Pagination';
import Loading from '../components/Loading';
import { Plus, Filter } from 'lucide-react';

const CAPAList = () => {
  const [capas, setCapas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [statusFilter, setStatusFilter] = useState('');
  const [searchParams] = useSearchParams();

  const statusParam = useMemo(() => {
    return (searchParams.get('status') || '').toString();
  }, [searchParams]);

  useEffect(() => {
    fetchCAPAs();
  }, [page, statusFilter]);

  useEffect(() => {
    if (statusParam) {
      setStatusFilter(statusParam);
      setPage(1);
    }
  }, [statusParam]);

  const fetchCAPAs = async () => {
    setLoading(true);
    try {
      const response = await api.get('/capas', {
        params: { page, status: statusFilter }
      });
      setCapas(response.data.data);
      setTotalPages(response.data.last_page);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    { header: 'CAPA No', accessor: 'capaNumber', render: (row) => <Link to={`/capas/${row.id}`} className="text-blue-600 hover:text-blue-900">{row.capaNumber}</Link> },
    { header: 'Source NCR', accessor: 'ncrNumber', render: (row) => row.ncr ? <Link to={`/ncrs/${row.ncr.id}`} className="text-gray-600 hover:text-gray-900">{row.ncr.ncrNumber}</Link> : 'N/A' },
    { header: 'Title', accessor: 'rootCauseSummary' },
    { header: 'Status', accessor: 'currentStatus', render: (row) => (
      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
        row.currentStatus === 'Closed' ? 'bg-green-100 text-green-800' : 
        row.currentStatus === 'Open' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'
      }`}>
        {row.currentStatus}
      </span>
    )},
    { header: 'Due Date', accessor: 'targetCompletionDate', render: (row) => row.targetCompletionDate ? new Date(row.targetCompletionDate).toLocaleDateString() : '-' },
    { header: 'Assigned To', accessor: 'assignedPic.name', render: (row) => row.assignedPic?.name || '-' }
  ];

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="md:flex md:items-center md:justify-between">
        <div className="flex-1 min-w-0">
          <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Corrective & Preventive Actions
          </h2>
        </div>
        <div className="mt-4 flex md:mt-0 md:ml-4">
          <Link
            to="/capas/create"
            className="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <Plus className="-ml-1 mr-2 h-5 w-5" />
            New CAPA
          </Link>
        </div>
      </div>

      <div className="bg-white shadow rounded-lg p-4">
          <div className="flex items-center space-x-4 mb-4">
              <Filter className="h-5 w-5 text-gray-400" />
              <select 
                value={statusFilter} 
                onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }}
                className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                  <option value="">All Statuses</option>
                  <option value="open">Open</option>
                  <option value="In_Progress">In Progress</option>
                  <option value="Pending_Verification">Verification</option>
                  <option value="Closed">Closed</option>
              </select>
          </div>

          {loading ? <Loading /> : (
            <>
                <Table columns={columns} data={capas} />
                <Pagination currentPage={page} totalPages={totalPages} onPageChange={setPage} />
            </>
          )}
      </div>
    </div>
  );
};

export default CAPAList;
