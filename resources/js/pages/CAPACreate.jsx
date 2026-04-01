import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import Input from '../components/Input';
import DatePicker from '../components/DatePicker';
import Select from '../components/Select';
import Button from '../components/Button';

const CAPACreate = () => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const ncrId = searchParams.get('ncr_id');

  const [formData, setFormData] = useState({
    ncr_id: ncrId || '',
    title: '',
    description: '',
    assigned_to: '',
    due_date: '',
    source: ncrId ? 'NCR' : 'Audit', // Default source
  });
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    // Fetch users for assignment
    api.get('/users').then(res => setUsers(res.data.data || res.data));
    
    // If NCR ID is present, fetch NCR details to pre-fill
    if (ncrId) {
        api.get(`/ncrs/${ncrId}`).then(res => {
            const ncr = res.data.data || res.data;
            setFormData(prev => ({
                ...prev,
                title: `CAPA for NCR #${ncr.ncr_number}`,
                description: `Corrective action for ${ncr.description}`
            }));
        });
    }
  }, [ncrId]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const response = await api.post('/capas', formData);
      navigate(`/capas/${response.data.data.id}`);
    } catch (err) {
      console.error(err);
      alert('Failed to create CAPA');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="md:flex md:items-center md:justify-between">
        <div className="flex-1 min-w-0">
          <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Create New CAPA
          </h2>
        </div>
      </div>

      <div className="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div className="sm:col-span-2">
                    <Input 
                        label="Title" 
                        id="title" 
                        value={formData.title} 
                        onChange={handleChange} 
                        required 
                    />
                </div>
                
                <div className="sm:col-span-2">
                    <label htmlFor="description" className="block text-sm font-medium text-gray-700">Description</label>
                    <div className="mt-1">
                        <textarea
                            id="description"
                            name="description"
                            rows={3}
                            className="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md"
                            value={formData.description}
                            onChange={handleChange}
                            required
                        />
                    </div>
                </div>

                <Select 
                    label="Source" 
                    id="source" 
                    value={formData.source} 
                    onChange={handleChange}
                    options={[
                        { value: 'NCR', label: 'Non-Conformance Report' },
                        { value: 'Audit', label: 'Internal/External Audit' },
                        { value: 'Complaint', label: 'Customer Complaint' },
                        { value: 'Risk Assessment', label: 'Risk Assessment' }
                    ]}
                />

                <Select
                    label="Assigned To"
                    id="assigned_to"
                    value={formData.assigned_to}
                    onChange={handleChange}
                    options={users.map(u => ({ value: u.id, label: u.name }))}
                    required
                />

                <DatePicker 
                    label="Due Date" 
                    id="due_date" 
                    value={formData.due_date} 
                    onChange={handleChange} 
                    required 
                />
                
                {ncrId && (
                    <div className="sm:col-span-2">
                         <label className="block text-sm font-medium text-gray-700">Linked NCR ID</label>
                         <input type="text" disabled value={ncrId} className="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md shadow-sm sm:text-sm" />
                    </div>
                )}
            </div>

            <div className="flex justify-end">
                <Button type="button" variant="secondary" onClick={() => navigate(-1)} className="mr-3">
                    Cancel
                </Button>
                <Button type="submit" disabled={loading}>
                    {loading ? 'Creating...' : 'Create CAPA'}
                </Button>
            </div>
        </form>
      </div>
    </div>
  );
};

export default CAPACreate;
