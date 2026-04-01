import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import Loading from '../components/Loading';
import Button from '../components/Button';
import ProgressTracker from '../components/CAPA/ProgressTracker';
import RCAForm from '../components/CAPA/RCAForm';
import ActionPlanForm from '../components/CAPA/ActionPlanForm';
import VerificationForm from '../components/CAPA/VerificationForm';
import AttachmentUpload from '../components/AttachmentUpload';

const CAPADetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [capa, setCapa] = useState(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('details'); // details, rca, actions, verification, attachments

  useEffect(() => {
    fetchCAPA();
  }, [id]);

  const fetchCAPA = async () => {
    try {
      const response = await api.get(`/capas/${id}`);
      setCapa(response.data.data || response.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleUpdate = async (section, data) => {
    try {
      await api.put(`/capas/${id}/${section}`, data);
      fetchCAPA(); // Refresh data
      alert('Updated successfully');
    } catch (err) {
      console.error(err);
      alert('Failed to update');
    }
  };

  const handleSubmitForReview = async () => {
    if (!confirm('Are you sure you want to submit this CAPA for review?')) return;
    try {
      await api.put(`/capas/${id}/status`, { status: 'Review' });
      fetchCAPA();
      alert('CAPA submitted for review successfully.');
    } catch (err) {
      console.error(err);
      alert('Failed to submit for review.');
    }
  };

  if (loading) return <Loading />;
  if (!capa) return <div>CAPA not found</div>;

  const steps = [
    { id: 1, name: 'Initiation', status: 'completed' },
    { id: 2, name: 'Root Cause Analysis', status: capa.why1 ? 'completed' : 'current' },
    { id: 3, name: 'Action Planning', status: capa.correctiveActionPlan ? 'completed' : (capa.why1 ? 'current' : 'upcoming') },
    { id: 4, name: 'Implementation', status: capa.currentStatus === 'In_Progress' ? 'current' : (capa.currentStatus === 'Verification' || capa.currentStatus === 'Closed' ? 'completed' : 'upcoming') },
    { id: 5, name: 'Verification', status: capa.currentStatus === 'Verification' ? 'current' : (capa.currentStatus === 'Closed' ? 'completed' : 'upcoming') },
    { id: 6, name: 'Closure', status: capa.currentStatus === 'Closed' ? 'completed' : 'upcoming' },
  ];

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="md:flex md:items-center md:justify-between">
        <div className="flex-1 min-w-0">
          <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            {capa.capaNumber}
          </h2>
          <p className="mt-1 text-sm text-gray-500">
            Assigned to {capa.assignedPic?.name || 'Unassigned'} | Due: {capa.targetCompletionDate ? new Date(capa.targetCompletionDate).toLocaleDateString() : '-'}
          </p>
        </div>
        <div className="mt-4 flex md:mt-0 md:ml-4">
           {capa.currentStatus !== 'Closed' && (
               <Button onClick={handleSubmitForReview} className="ml-3">
                   Submit for Review
               </Button>
           )}
        </div>
      </div>

      <ProgressTracker steps={steps} currentStep={steps.find(s => s.status === 'current')?.id || 6} />

      <div className="bg-white shadow overflow-hidden sm:rounded-lg">
        <div className="border-b border-gray-200">
          <nav className="-mb-px flex" aria-label="Tabs">
            {['details', 'rca', 'actions', 'verification', 'attachments'].map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`${
                  activeTab === tab
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                } w-1/5 py-4 px-1 text-center border-b-2 font-medium text-sm capitalize`}
              >
                {tab === 'rca' ? 'Root Cause Analysis' : tab}
              </button>
            ))}
          </nav>
        </div>

        <div className="p-6">
          {activeTab === 'details' && (
            <div className="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
              <div className="sm:col-span-2">
                <dt className="text-sm font-medium text-gray-500">Defect Description</dt>
                <dd className="mt-1 text-sm text-gray-900">{capa.ncr?.defectDescription || '-'}</dd>
              </div>
              <div className="sm:col-span-2">
                <dt className="text-sm font-medium text-gray-500">Root Cause Summary</dt>
                <dd className="mt-1 text-sm text-gray-900">{capa.rootCauseSummary || '-'}</dd>
              </div>
              <div>
                <dt className="text-sm font-medium text-gray-500">Source NCR</dt>
                <dd className="mt-1 text-sm text-gray-900">{capa.ncr?.ncrNumber || '-'}</dd>
              </div>
              <div>
                <dt className="text-sm font-medium text-gray-500">Status</dt>
                <dd className="mt-1 text-sm text-gray-900">{capa.currentStatus}</dd>
              </div>
            </div>
          )}

          {activeTab === 'rca' && (
            <RCAForm 
                value={capa.rca || {}} 
                onChange={(data) => handleUpdate('rca', data)} 
            />
          )}

          {activeTab === 'actions' && (
            <div className="space-y-8">
                <ActionPlanForm 
                    type="corrective"
                    actions={capa.actions?.filter(a => a.type === 'corrective') || []}
                    onChange={(data) => handleUpdate('actions', { type: 'corrective', actions: data })}
                />
                 <div className="border-t border-gray-200 pt-8">
                    <ActionPlanForm 
                        type="preventive"
                        actions={capa.actions?.filter(a => a.type === 'preventive') || []}
                        onChange={(data) => handleUpdate('actions', { type: 'preventive', actions: data })}
                    />
                </div>
            </div>
          )}

          {activeTab === 'verification' && (
            <VerificationForm 
                verification={capa.verification}
                onChange={(data) => { /* Local state update if needed */ }}
                onSubmit={(data) => handleUpdate('verification', data)}
            />
          )}

          {activeTab === 'attachments' && (
            <div className="space-y-4">
                <AttachmentUpload entityType="capa" entityId={id} onUploadSuccess={fetchCAPA} />
                <div className="mt-4">
                    <h4 className="text-sm font-medium text-gray-900">Existing Attachments</h4>
                    {capa.attachments && capa.attachments.length > 0 ? (
                        <ul className="mt-2 divide-y divide-gray-200 border border-gray-200 rounded-md">
                            {capa.attachments.map((file) => (
                                <li key={file.id} className="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                    <div className="w-0 flex-1 flex items-center">
                                        <span className="ml-2 flex-1 w-0 truncate">{file.file_name}</span>
                                    </div>
                                    <div className="ml-4 flex-shrink-0">
                                        <a href={file.file_path} target="_blank" rel="noreferrer" className="font-medium text-blue-600 hover:text-blue-500">
                                            Download
                                        </a>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <p className="text-sm text-gray-500 mt-2">No attachments yet.</p>
                    )}
                </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default CAPADetail;
