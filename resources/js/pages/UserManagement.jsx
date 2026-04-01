import React, { useState, useEffect } from 'react';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import { Edit, Trash2, Plus, UserPlus } from 'lucide-react';

const UserManagement = () => {
  const [users, setUsers] = useState([]);
  const [departments, setDepartments] = useState([]);
  const [roles, setRoles] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [currentUser, setCurrentUser] = useState(null); // If editing
  const [pagination, setPagination] = useState({ currentPage: 1, lastPage: 1, perPage: 10, total: 0 });
  const [formData, setFormData] = useState({ 
      name: '', 
      email: '', 
      password: '', 
      role_id: '', 
      department_id: '',
      employee_id: '',
      is_active: true
  });

  useEffect(() => {
    fetchUsers(1);
    fetchDepartments();
    fetchRoles();
  }, []);

  const fetchUsers = async (page = 1) => {
    try {
      setLoading(true);
      const response = await api.get('/admin/users', { params: { page } });
      const paged = response.data?.data;
      if (paged && Array.isArray(paged.data)) {
        setUsers(paged.data);
        setPagination({
          currentPage: paged.current_page || page,
          lastPage: paged.last_page || 1,
          perPage: paged.per_page || 10,
          total: paged.total || paged.data.length,
        });
      } else {
        setUsers(response.data.data || []);
        setPagination(prev => ({ ...prev, total: (response.data.data || []).length, lastPage: 1, currentPage: 1 }));
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const fetchDepartments = async () => {
      try {
          const response = await api.get('/master/departments');
          setDepartments(response.data.data || response.data);
      } catch (err) { console.error(err); }
  };

  const fetchRoles = async () => {
      try {
          const response = await api.get('/master/roles');
          setRoles(response.data.data || response.data);
      } catch (err) { console.error(err); }
  };

  const handleExportUsers = async () => {
    try {
      const res = await api.get('/admin/users/export', { responseType: 'blob' });
      const blob = new Blob([res.data], { type: res.headers['content-type'] || 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      // Try to extract filename from Content-Disposition
      const cd = res.headers['content-disposition'] || '';
      const match = /filename="?([^"]+)"?/.exec(cd);
      a.href = url;
      a.download = match ? match[1] : `users_${new Date().toISOString().slice(0,10)}.xlsx`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    } catch (err) {
      const message = err.response?.data?.message || err.message;
      alert('Failed to export users: ' + message);
    }
  };

  const handleDelete = async (id) => {
    if (!confirm('Are you sure?')) return;
    try {
      await api.delete(`/admin/users/${id}`);
      setUsers(prev => prev.filter(u => u.id !== id));
    } catch (err) {
      const message = err.response?.data?.message || err.message;
      alert('Failed to delete user: ' + message);
    }
  };

  const handleSave = async (e) => {
      e.preventDefault();
      try {
          const payload = {
              ...formData,
              employee_id: String(formData.employee_id || '').trim(),
              department_id: formData.department_id ? Number(formData.department_id) : null,
              role_id: formData.role_id ? Number(formData.role_id) : formData.role_id,
          };

          if (currentUser && !payload.password) {
              delete payload.password;
          }

          if (currentUser) {
              await api.put(`/admin/users/${currentUser.id}`, payload);
          } else {
              await api.post('/admin/users', payload);
          }
          setShowModal(false);
          fetchUsers();
          resetForm();
      } catch (err) {
          console.error(err);
          const message = err.response?.data?.message || err.message;
          const errors = err.response?.data?.errors;
          const errorText = errors
              ? Object.values(errors).flat().join('\n')
              : message;
          alert('Failed to save user:\n' + errorText);
      }
  };

  const resetForm = () => {
      setFormData({ 
          name: '', 
          email: '', 
          password: '', 
          role_id: '', 
          department_id: '',
          employee_id: '',
          is_active: true
      });
      setCurrentUser(null);
  };

  const openModal = (user = null) => {
      if (user) {
          setCurrentUser(user);
          setFormData({
              name: user.name,
              email: user.email,
              password: '', // Don't show password
              role_id: user.role_id || '',
              department_id: user.department_id || '',
              employee_id: user.employee_id || '',
              is_active: user.is_active !== undefined ? user.is_active : true
          });
      } else {
          resetForm();
      }
      setShowModal(true);
  };

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-semibold text-gray-900">User Management</h1>
        <div className="flex items-center gap-2">
          <button
            onClick={handleExportUsers}
            className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Export Excel
          </button>
          <label className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
            <input
              type="file"
              accept=".xlsx,.xls,.csv"
              className="hidden"
              onChange={async (e) => {
                const file = e.target.files?.[0];
                if (!file) return;
                const fd = new FormData();
                fd.append('file', file);
                try {
                  const res = await api.post('/admin/users/import', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                  });
                  alert(res.data?.message || 'Import completed');
                  fetchUsers(pagination.currentPage);
                } catch (err) {
                  const message = err.response?.data?.message || err.message;
                  alert('Failed to import users: ' + message);
                } finally {
                  e.target.value = '';
                }
              }}
            />
            Import Excel
          </label>
          <button
            onClick={() => openModal()}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <UserPlus className="-ml-1 mr-2 h-5 w-5" />
            Add User
          </button>
        </div>
      </div>

      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        {loading ? (
            <div className="p-4 text-center">Loading...</div>
        ) : (
            <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
                <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                <th className="relative px-6 py-3"><span className="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
                {users.map((user) => (
                <tr key={user.id}>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{user.name}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{user.email}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{user.role?.role_name || '-'}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{user.department?.department_name || '-'}</td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onClick={() => openModal(user)} className="text-indigo-600 hover:text-indigo-900 mr-4"><Edit className="h-4 w-4" /></button>
                    <button onClick={() => handleDelete(user.id)} className="text-red-600 hover:text-red-900 mr-4"><Trash2 className="h-4 w-4" /></button>
                    <button
                      onClick={async () => {
                        const newPwd = prompt('Enter new password (min 8 chars):', 'password');
                        if (!newPwd) return;
                        if (newPwd.length < 8) return alert('Password must be at least 8 characters.');
                        try {
                          const res = await api.post(`/admin/users/${user.id}/reset-password`, { new_password: newPwd });
                          alert(res.data?.message || 'Password reset');
                        } catch (err) {
                          const message = err.response?.data?.message || err.message;
                          alert('Failed to reset password: ' + message);
                        }
                      }}
                      className="text-gray-700 hover:text-gray-900"
                    >
                      Reset Password
                    </button>
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
            Page {pagination.currentPage} of {pagination.lastPage} • Total {pagination.total} users
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={() => fetchUsers(pagination.currentPage - 1)}
              disabled={pagination.currentPage <= 1}
              className="px-3 py-1.5 text-sm rounded-md border border-gray-300 bg-white disabled:opacity-50"
            >
              Previous
            </button>
            <button
              onClick={() => fetchUsers(pagination.currentPage + 1)}
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
          <div className="fixed inset-0 z-50" role="dialog" aria-modal="true">
            <div
              className="absolute inset-0 bg-gray-500 opacity-75"
              onClick={() => setShowModal(false)}
            ></div>

            <div className="relative z-10 flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
              <div className="relative w-full max-w-lg transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:p-6">
                <div>
                  <h3 className="text-lg leading-6 font-medium text-gray-900 mb-4">{currentUser ? 'Edit User' : 'Add User'}</h3>
                  <form onSubmit={handleSave} className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700">Name</label>
                      <input type="text" required value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700">Email</label>
                      <input type="email" required value={formData.email} onChange={e => setFormData({ ...formData, email: e.target.value })} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700">Employee ID</label>
                      <input type="text" required value={formData.employee_id} onChange={e => setFormData({ ...formData, employee_id: e.target.value })} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                    </div>
                    {!currentUser && (
                      <div>
                        <label className="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" required value={formData.password} onChange={e => setFormData({ ...formData, password: e.target.value })} className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                      </div>
                    )}
                    <div>
                      <label className="block text-sm font-medium text-gray-700">Role</label>
                      <select required value={formData.role_id} onChange={e => setFormData({ ...formData, role_id: e.target.value })} className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Select Role</option>
                        {roles.map(r => <option key={r.id} value={r.id}>{r.role_name}</option>)}
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700">Department</label>
                      <select value={formData.department_id} onChange={e => setFormData({ ...formData, department_id: e.target.value })} className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Select Department</option>
                        {departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                      </select>
                    </div>
                    <div className="flex items-center">
                      <input
                        id="is_active"
                        name="is_active"
                        type="checkbox"
                        checked={formData.is_active}
                        onChange={e => setFormData({ ...formData, is_active: e.target.checked })}
                        className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <label htmlFor="is_active" className="ml-2 block text-sm text-gray-900">
                        Active Account
                      </label>
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

export default UserManagement;
