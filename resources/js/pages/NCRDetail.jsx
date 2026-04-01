import React, { useState, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import NCRStatusBadge from '../components/NCRStatusBadge';
import NCRTimeline from '../components/NCRTimeline';
import { Paperclip, Trash2, Upload, Edit2, CheckCircle, XCircle, Printer } from 'lucide-react';

const NCRDetail = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { user } = useAuth();
    const [ncr, setNcr] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [uploading, setUploading] = useState(false);
    const [users, setUsers] = useState([]);
    const [loadingUsers, setLoadingUsers] = useState(false);
    const [assignPicId, setAssignPicId] = useState('');
    const [savingAssign, setSavingAssign] = useState(false);

    const fetchNCR = async () => {
        try {
            const response = await api.get(`/ncrs/${id}`);
            setNcr(response.data.data);
        } catch (err) {
            console.error('Error fetching NCR:', err);
            // Fallback for dev/demo if API fails or returns 404 (mocking data)
            if (err.response && err.response.status === 404) {
                setError('NCR not found');
            } else {
                 setNcr({
                    id: id,
                    ncrNumber: `NCR-2025-00${id}`,
                    finderDepartment: { department_name: 'Production' },
                    defectDescription: 'Defective material found in batch A1. The material has cracks on the surface visible to the naked eye. This affects the structural integrity of the final product.',
                    status: 'Draft',
                    created_at: '2025-02-10T10:00:00Z',
                    dateFound: '2025-02-09',
                    productDescription: 'Widget X',
                    orderNumber: 'LOT-12345',
                    attachments: [],
                    timeline: [
                        { id: 1, action: 'Created', user_name: 'Admin User', created_at: '2025-02-10T10:00:00Z', comment: 'Initial submission' },
                    ]
                 });
            }
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchNCR();
    }, [id]);

    useEffect(() => {
        const current = ncr?.assignedPicId ?? ncr?.assigned_pic_id ?? ncr?.assignedPic?.id ?? ncr?.assigned_pic?.id ?? '';
        setAssignPicId(current ? String(current) : '');
    }, [ncr?.id]);

    const handleSubmit = async () => {
        if (!window.confirm('Are you sure you want to submit this NCR? You will not be able to edit it afterwards.')) {
            return;
        }

        try {
            setLoading(true);
            await api.post(`/ncrs/${id}/submit`);
            await fetchNCR(); // Refresh data
            alert('NCR submitted successfully!');
        } catch (err) {
            console.error('Error submitting NCR:', err);
            alert('Failed to submit NCR. Please try again.');
            setLoading(false);
        }
    };

    const handleFileUpload = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        // formData.append('description', file.name); // Optional

        try {
            setUploading(true);
            await api.post(`/ncrs/${id}/attachments`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            await fetchNCR(); // Refresh list
            e.target.value = null; // Reset input
        } catch (err) {
            console.error('Error uploading file:', err);
            alert('Failed to upload file.');
        } finally {
            setUploading(false);
        }
    };

    const handleDeleteAttachment = async (attachmentId) => {
        if (!window.confirm('Are you sure you want to delete this attachment?')) return;

        try {
            await api.delete(`/ncrs/attachments/${attachmentId}`);
            await fetchNCR();
        } catch (err) {
            console.error('Error deleting attachment:', err);
            alert('Failed to delete attachment.');
        }
    };

    // Helper to safely access nested properties
    const safeGet = (obj, path, fallback = '-') => {
        return path.split('.').reduce((acc, part) => acc && acc[part], obj) || fallback;
    };

    const formatDateValue = (value) => {
        if (!value) return '-';
        if (value instanceof Date) return value.toISOString().slice(0, 10);
        const s = String(value);
        if (!s) return '-';
        return s.includes('T') ? s.split('T')[0] : s;
    };

    const canApprove = () => {
        if (!user || !ncr) return false;
        const status = ncr.status ? ncr.status.toLowerCase() : '';
        const role = user.role ? user.role.role_name : '';
        
        // Finder Manager Approval
        if (status === 'pending_finder_approval') {
            return (role === 'Department Manager' && user.department_id === ncr.finder_dept_id) || role === 'Super Admin';
        }
        
        // QC Registration
        if (status === 'pending_qc_registration') {
            return role === 'QC Manager' || role === 'Super Admin';
        }

        return false;
    };

    const roleName = user?.role?.role_name || user?.role?.name || user?.role || '';
    const roleKey = roleName.toString().trim().toLowerCase().replace(/\s+/g, ' ').replace(/[\s-]/g, '_');
    const isAdminRole = ['administrator', 'super_admin', 'admin'].includes(roleKey);
    const isQcManagerRole = ['qc_manager', 'qcmanager'].includes(roleKey);
    const isDeptManagerRole = ['department_manager', 'dept_manager', 'departmentmanager'].includes(roleKey);
    const userDeptId = user?.department_id || user?.department?.id;

    const receiverDeptId =
        ncr?.receiverDeptId ||
        ncr?.receiver_dept_id ||
        ncr?.receiverDepartment?.id ||
        ncr?.receiver_department?.id ||
        null;

    const canQuickAssignPic = (() => {
        if (!user || !ncr) return false;
        if (isAdminRole || isQcManagerRole) return true;
        return isDeptManagerRole && userDeptId && receiverDeptId && Number(userDeptId) === Number(receiverDeptId);
    })();

    const fetchUsers = async () => {
        if (loadingUsers) return;
        try {
            setLoadingUsers(true);
            const res = await api.get('/master/users');
            const list = res.data?.data || res.data || [];
            setUsers(Array.isArray(list) ? list : []);
        } catch (e) {
            setUsers([]);
        } finally {
            setLoadingUsers(false);
        }
    };

    useEffect(() => {
        if (canQuickAssignPic) fetchUsers();
    }, [canQuickAssignPic]);

    const filteredUsers = (() => {
        if (!receiverDeptId) return users;
        if (isAdminRole || isQcManagerRole) return users;
        return users.filter((u) => Number(u.department_id) === Number(receiverDeptId));
    })();

    const handleQuickAssign = async () => {
        try {
            setSavingAssign(true);
            await api.put(`/ncrs/${id}`, {
                assigned_pic_id: assignPicId ? Number(assignPicId) : null,
            });
            await fetchNCR();
        } catch (err) {
            const msg = err.response?.data?.message || err.message || 'Failed to assign PIC';
            alert(msg);
        } finally {
            setSavingAssign(false);
        }
    };

    const canUploadAttachments = () => {
        if (!user || !ncr) return false;
        return (
            isAdminRole ||
            isQcManagerRole ||
            ncr.finderDeptId === userDeptId ||
            ncr.receiverDeptId === userDeptId ||
            ncr.assignedPicId === user.id ||
            ncr.createdByUserId === user.id
        );
    };

    if (loading) return <div className="p-8 flex justify-center"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div></div>;
    if (error) return <div className="p-8 text-center text-red-600">{error}</div>;
    if (!ncr) return <div className="p-8 text-center text-gray-500">NCR not found</div>;

    const canDeleteAttachment = (attachment) => {
        if (!user || !attachment) return false;
        return isAdminRole || isQcManagerRole || attachment.uploadedByUserId === user.id;
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="md:flex md:items-center md:justify-between">
                <div className="flex-1 min-w-0">
                    <div className="flex items-center">
                        <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            {ncr.ncrNumber}
                        </h2>
                        <div className="ml-4">
                            <NCRStatusBadge status={ncr.status} />
                        </div>
                    </div>
                </div>
                <div className="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <Link
                        to={`/ncrs/${ncr.id}/print`}
                        target="_blank"
                        className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                    >
                        <Printer className="w-4 h-4 mr-2" />
                        Print / Export
                    </Link>
                    <Link
                        to="/ncrs"
                        className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                    >
                        Back to List
                    </Link>
                    {canApprove() && (
                        <Link
                            to={`/ncrs/${ncr.id}/approve`}
                            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none"
                        >
                            Review & Approve
                        </Link>
                    )}
                    <Link
                        to={`/ncrs/${ncr.id}/edit`}
                        className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                    >
                        <Edit2 className="w-4 h-4 mr-2" />
                        Edit
                    </Link>
                </div>
            </div>

            {canQuickAssignPic && (
                <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div className="px-4 py-5 sm:px-6 flex items-center justify-between">
                        <div>
                            <h3 className="text-lg leading-6 font-medium text-gray-900">Quick Assign PIC</h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Current PIC: {ncr.assignedPic?.name || ncr.assigned_pic?.name || '-'}
                            </p>
                        </div>
                    </div>
                    <div className="border-t border-gray-200 px-4 py-5 sm:px-6">
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-12">
                            <div className="sm:col-span-8">
                                <label className="block text-sm font-medium text-gray-700">PIC of CA</label>
                                <select
                                    value={assignPicId}
                                    onChange={(e) => setAssignPicId(e.target.value)}
                                    className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-2"
                                    disabled={loadingUsers || savingAssign}
                                >
                                    <option value="">Unassigned</option>
                                    {filteredUsers.map((u) => (
                                        <option key={u.id} value={String(u.id)}>
                                            {u.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="sm:col-span-4 flex items-end">
                                <button
                                    type="button"
                                    onClick={handleQuickAssign}
                                    disabled={savingAssign || loadingUsers}
                                    className="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {savingAssign ? 'Saving...' : 'Save PIC'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {/* Main Content */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Details Card */}
                    <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div className="px-4 py-5 sm:px-6">
                            <h3 className="text-lg leading-6 font-medium text-gray-900">NCR Details</h3>
                            <p className="mt-1 max-w-2xl text-sm text-gray-500">Report information and description.</p>
                        </div>
                        <div className="border-t border-gray-200 px-4 py-5 sm:px-6">
                            <dl className="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                                {/* Header Info */}
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">NCR Number</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.ncrNumber || ncr.ncr_number}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Line No.</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.lineNo || ncr.line_no || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Issued Date</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        {formatDateValue(ncr.issuedDate || ncr.issued_date)}
                                    </dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Last NCR No</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.lastNcrNo || ncr.last_ncr_no || '-'}</dd>
                                </div>

                                {/* Departments & Dates */}
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Finder Department</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{safeGet(ncr, 'finderDepartment.department_name')}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Receiver Department</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{safeGet(ncr, 'receiverDepartment.department_name')}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Date Found</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        {(() => {
                                            const raw = formatDateValue(ncr.dateFound || ncr.date_found);
                                            if (raw === '-') return raw;
                                            const parts = raw.split('-');
                                            if (parts.length !== 3) return raw;
                                            return parts.reverse().join('-');
                                        })()}
                                    </dd>
                                </div>

                                {/* Project / Product Info */}
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Project Name</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.projectName || ncr.project_name || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Project SN</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.projectSn || ncr.project_sn || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Part Name</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.partName || ncr.part_name || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Order No.</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.orderNumber || ncr.order_number || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Customer</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.customerName || ncr.customer_name || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Drawing No.</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.drawingNumber || ncr.drawing_number || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Qty Affected</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.quantityAffected || ncr.quantity_affected || '-'}</dd>
                                </div>

                                {/* Defect Info */}
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Defect Category</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{safeGet(ncr, 'defectCategory.category_name')}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Defect Mode</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.defectMode || ncr.defect_mode || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Severity</dt>
                                    <dd className="mt-1 text-sm text-gray-900">
                                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                                            safeGet(ncr, 'severityLevel.level_name') === 'Critical' ? 'bg-red-100 text-red-800' :
                                            safeGet(ncr, 'severityLevel.level_name') === 'Major' ? 'bg-orange-100 text-orange-800' :
                                            'bg-green-100 text-green-800'
                                        }`}>
                                            {safeGet(ncr, 'severityLevel.level_name')}
                                        </span>
                                    </dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-sm font-medium text-gray-500">Defect Description</dt>
                                    <dd className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{ncr.defectDescription || ncr.defect_description}</dd>
                                </div>

                                {/* Corrective Action */}
                                <div className="sm:col-span-2 border-t pt-4 mt-4">
                                    <h4 className="text-md font-bold text-gray-900 mb-2">Corrective Action</h4>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Disposition</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{safeGet(ncr, 'dispositionMethod.method_name') || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">PIC</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{safeGet(ncr, 'assignedPic.name') || '-'}</dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-sm font-medium text-gray-500">Immediate Action</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.immediateAction || ncr.immediate_action || '-'}</dd>
                                </div>

                                {/* Costs */}
                                <div className="sm:col-span-2 border-t pt-4 mt-4">
                                    <h4 className="text-md font-bold text-gray-900 mb-2">Cost Analysis (USD)</h4>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Labor Cost</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.laborCost || ncr.labor_cost || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Material Cost</dt>
                                    <dd className="mt-1 text-sm text-gray-900">{ncr.materialCost || ncr.material_cost || '-'}</dd>
                                </div>
                                <div className="sm:col-span-1">
                                    <dt className="text-sm font-medium text-gray-500">Total Cost</dt>
                                    <dd className="mt-1 text-sm font-bold text-gray-900">{ncr.totalCost || ncr.total_cost || '-'}</dd>
                                </div>

                                {/* RCA & PA */}
                                <div className="sm:col-span-2 border-t pt-4 mt-4">
                                    <h4 className="text-md font-bold text-gray-900 mb-2">RCA & PA</h4>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-sm font-medium text-gray-500">Root Cause</dt>
                                    <dd className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{ncr.rootCause || ncr.root_cause || '-'}</dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-sm font-medium text-gray-500">Preventive Action</dt>
                                    <dd className="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{ncr.preventiveAction || ncr.preventive_action || '-'}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {/* Attachments Section */}
                    <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div className="px-4 py-5 sm:px-6 flex justify-between items-center">
                            <h3 className="text-lg leading-6 font-medium text-gray-900">Attachments</h3>
                            {canUploadAttachments() && (
                                <div>
                                    <label htmlFor="file-upload" className="cursor-pointer inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                        <Upload className="h-4 w-4 mr-2" />
                                        {uploading ? 'Uploading...' : 'Upload File'}
                                    </label>
                                    <input 
                                        id="file-upload" 
                                        name="file-upload" 
                                        type="file" 
                                        className="sr-only" 
                                        onChange={handleFileUpload}
                                        disabled={uploading}
                                    />
                                </div>
                            )}
                        </div>
                        <div className="border-t border-gray-200">
                            {ncr.attachments && ncr.attachments.length > 0 ? (
                                <ul className="divide-y divide-gray-200">
                                    {ncr.attachments.map((attachment) => (
                                        <li key={attachment.id} className="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                            <div className="w-0 flex-1 flex items-center">
                                                <Paperclip className="flex-shrink-0 h-5 w-5 text-gray-400" />
                                                <span className="ml-2 flex-1 w-0 truncate">{attachment.fileName}</span>
                                            </div>
                                            <div className="ml-4 flex-shrink-0 flex space-x-4">
                                                <a 
                                                    href={`/api/ncrs/attachments/${attachment.id}/download`} 
                                                    target="_blank" 
                                                    rel="noopener noreferrer"
                                                    className="font-medium text-blue-600 hover:text-blue-500"
                                                >
                                                    Download
                                                </a>
                                                {canDeleteAttachment(attachment) && (
                                                    <button
                                                        onClick={() => handleDeleteAttachment(attachment.id)}
                                                        className="font-medium text-red-600 hover:text-red-500"
                                                    >
                                                        Delete
                                                    </button>
                                                )}
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <div className="px-4 py-5 sm:px-6 text-center text-sm text-gray-500 italic">
                                    No attachments uploaded yet.
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    {/* Timeline */}
                    <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div className="px-4 py-5 sm:px-6">
                            <h3 className="text-lg leading-6 font-medium text-gray-900">Timeline</h3>
                        </div>
                        <div className="border-t border-gray-200 px-4 py-5 sm:px-6">
                            <NCRTimeline timeline={ncr.timeline || []} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default NCRDetail;
