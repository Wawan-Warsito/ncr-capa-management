import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import NCRStatusBadge from '../components/NCRStatusBadge';
import { Check, X, AlertTriangle } from 'lucide-react';

const NCRApproval = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [ncr, setNcr] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [comment, setComment] = useState('');
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchNCR = async () => {
      try {
        const response = await api.get(`/ncrs/${id}`);
        setNcr(response.data.data);
      } catch (err) {
        setError('Failed to load NCR details.');
      } finally {
        setLoading(false);
      }
    };
    fetchNCR();
  }, [id]);

  const handleAction = async (action) => {
    if (!confirm(`Are you sure you want to ${action} this NCR?`)) return;
    
    setProcessing(true);
    try {
      // Use 'remarks' to match backend validation
      await api.post(`/ncrs/${id}/${action}`, { remarks: comment });
      navigate(`/ncrs/${id}`); // Redirect to detail page
    } catch (err) {
        console.error(err);
      setError(`Failed to ${action} NCR. Please try again.`);
      setProcessing(false);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div className="text-red-600">{error}</div>;
  if (!ncr) return <div>NCR not found.</div>;

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        <div className="px-4 py-5 sm:px-6 flex justify-between">
          <div>
            <h3 className="text-lg leading-6 font-medium text-gray-900">NCR Approval Request</h3>
            <p className="mt-1 max-w-2xl text-sm text-gray-500">{ncr.ncr_number}</p>
          </div>
          <NCRStatusBadge status={ncr.status} />
        </div>
        
        <div className="border-t border-gray-200 px-4 py-5 sm:p-0">
          <dl className="sm:divide-y sm:divide-gray-200">
            <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt className="text-sm font-medium text-gray-500">Defect Description</dt>
              <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{ncr.defect_description}</dd>
            </div>
            <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
              <dt className="text-sm font-medium text-gray-500">Finder Department</dt>
              <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{ncr.finder_department?.department_name || '-'}</dd>
            </div>
            <div className="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt className="text-sm font-medium text-gray-500">Created By</dt>
                <dd className="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{ncr.created_by?.name || '-'}</dd>
            </div>
             {/* Add more details as needed */}
          </dl>
        </div>
      </div>

      <div className="bg-white shadow sm:rounded-lg p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Approval Decision</h3>
        
        <div className="mb-4">
          <label htmlFor="comment" className="block text-sm font-medium text-gray-700">Comments (Optional)</label>
          <textarea
            id="comment"
            rows={3}
            className="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
            placeholder="Add reasoning for approval or rejection..."
            value={comment}
            onChange={(e) => setComment(e.target.value)}
          />
        </div>

        <div className="flex space-x-3">
          <button
            onClick={() => handleAction('approve')}
            disabled={processing}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
          >
            <Check className="mr-2 h-4 w-4" />
            Approve
          </button>
          
          <button
            onClick={() => handleAction('reject')}
            disabled={processing}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
          >
            <X className="mr-2 h-4 w-4" />
            Reject
          </button>
        </div>
      </div>
    </div>
  );
};

export default NCRApproval;
