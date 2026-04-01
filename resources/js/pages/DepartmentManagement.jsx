import React, { useState, useEffect } from 'react';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import { Edit, Trash2, Plus, Building } from 'lucide-react';

const DepartmentManagement = () => {
  const [departments, setDepartments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [currentDept, setCurrentDept] = useState(null);
  const [pagination, setPagination] = useState({ currentPage: 1, lastPage: 1, perPage: 10, total: 0 });
  const [formData, setFormData] = useState({ department_name: '', department_code: '' });

  useEffect(() => {
    fetchDepartments(1);
  }, []);

  const fetchDepartments = async (page = 1) => {
    try {
      setLoading(true);
      const response = await api.get('/admin/departments', { params: { page } });
      const paged = response.data?.data;
      if (paged && Array.isArray(paged.data)) {
        setDepartments(paged.data);
        setPagination({
          currentPage: paged.current_page || page,
          lastPage: paged.last_page || 1,
          perPage: paged.per_page || 10,
          total: paged.total || paged.data.length,
        });
      } else {
        setDepartments(response.data.data || []);
        setPagination(prev => ({ ...prev, total: (response.data.data || []).length, lastPage: 1, currentPage: 1 }));
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id) => {
    if (!confirm('Are you sure? This might fail if users are assigned.')) return;
    try {
      await api.delete(`/admin/departments/${id}`);
      setDepartments(prev => prev.filter(d => d.id !== id));
    } catch (err) {
      const message = err.response?.data?.message || err.message;
      alert('Failed to delete department: ' + message);
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      const payload = {
        department_name: String(formData.department_name || '').trim(),
        department_code: String(formData.department_code || '').trim(),
      };
      if (currentDept) {
        await api.put(`/admin/departments/${currentDept.id}`, payload);
      } else {
        await api.post('/admin/departments', payload);
      }
      setShowModal(false);
      fetchDepartments(pagination.currentPage);
      setFormData({ department_name: '', department_code: '' });
      setCurrentDept(null);
    } catch (err) {
      const message = err.response?.data?.message || err.message;
      const errors = err.response?.data?.errors;
      const errorText = errors
        ? Object.values(errors).flat().join('\n')
        : message;
      alert('Failed to save department:\n' + errorText);
    }
  };

  const openModal = (dept = null) => {
    if (dept) {
      setCurrentDept(dept);
      setFormData({
        department_name: dept.department_name || '',
        department_code: dept.department_code || '',
      });
    } else {
      setCurrentDept(null);
      setFormData({ department_name: '', department_code: '' });
    }
    setShowModal(true);
  };

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-semibold text-gray-900">Department Management</h1>
        <button
          onClick={() => openModal()}
          className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <Plus className="-ml-1 mr-2 h-5 w-5" />
          Add Department
        </button>
      </div>

      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        {loading ? (
            <div className="p-4 text-center">Loading...</div>
        ) : (
            <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
                <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                <th className="relative px-6 py-3"><span className="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
                {departments.map((dept) => (
                <tr key={dept.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{dept.department_name}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{dept.department_code}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onClick={() => openModal(dept)} className="text-indigo-600 hover:text-indigo-900 mr-4"><Edit className="h-4 w-4" /></button>
                    <button onClick={() => handleDelete(dept.id)} className="text-red-600 hover:text-red-900"><Trash2 className="h-4 w-4" /></button>
                    </td>
                </tr>
                ))}
            </tbody>
            </table>
        )}
      </div>
      
      {!loading && (
        <div className="flex items-center justify-between bg-white px-4 py-3 border border-gray-200 rounded-b-md">
          <div className="text-sm text-gray-600">
            Page {pagination.currentPage} of {pagination.lastPage} • Total {pagination.total} departments
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => fetchDepartments(pagination.currentPage - 1)}
              disabled={pagination.currentPage <= 1}
              className="px-3 py-1.5 text-sm rounded-md border border-gray-300 bg-white disabled:opacity-50"
            >
              Previous
            </button>
            <button
              onClick={() => fetchDepartments(pagination.currentPage + 1)}
              disabled={pagination.currentPage >= pagination.lastPage}
              className="px-3 py-1.5 text-sm rounded-md border border-gray-300 bg-white disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      )}

      {/* Modal */}
      {showModal && (
          <div className="fixed z-10 inset-0 overflow-y-auto">
            <div className="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div className="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div className="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span className="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div className="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">{currentDept ? 'Edit Department' : 'Add Department'}</h3>
                        <form onSubmit={handleSave} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" required value={formData.department_name} onChange={e => setFormData({ ...formData, department_name: e.target.value })} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Code</label>
                                <input type="text" required value={formData.department_code} onChange={e => setFormData({ ...formData, department_code: e.target.value })} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                            </div>
                            <div className="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit" className="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                                    Save
                                </button>
                                <button type="button" onClick={() => setShowModal(false)} className="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
          </div>
      )}
    </div>
  );
};

export default DepartmentManagement;
