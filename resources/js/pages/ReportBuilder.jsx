import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import Breadcrumb from '../components/Breadcrumb';
import api from '../services/api';
import DatePicker from '../components/DatePicker';
import Select from '../components/Select';

const ReportBuilder = () => {
  const [searchParams] = useSearchParams();
  const reportType = searchParams.get('type') || 'ncr-summary';
  const navigate = useNavigate();
  
  const [departments, setDepartments] = useState([]);
  const [filters, setFilters] = useState({
    startDate: '',
    endDate: '',
    department_id: '',
    status: ''
  });

  useEffect(() => {
    const fetchDeps = async () => {
      try {
        const res = await api.get('/departments');
        const payload = res.data?.data ?? res.data;
        const list = Array.isArray(payload) ? payload : (payload?.data ?? []);
        setDepartments(list);
      } catch (err) {
        console.error(err);
      }
    };
    fetchDeps();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFilters(prev => ({ ...prev, [name]: value }));
  };

  const handleGenerate = (e) => {
    e.preventDefault();
    // Build query string
    const params = new URLSearchParams();
    if (filters.startDate) params.append('date_from', filters.startDate);
    if (filters.endDate) params.append('date_to', filters.endDate);
    if (filters.department_id) params.append('department_id', filters.department_id);
    if (filters.status) params.append('status', filters.status);
    
    navigate(`/reports/view/${reportType}?${params.toString()}`);
  };

  const getReportTitle = () => {
    switch(reportType) {
        case 'ncr-summary': return 'NCR Summary Report Builder';
        case 'capa-effectiveness': return 'CAPA Effectiveness Report Builder';
        case 'department-performance': return 'Department Performance Report Builder';
        case 'pareto-analysis': return 'Pareto Analysis Builder';
        default: return 'Custom Report Builder';
    }
  };

  const getStatusOptions = () => {
    if (reportType === 'capa-effectiveness') {
      return [
        { value: 'Open', label: 'Open (Not Closed)' },
        { value: 'In_Progress', label: 'In Progress' },
        { value: 'Pending_Verification', label: 'Pending Verification' },
        { value: 'Closed', label: 'Closed' },
      ];
    }

    return [
      { value: 'Draft', label: 'Draft' },
      { value: 'Submitted', label: 'Submitted' },
      { value: 'Approved', label: 'Approved' },
      { value: 'In Progress', label: 'In Progress' },
      { value: 'Closed', label: 'Closed' },
      { value: 'Rejected', label: 'Rejected' },
      { value: 'Cancelled', label: 'Cancelled' },
    ];
  };

  return (
    <div className="space-y-6">
      <Breadcrumb />

      <div className="bg-white shadow sm:rounded-lg">
        <div className="px-4 py-5 sm:p-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900">
            {getReportTitle()}
          </h3>
          <div className="mt-2 max-w-xl text-sm text-gray-500">
            <p>Select filters to generate your report.</p>
          </div>
          
          <form className="mt-5 space-y-4" onSubmit={handleGenerate}>
            <div className="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <DatePicker 
                    label="Start Date" 
                    id="startDate" 
                    value={filters.startDate} 
                    onChange={handleChange} 
                />
                <DatePicker 
                    label="End Date" 
                    id="endDate" 
                    value={filters.endDate} 
                    onChange={handleChange} 
                />
                
                <Select 
                    label="Department"
                    id="department_id"
                    value={filters.department_id}
                    onChange={handleChange}
                    required={reportType === 'department-performance'}
                    options={departments.map((d) => ({
                      value: d.id,
                      label: d.full_name || d.fullName || d.department_name || d.name || String(d.id),
                    }))}
                />

                <Select 
                    label="Status"
                    id="status"
                    value={filters.status}
                    onChange={handleChange}
                    options={getStatusOptions()}
                />
            </div>

            <div className="pt-5">
                <div className="flex justify-end">
                    <button
                        type="submit"
                        className="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Generate Report
                    </button>
                </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default ReportBuilder;
