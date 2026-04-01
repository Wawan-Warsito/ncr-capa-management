import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import Loading from '../components/Loading';
import Button from '../components/Button';
import Select from '../components/Select';

const CAPAProgress = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [capa, setCapa] = useState(null);
  const [loading, setLoading] = useState(true);
  const [updates, setUpdates] = useState({});

  useEffect(() => {
    fetchCAPA();
  }, [id]);

  const fetchCAPA = async () => {
    try {
      const response = await api.get(`/capas/${id}`);
      setCapa(response.data.data || response.data);
      // Initialize updates state with current action statuses
      const initialUpdates = {};
      if (response.data.data.actions) {
          response.data.data.actions.forEach(action => {
              initialUpdates[action.id] = { status: action.status, comments: '' };
          });
      }
      setUpdates(initialUpdates);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleStatusChange = (actionId, status) => {
    setUpdates(prev => ({
        ...prev,
        [actionId]: { ...prev[actionId], status }
    }));
  };

  const handleCommentChange = (actionId, comments) => {
    setUpdates(prev => ({
        ...prev,
        [actionId]: { ...prev[actionId], comments }
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await api.post(`/capas/${id}/progress`, { updates });
      alert('Progress updated successfully');
      navigate(`/capas/${id}`);
    } catch (err) {
      console.error(err);
      alert('Failed to update progress');
    }
  };

  if (loading) return <Loading />;
  if (!capa) return <div>CAPA not found</div>;

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="md:flex md:items-center md:justify-between">
        <div className="flex-1 min-w-0">
          <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Update Progress: {capa.capa_number}
          </h2>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="bg-white shadow overflow-hidden sm:rounded-lg p-6 space-y-6">
        <div>
            <h3 className="text-lg font-medium text-gray-900">Action Items</h3>
            <p className="mt-1 text-sm text-gray-500">Update the status of assigned actions.</p>
        </div>

        {capa.actions && capa.actions.length > 0 ? (
            <div className="space-y-6">
                {capa.actions.map(action => (
                    <div key={action.id} className="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                        <div className="mb-2">
                            <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${action.type === 'corrective' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'}`}>
                                {action.type}
                            </span>
                            <p className="mt-1 text-sm text-gray-900 font-medium">{action.description}</p>
                            <p className="text-xs text-gray-500">Assigned to: {action.assigned_to}</p>
                        </div>
                        
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <Select
                                label="Status"
                                id={`status-${action.id}`}
                                value={updates[action.id]?.status || action.status}
                                onChange={(e) => handleStatusChange(action.id, e.target.value)}
                                options={[
                                    { value: 'Pending', label: 'Pending' },
                                    { value: 'In Progress', label: 'In Progress' },
                                    { value: 'Completed', label: 'Completed' }
                                ]}
                            />
                            
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Progress Note</label>
                                <input
                                    type="text"
                                    className="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="Add a comment..."
                                    value={updates[action.id]?.comments || ''}
                                    onChange={(e) => handleCommentChange(action.id, e.target.value)}
                                />
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        ) : (
            <p className="text-gray-500 italic">No action items defined yet.</p>
        )}

        <div className="flex justify-end pt-4">
            <Button type="button" variant="secondary" onClick={() => navigate(-1)} className="mr-3">
                Cancel
            </Button>
            <Button type="submit">
                Save Progress
            </Button>
        </div>
      </form>
    </div>
  );
};

export default CAPAProgress;
