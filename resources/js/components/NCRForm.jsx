import React, { useState, useEffect } from 'react';
import api from '../services/api';
import SignaturePad from './SignaturePad';

const NCRForm = ({ initialData = {}, onSubmit, loading, errors: externalErrors = {}, submitLabel = 'Submit', onCancel }) => {
    const [formData, setFormData] = useState({
        // Header Info
        ncr_number: '', // Auto-generated usually, but display if exists
        line_no: '',
        date_found: new Date().toISOString().split('T')[0], // "Date of NCR issued"
        issued_date: new Date().toISOString().split('T')[0], // Distinct issued date?
        receiver_dept_id: '',
        defect_category_id: '', // "Defect Group"
        last_ncr_no: '',

        // Project Info
        project_sn: '',
        project_name: '',
        order_number: '', // Hidden/Mapped if needed
        customer_name: '', // Hidden/Mapped if needed

        // Finder Info
        part_name: '', // "Part Name"
        product_description: '', // Mapped to Part Name if needed or separate
        finder_dept_id: '',
        location_found: '', // Finder Name/Dept usually implies location too

        // Description
        defect_description: '', // "NCR Description"
        defect_mode: '', 
        severity_level_id: '', // Not in image but required by backend
        quantity_affected: '',
        defect_location: '',

        // Corrective Action (CA)
        disposition_method_id: '', // "Decision"
        assigned_pic_id: '', // "PIC of CA"
        immediate_action: '', // "Corrected Action"
        containment_action: '',

        // Cost Analysis
        mh_used: '',
        mh_rate: '',
        labor_cost: '',
        material_cost: '',
        subcont_cost: '',
        engineering_cost: '',
        other_cost: '',
        total_cost: '',
        ca_finish_date: '',
        days_passed: '',

        // RCA & PA
        root_cause: '',
        preventive_action: '',
        status: 'Draft',
        related_document: '',
        closed_at: '', // "Closing NCR" date
        
        is_asme_project: false,
        asme_code_reference: '',

        // Verification & IR (Phase 10)
        verification_remarks: '',
        effectiveness_verified: false,
        evaluation_sustainability_verified: false,
        evaluation_issue_closed_3months: false,
        customer_approval_reference: false,
        ir_required: null,
        ir_no: '',
    });

    const [masterData, setMasterData] = useState({
        departments: [],
        defectCategories: [],
        severityLevels: [],
        dispositionMethods: [],
        users: [], // For PIC
        defectModes: [] // New: Defect Modes
    });

    const [filteredDefectModes, setFilteredDefectModes] = useState([]); // Filtered by Category
    const [userInitials, setUserInitials] = useState(''); // Current User Initials
    const [currentUser, setCurrentUser] = useState(null);
    const [signatureUrl, setSignatureUrl] = useState(null);

    useEffect(() => {
        const fetchMasterData = async () => {
            try {
                // Try to fetch master data. Note: Adjust endpoints if needed based on actual API routes
                const [deptsRes, catsRes, severitiesRes, dispRes, modesRes, userRes, usersListRes] = await Promise.all([
                    api.get('/departments').catch(err => { console.error('Depts Error:', err); return { data: { data: [] } }; }), 
                    api.get('/defect-categories').catch(err => { console.error('Cats Error:', err); return { data: { data: [] } }; }),
                    api.get('/severity-levels').catch(err => { console.error('Sev Error:', err); return { data: { data: [] } }; }),
                    api.get('/disposition-methods').catch(err => { console.error('Disp Error:', err); return { data: { data: [] } }; }),
                    api.get('/defect-modes').catch(err => { console.error('Modes Error:', err); return { data: { data: [] } }; }), // Fetch modes
                    api.get('/auth/me').catch(() => ({ data: { data: { name: '' } } })), // Fetch current user
                    api.get('/master/users').catch(() => ({ data: { data: [] } })), // Fetch all users for PIC
                ]);
                
                // Set Initials
                const me = userRes.data.data || null;
                setCurrentUser(me);
                setSignatureUrl(me?.signature_url || null);
                const userName = me?.name || '';
                if (userName) {
                    const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 3);
                    setUserInitials(initials);
                }

                setMasterData({
                    departments: deptsRes.data.data || [],
                    defectCategories: catsRes.data.data || [],
                    severityLevels: severitiesRes.data.data || [],
                    dispositionMethods: dispRes.data.data || [],
                    defectModes: modesRes.data.data || [],
                    users: usersListRes.data.data || [] // Store users
                });
            } catch (error) {
                console.error('Error fetching master data:', error);
            }
        };

        fetchMasterData();
    }, []);

    // Filter Defect Modes when Category changes
    useEffect(() => {
        if (formData.defect_category_id) {
            // Convert to string for comparison if IDs are different types
            const filtered = masterData.defectModes.filter(m => String(m.defect_category_id) === String(formData.defect_category_id));
            console.log('Filtering Modes:', { 
                selectedCatId: formData.defect_category_id, 
                totalModes: masterData.defectModes.length, 
                filteredCount: filtered.length 
            });
            setFilteredDefectModes(filtered);
        } else {
            setFilteredDefectModes([]);
        }
    }, [formData.defect_category_id, masterData.defectModes]);

    useEffect(() => {
        if (Object.keys(initialData).length > 0) {
            setFormData(prev => ({
                ...prev,
                ...initialData,
                // Ensure dates are formatted YYYY-MM-DD
                date_found: (initialData.dateFound || initialData.date_found) ? String(initialData.dateFound || initialData.date_found).split('T')[0] : prev.date_found,
                issued_date: (initialData.issuedDate || initialData.issued_date) ? String(initialData.issuedDate || initialData.issued_date).split('T')[0] : prev.issued_date,
                ca_finish_date: (initialData.caFinishDate || initialData.ca_finish_date) ? String(initialData.caFinishDate || initialData.ca_finish_date).split('T')[0] : prev.ca_finish_date,
                closed_at: (initialData.closedAt || initialData.closed_at) ? String(initialData.closedAt || initialData.closed_at).split('T')[0] : prev.closed_at,
                // Map snake_case if initialData uses camelCase (resource dependent)
                finder_dept_id: initialData.finderDeptId || initialData.finder_dept_id || prev.finder_dept_id,
                receiver_dept_id: initialData.receiverDeptId || initialData.receiver_dept_id || prev.receiver_dept_id,
                defect_category_id: initialData.defectCategoryId || initialData.defect_category_id || prev.defect_category_id,
                severity_level_id: initialData.severityLevelId || initialData.severity_level_id || prev.severity_level_id,
                disposition_method_id: initialData.dispositionMethodId || initialData.disposition_method_id || prev.disposition_method_id,
                assigned_pic_id: initialData.assignedPicId || initialData.assigned_pic_id || prev.assigned_pic_id, // Map PIC
                status: initialData.status || prev.status, // Ensure status is mapped
                asme_code_reference: initialData.asmeCodeReference || initialData.asme_code_reference || prev.asme_code_reference,
                
                // New Fields Mapping (CamelCase -> Snake_case)
                line_no: initialData.lineNo || initialData.line_no || prev.line_no,
                project_name: initialData.projectName || initialData.project_name || prev.project_name,
                customer_name: initialData.customerName || initialData.customer_name || prev.customer_name,
                drawing_number: initialData.drawingNumber || initialData.drawing_number || prev.drawing_number,
                quantity_affected: initialData.quantityAffected || initialData.quantity_affected || prev.quantity_affected,
                order_number: initialData.orderNumber || initialData.order_number || prev.order_number,
                last_ncr_no: initialData.lastNcrNo || initialData.last_ncr_no || prev.last_ncr_no,
                project_sn: initialData.projectSn || initialData.project_sn || prev.project_sn,
                part_name: initialData.partName || initialData.part_name || prev.part_name,
                defect_mode: initialData.defectMode || initialData.defect_mode || prev.defect_mode,
                defect_location: initialData.defectLocation || initialData.defect_location || prev.defect_location,
                defect_description: initialData.defectDescription || initialData.defect_description || prev.defect_description,
                immediate_action: initialData.immediateAction || initialData.immediate_action || prev.immediate_action,
                
                // Costs
                mh_used: initialData.mhUsed || initialData.mh_used || prev.mh_used,
                mh_rate: initialData.mhRate || initialData.mh_rate || prev.mh_rate,
                labor_cost: initialData.laborCost || initialData.labor_cost || prev.labor_cost,
                material_cost: initialData.materialCost || initialData.material_cost || prev.material_cost,
                subcont_cost: initialData.subcontCost || initialData.subcont_cost || prev.subcont_cost,
                engineering_cost: initialData.engineeringCost || initialData.engineering_cost || prev.engineering_cost,
                other_cost: initialData.otherCost || initialData.other_cost || prev.other_cost,
                total_cost: initialData.totalCost || initialData.total_cost || prev.total_cost,
                
                // CA & RCA
                days_passed: initialData.daysPassed || initialData.days_passed || prev.days_passed,
                root_cause: initialData.rootCause || initialData.root_cause || prev.root_cause,
                preventive_action: initialData.preventiveAction || initialData.preventive_action || prev.preventive_action,
                related_document: initialData.relatedDocument || initialData.related_document || prev.related_document,

                // Verification & IR
                verification_remarks: initialData.verificationRemarks || initialData.verification_remarks || prev.verification_remarks,
                effectiveness_verified: (initialData.effectivenessVerified ?? initialData.effectiveness_verified) ?? prev.effectiveness_verified,
                evaluation_sustainability_verified: (initialData.evaluationSustainabilityVerified ?? initialData.evaluation_sustainability_verified) ?? prev.evaluation_sustainability_verified,
                evaluation_issue_closed_3months: (initialData.evaluationIssueClosed3Months ?? initialData.evaluation_issue_closed_3months) ?? prev.evaluation_issue_closed_3months,
                customer_approval_reference: (initialData.customerApprovalReference ?? initialData.customer_approval_reference) ?? prev.customer_approval_reference,
                ir_required: (() => {
                    const val = initialData.irRequired ?? initialData.ir_required;
                    if (val === true || val === 'true' || val === 1 || val === '1') return true;
                    if (val === false || val === 'false' || val === 0 || val === '0') return false;
                    return prev.ir_required;
                })(),
                ir_no: initialData.irNo || initialData.ir_no || prev.ir_no,
            }));
        }
    }, [initialData]);

    const handleOpenDocument = () => {
        if (formData.related_document) {
            if (formData.related_document.startsWith('http')) {
                window.open(formData.related_document, '_blank');
            } else {
                alert(`Document Reference: ${formData.related_document}`);
            }
        } else {
            alert('No document related to this NCR.');
        }
    };

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => {
            const newData = { ...prev };
            if (type === 'checkbox') {
                newData[name] = checked;
            } else if (name === 'ir_required') {
                if (value === 'true') newData.ir_required = true;
                else if (value === 'false') newData.ir_required = false;
                else newData.ir_required = null;
                if (newData.ir_required === false) newData.ir_no = '';
            } else if (name === 'effectiveness_verified') {
                newData.effectiveness_verified = value === 'true';
            } else {
                newData[name] = value;
            }
            
            // Auto-calculate Total Cost
            if (['mh_used', 'mh_rate', 'material_cost', 'subcont_cost', 'engineering_cost', 'other_cost'].includes(name)) {
                const mhCost = (parseFloat(newData.mh_used) || 0) * (parseFloat(newData.mh_rate) || 0);
                const matCost = parseFloat(newData.material_cost) || 0;
                const subCost = parseFloat(newData.subcont_cost) || 0;
                const engCost = parseFloat(newData.engineering_cost) || 0;
                const otherCost = parseFloat(newData.other_cost) || 0;
                newData.labor_cost = mhCost.toFixed(2);
                newData.total_cost = (mhCost + matCost + subCost + engCost + otherCost).toFixed(2);
            }

            // Auto-calculate Days Passed
            if (['date_found', 'ca_finish_date'].includes(name) && newData.date_found && newData.ca_finish_date) {
                const start = new Date(newData.date_found);
                const end = new Date(newData.ca_finish_date);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                newData.days_passed = diffDays;
            }

            return newData;
        });
    };

    const uploadSignatureFile = async (file) => {
        if (!file) return;
        const fd = new FormData();
        fd.append('signature', file);
        const res = await api.post('/auth/signature', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        setSignatureUrl(res.data.data?.signature_url || null);
    };

    const uploadSignatureData = async (dataUrl) => {
        if (!dataUrl) return;
        const res = await api.post('/auth/signature', { signature_data: dataUrl });
        setSignatureUrl(res.data.data?.signature_url || null);
    };

    const deleteSignature = async () => {
        await api.delete('/auth/signature');
        setSignatureUrl(null);
    };

    const canShowVerification = () => {
        const roleName = (currentUser?.role?.name || currentUser?.role?.role_name || '').toLowerCase();
        const roleDisplay = (currentUser?.role?.display_name || '').toLowerCase();
        return roleName.includes('qc') || roleDisplay.includes('qc') || roleName.includes('admin') || roleDisplay.includes('admin');
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        // Calculate days passed if dates are present
        // (Optional: perform client-side logic before submit)
        onSubmit(formData);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6 text-sm">
            {externalErrors.general && (
                <div className="rounded-md bg-red-50 p-4 mb-4">
                    <div className="flex">
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-red-800">{externalErrors.general}</h3>
                        </div>
                    </div>
                </div>
            )}

            {/* Section 1: Non Conformance Report (NCR) */}
            <div className="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 border-t-4 border-blue-600">
                <h3 className="text-lg leading-6 font-bold text-gray-900 mb-4 text-blue-800 border-b pb-2">
                    Non Conformance Report (NCR)
                </h3>
                
                <div className="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-12">
                    {/* Row 1: Line No, Date, Receiver Dept, Defect Group, NCR No, Last NCR No */}
                    <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-blue-700">Line No.</label>
                        <input type="text" name="line_no" value={formData.line_no || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" placeholder="Production Line / Item No" />
                    </div>

                    <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-blue-700">Date of NCR issued</label>
                        <input type="date" name="issued_date" value={formData.issued_date || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-blue-700">Date Found (Internal)</label>
                        <input type="date" name="date_found" value={formData.date_found || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" required />
                    </div>

                    <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-blue-700">NCR Number</label>
                        <input type="text" name="ncr_number" value={formData.ncr_number || ''} disabled className="mt-1 block w-full shadow-sm sm:text-sm bg-gray-100 border-gray-300 rounded-md border p-1" placeholder="Auto" />
                    </div>

                     <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-blue-700">Last NCR No</label>
                        <input type="text" name="last_ncr_no" value={formData.last_ncr_no || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    {/* Row 2: Receiver Dept, Defect Group (Category) */}
                    <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Receiver Dept.</label>
                        <select name="receiver_dept_id" value={formData.receiver_dept_id || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" required>
                            <option value="">Select Dept</option>
                            {masterData.departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                        </select>
                         {externalErrors.receiver_dept_id && <p className="text-xs text-red-600">{externalErrors.receiver_dept_id[0]}</p>}
                    </div>

                     <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Defect Group</label>
                        <select name="defect_category_id" value={formData.defect_category_id || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" required>
                            <option value="">Select Group</option>
                            {masterData.defectCategories.map(c => <option key={c.id} value={c.id}>{c.category_name}</option>)}
                        </select>
                        {externalErrors.defect_category_id && <p className="text-xs text-red-600">{externalErrors.defect_category_id[0]}</p>}
                    </div>
                    
                    <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Severity Level (Required)</label>
                        <select name="severity_level_id" value={formData.severity_level_id || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" required>
                            <option value="">Select Severity</option>
                            {masterData.severityLevels.map(s => <option key={s.id} value={s.id}>{s.level_name}</option>)}
                        </select>
                    </div>

                    {/* Row 3: Project / SN, Proj. Name */}
                    <div className="sm:col-span-6">
                        <label className="block text-xs font-bold text-blue-700">Project / SN</label>
                        <input type="text" name="project_sn" value={formData.project_sn || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-6">
                        <label className="block text-xs font-bold text-blue-700">Proj. Name</label>
                        <input type="text" name="project_name" value={formData.project_name || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    <div className="sm:col-span-6">
                        <label className="block text-xs font-bold text-blue-700">Customer</label>
                        <input type="text" name="customer_name" value={formData.customer_name || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-6">
                        <label className="block text-xs font-bold text-blue-700">Order No. (PO)</label>
                        <input type="text" name="order_number" value={formData.order_number || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    {/* Row 4: Part Name, Finder Name, Finder Dept */}
                    <div className="sm:col-span-4">
                        <label className="block text-xs font-bold text-blue-700">Part Name</label>
                        <input type="text" name="part_name" value={formData.part_name || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-4">
                        <label className="block text-xs font-bold text-blue-700">Drawing No.</label>
                        <input type="text" name="drawing_number" value={formData.drawing_number || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-4">
                        <label className="block text-xs font-bold text-blue-700">Quantity Affected</label>
                        <input type="number" name="quantity_affected" value={formData.quantity_affected || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    <div className="sm:col-span-6">
                        <label className="block text-xs font-bold text-blue-700">Finder Name</label>
                        <input type="text" disabled value={initialData.created_by_user?.name || 'Current User'} className="mt-1 block w-full shadow-sm sm:text-sm bg-gray-100 border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-6">
                         <label className="block text-xs font-bold text-blue-700">Finder's Dept.</label>
                         <select name="finder_dept_id" value={formData.finder_dept_id || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" required>
                            <option value="">Select Dept</option>
                            {masterData.departments.map(d => <option key={d.id} value={d.id}>{d.department_name}</option>)}
                        </select>
                    </div>

                    {/* Row 5: NCR Description */}
                    <div className="sm:col-span-12">
                        <label className="block text-xs font-bold text-blue-700">NCR Description</label>
                        <input type="text" name="defect_description" value={formData.defect_description || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" required />
                    </div>

                    {/* Row 6: Defect Mode */}
                    <div className="sm:col-span-6">
                        <label className="block text-xs font-bold text-blue-700">Defect Mode</label>
                        <select name="defect_mode" value={formData.defect_mode || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" disabled={!formData.defect_category_id}>
                            <option value="">
                                {formData.defect_category_id 
                                    ? `Select Mode (${filteredDefectModes.length} available)` 
                                    : 'Select Group First'}
                            </option>
                            {filteredDefectModes.map(mode => (
                                <option key={mode.id} value={mode.mode_name}>{mode.mode_name}</option>
                            ))}
                        </select>
                    </div>

                    {/* Defect Area Checkboxes */}
                    <div className="sm:col-span-6">
                        <label className="block text-xs font-bold text-blue-700">Defect Area</label>
                        <div className="mt-2 flex space-x-6">
                            <label className="inline-flex items-center">
                                <input 
                                    type="radio" 
                                    name="defect_location" 
                                    value="Workshop" 
                                    checked={formData.defect_location === 'Workshop'} 
                                    onChange={handleChange}
                                    className="form-radio h-4 w-4 text-blue-600" 
                                />
                                <span className="ml-2 text-sm text-gray-700">Workshop</span>
                            </label>
                            <label className="inline-flex items-center">
                                <input 
                                    type="radio" 
                                    name="defect_location" 
                                    value="Supplier" 
                                    checked={formData.defect_location === 'Supplier'} 
                                    onChange={handleChange}
                                    className="form-radio h-4 w-4 text-blue-600" 
                                />
                                <span className="ml-2 text-sm text-gray-700">S/C or Supplier</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {/* Section 2: Corrective Action (CA) */}
            <div className="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 border-t-4 border-blue-600">
                <h3 className="text-lg leading-6 font-bold text-gray-900 mb-4 text-blue-800 border-b pb-2">
                    Corrective Action (CA)
                </h3>
                <div className="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-12">
                    {/* Decision, PIC */}
                    <div className="sm:col-span-4">
                        <label className="block text-xs font-bold text-blue-700">Decision</label>
                        <select name="disposition_method_id" value={formData.disposition_method_id || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1">
                             <option value="">Select Decision</option>
                             {masterData.dispositionMethods.map(m => <option key={m.id} value={m.id}>{m.method_name}</option>)}
                        </select>
                    </div>
                    <div className="sm:col-span-8">
                        <label className="block text-xs font-bold text-blue-700">PIC of CA</label>
                        <select name="assigned_pic_id" value={formData.assigned_pic_id || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1">
                             <option value="">Select PIC</option>
                             {masterData.users && masterData.users.map(u => (
                                <option key={u.id} value={u.id}>{u.name} ({u.department?.department_name || '-'})</option>
                             ))}
                        </select>
                    </div>

                    {/* Corrected Action */}
                    <div className="sm:col-span-12">
                        <label className="block text-xs font-bold text-blue-700">Corrected Action</label>
                        <input type="text" name="immediate_action" value={formData.immediate_action || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    {/* Cost Analysis Row 1 */}
                    <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-blue-700">MH used</label>
                        <input type="number" step="0.1" name="mh_used" value={formData.mh_used || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="block text-xs font-bold text-blue-700">MH rate, USD</label>
                        <input type="number" step="0.01" name="mh_rate" value={formData.mh_rate || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Labor Cost, USD</label>
                        <input type="number" step="0.01" name="labor_cost" value={formData.labor_cost || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1 bg-yellow-50" />
                    </div>
                    <div className="sm:col-span-5">
                         <label className="block text-xs font-bold text-blue-700">Cost for Material, USD</label>
                        <input type="number" step="0.01" name="material_cost" value={formData.material_cost || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    {/* Cost Analysis Row 2 */}
                    <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Cost for Subcont</label>
                        <input type="number" step="0.01" name="subcont_cost" value={formData.subcont_cost || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Cost for Eng.</label>
                        <input type="number" step="0.01" name="engineering_cost" value={formData.engineering_cost || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Cost for Other</label>
                        <input type="number" step="0.01" name="other_cost" value={formData.other_cost || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                    <div className="sm:col-span-3">
                        <label className="block text-xs font-bold text-blue-700">Total Cost, USD</label>
                        <input type="number" step="0.01" name="total_cost" value={formData.total_cost || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1 bg-yellow-50" />
                    </div>

                    {/* Finish Date */}
                    <div className="sm:col-span-4">
                         <label className="block text-xs font-bold text-blue-700">CA Finish, actual</label>
                         <div className="flex items-center">
                            <input type="date" name="ca_finish_date" value={formData.ca_finish_date || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                            <span className="mx-2">=</span>
                         </div>
                    </div>
                    <div className="sm:col-span-4">
                        <label className="block text-xs font-bold text-blue-700">days passed until today</label>
                        <div className="flex items-center">
                            <input type="number" name="days_passed" value={formData.days_passed || ''} onChange={handleChange} className="mt-1 block w-20 shadow-sm sm:text-sm border-gray-300 rounded-md border p-1 bg-yellow-50" />
                            <span className="ml-2 text-xs text-gray-500">days</span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Section 3: TI Deployment, RCA and PA */}
            <div className="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 border-t-4 border-blue-600">
                <h3 className="text-lg leading-6 font-bold text-gray-900 mb-4 text-blue-800 border-b pb-2">
                    TI Deployment, RCA and PA
                </h3>
                <div className="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-12">
                    <div className="sm:col-span-12">
                        <label className="block text-xs font-bold text-blue-700">Root Cause</label>
                        <textarea rows={2} name="root_cause" value={formData.root_cause || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    <div className="sm:col-span-12">
                        <label className="block text-xs font-bold text-blue-700">Preventive Action (PA)</label>
                         <textarea rows={2} name="preventive_action" value={formData.preventive_action || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>

                    <div className="sm:col-span-9">
                        <label className="block text-xs font-bold text-blue-700">Status NCR</label>
                        <div className="flex space-x-2 mt-1">
                             <select name="status" value={formData.status || 'Draft'} onChange={handleChange} className="block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1">
                                <option value="Draft">Draft</option>
                                <option value="Pending_Finder_Approval">Pending Finder Approval</option>
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                             </select>
                             <input 
                                type="text" 
                                className="block w-20 shadow-sm sm:text-sm bg-gray-100 border-gray-300 rounded-md border p-1 text-gray-700 text-center font-bold" 
                                placeholder="Initial" 
                                value={userInitials} // Auto-fill with current user initials
                                disabled 
                                title="Your Initials (Auto-filled)" 
                             />
                        </div>
                    </div>

                    <div className="sm:col-span-8">
                        <label className="block text-xs font-bold text-blue-700">Document Related NCR</label>
                        <input type="text" name="related_document" value={formData.related_document || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                     <div className="sm:col-span-4">
                        <label className="block text-xs font-bold text-blue-700">Closing NCR</label>
                        <input type="date" name="closed_at" value={formData.closed_at || ''} onChange={handleChange} className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1" />
                    </div>
                </div>
                
                 <div className="mt-6">
                    <button 
                        type="button" 
                        onClick={handleOpenDocument}
                        className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                    >
                        Open Document
                    </button>
                 </div>
            </div>

            {/* Section 4: Verification & IR */}
            {canShowVerification() && (
                <div className="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 border-t-4 border-blue-600">
                    <h3 className="text-lg leading-6 font-bold text-gray-900 mb-4 text-blue-800 border-b pb-2">
                        Verification & IR
                    </h3>
                    <div className="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-12">
                        <div className="sm:col-span-6">
                            <label className="block text-xs font-bold text-blue-700">Inspection / Verification Result</label>
                            <select
                                name="effectiveness_verified"
                                value={String(!!formData.effectiveness_verified)}
                                onChange={handleChange}
                                className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1"
                            >
                                <option value="false">Not OK</option>
                                <option value="true">OK</option>
                            </select>
                        </div>
                        <div className="sm:col-span-6">
                            <label className="block text-xs font-bold text-blue-700">Verified By</label>
                            <input
                                type="text"
                                value={currentUser?.name || ''}
                                disabled
                                className="mt-1 block w-full shadow-sm sm:text-sm bg-gray-100 border-gray-300 rounded-md border p-1"
                            />
                        </div>

                        <div className="sm:col-span-12">
                            <label className="block text-xs font-bold text-blue-700">Inspector's Comment / Notes</label>
                            <textarea
                                rows={3}
                                name="verification_remarks"
                                value={formData.verification_remarks || ''}
                                onChange={handleChange}
                                className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1"
                            />
                        </div>

                        <div className="sm:col-span-12">
                            <label className="block text-xs font-bold text-blue-700">Effectiveness Evaluation</label>
                            <div className="mt-2 space-y-2">
                                <label className="inline-flex items-start">
                                    <input
                                        type="checkbox"
                                        name="evaluation_sustainability_verified"
                                        checked={!!formData.evaluation_sustainability_verified}
                                        onChange={handleChange}
                                        className="mt-1"
                                    />
                                    <span className="ml-2 text-sm text-gray-700">
                                        Sustainability verified after 3 times or more applied, no same issue raised
                                    </span>
                                </label>
                                <label className="inline-flex items-start">
                                    <input
                                        type="checkbox"
                                        name="evaluation_issue_closed_3months"
                                        checked={!!formData.evaluation_issue_closed_3months}
                                        onChange={handleChange}
                                        className="mt-1"
                                    />
                                    <span className="ml-2 text-sm text-gray-700">
                                        Considered verified and closed since no same issue after 3 months
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div className="sm:col-span-6">
                            <label className="block text-xs font-bold text-blue-700">Customer approval reference</label>
                            <label className="mt-2 inline-flex items-center">
                                <input
                                    type="checkbox"
                                    name="customer_approval_reference"
                                    checked={!!formData.customer_approval_reference}
                                    onChange={handleChange}
                                />
                                <span className="ml-2 text-sm text-gray-700">Yes</span>
                            </label>
                        </div>

                        <div className="sm:col-span-6">
                            <label className="block text-xs font-bold text-blue-700">Need to raise improvement Request (IR)</label>
                            <div className="mt-2 flex space-x-6">
                                <label className="inline-flex items-center">
                                    <input
                                        type="radio"
                                        name="ir_required"
                                        value="true"
                                        checked={formData.ir_required === true}
                                        onChange={handleChange}
                                    />
                                    <span className="ml-2 text-sm text-gray-700">Yes</span>
                                </label>
                                <label className="inline-flex items-center">
                                    <input
                                        type="radio"
                                        name="ir_required"
                                        value="false"
                                        checked={formData.ir_required === false}
                                        onChange={handleChange}
                                    />
                                    <span className="ml-2 text-sm text-gray-700">No</span>
                                </label>
                            </div>
                        </div>

                        <div className="sm:col-span-12">
                            <label className="block text-xs font-bold text-blue-700">IR No.</label>
                            <input
                                type="text"
                                name="ir_no"
                                value={formData.ir_no || ''}
                                onChange={handleChange}
                                disabled={formData.ir_required !== true}
                                className="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md border p-1 disabled:bg-gray-100"
                            />
                        </div>

                        <div className="sm:col-span-12 border-t pt-4">
                            <label className="block text-xs font-bold text-blue-700">E-Signature (My Signature)</label>
                            <div className="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-12">
                                <div className="sm:col-span-6">
                                    <div className="border rounded-md p-3 bg-gray-50">
                                        {signatureUrl ? (
                                            <img src={signatureUrl} alt="signature" className="max-h-16" />
                                        ) : (
                                            <div className="text-sm text-gray-600">No signature uploaded</div>
                                        )}
                                        <div className="mt-3 flex gap-2">
                                            <input
                                                type="file"
                                                accept="image/*"
                                                onChange={(e) => uploadSignatureFile(e.target.files?.[0])}
                                                className="text-xs"
                                            />
                                            <button
                                                type="button"
                                                onClick={deleteSignature}
                                                className="px-3 py-1 border rounded-md text-xs bg-white hover:bg-gray-50"
                                                disabled={!signatureUrl}
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div className="sm:col-span-6">
                                    <SignaturePad onSave={uploadSignatureData} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            <div className="flex justify-end space-x-3">
                 <button
                    type="button"
                    onClick={onCancel}
                    className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={loading}
                    className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
                >
                    {loading ? 'Processing...' : submitLabel}
                </button>
            </div>
        </form>
    );
};

export default NCRForm;
