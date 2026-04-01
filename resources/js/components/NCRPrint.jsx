import React, { forwardRef, useEffect, useState } from 'react';
import QRCode from 'qrcode';
import api from '../services/api';

const NCRPrint = forwardRef(({ ncr }, ref) => {
    if (!ncr) return null;

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }); // 20-Oct-25
    };

    // In NCRResource, it's mapped as `dispositionMethodId` and `dispositionMethod`.
    // Let's grab all possible variables.
    const methodId = ncr.dispositionMethodId || ncr.disposition_method_id || '';
    const methodObj = ncr.dispositionMethod || ncr.disposition_method || {};
    const methodName = (methodObj.method_name || methodObj.name || '').toString().toLowerCase();

    // The checkboxes condition based on ID and Name:
    // ID from database: 1=Use As Is, 3=Repaired, 7=Rejected
    const isRepaired = String(methodId) === '3' || methodName.includes('repair') || methodName.includes('repaired');
    const isRejected = String(methodId) === '7' || methodName.includes('reject') || methodName.includes('rejected');
    const isUseAsIs = String(methodId) === '1' || methodName.includes('use as is') || methodName.includes('use as it is');
    const finderSig = ncr.createdBy?.signature_url || ncr.created_by?.signature_url || null;
    const finderMgrSig = ncr.finderManager?.signature_url || ncr.finder_manager?.signature_url || null;
    const qcSig = ncr.qcManager?.signature_url || ncr.qc_manager?.signature_url || null;
    const receiverSig = ncr.assignedPic?.signature_url || ncr.assigned_pic?.signature_url || null;
    const receiverMgrSig = ncr.receiverManager?.signature_url || ncr.receiver_manager?.signature_url || null;
    const aiSig = ncr.ncrCoordinator?.signature_url || ncr.ncr_coordinator?.signature_url || null;
    const [qrDataUrl, setQrDataUrl] = useState(null);

    useEffect(() => {
        const gen = async () => {
            try {
                const res = await api.get(`/ncrs/${ncr.id}/public-link`);
                const url = res.data?.data?.url;
                const finalUrl = url || `${window.location.origin}/ncrs/${ncr.id}`;
                if (!finalUrl) return;
                const dataUrl = await QRCode.toDataURL(finalUrl, { margin: 0, width: 256 });
                setQrDataUrl(dataUrl);
            } catch {
                try {
                    const dataUrl = await QRCode.toDataURL(`${window.location.origin}/ncrs/${ncr.id}`, { margin: 0, width: 256 });
                    setQrDataUrl(dataUrl);
                } catch {}
            }
        };
        gen();
    }, [ncr]);

    return (
        <div ref={ref} className="p-8 bg-white text-black text-xs font-sans print:p-0">
            <style type="text/css" media="print">
                {`
                    @page { size: A4; margin: 10mm; }
                    body { -webkit-print-color-adjust: exact; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid black; padding: 4px; vertical-align: top; }
                    .no-border { border: none; }
                    .border-bottom { border-bottom: 1px solid black; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .bg-yellow { background-color: #ffff99; }
                    .h-24 { height: 6rem; }
                    .h-12 { height: 3rem; }
                `}
            </style>

            {/* Header */}
            <div className="flex justify-between items-center mb-2">
                <div className="text-xl font-bold text-blue-800">TOPSYSTEM</div>
                <div className="text-right text-[10px]">Form No. TAB-Q5-07-FO-02-06 Rev. 1</div>
            </div>

            <table className="w-full border border-black">
                <thead>
                    <tr>
                        <th colSpan="5" className="bg-yellow text-center text-lg font-bold py-2">
                            NON CONFORMANCE REPORT (NCR)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {/* Top Info Section */}
                    <tr>
                        <td colSpan="5" className="p-0 border-0">
                            <div className="flex items-start justify-between">
                                <table className="w-full border-none">
                                    <tbody>
                                        <tr>
                                            <td className="w-1/4 border-none py-1 px-2">Finder/Department</td>
                                            <td className="w-1/4 border-none py-1 px-2">: {ncr.finderDepartment?.department_name || ncr.finderDepartment?.departmentName || ncr.finder_department?.department_name || ncr.finder_department?.departmentName}</td>
                                            <td className="w-1/4 border-none py-1 px-2">NCR No.</td>
                                            <td className="w-1/4 border-none py-1 px-2">: {ncr.ncrNumber || ncr.ncr_number}</td>
                                        </tr>
                                        <tr>
                                            <td className="border-none py-1 px-2">To Receiver (Dept.)</td>
                                            <td className="border-none py-1 px-2">: {ncr.receiverDepartment?.department_name || ncr.receiverDepartment?.departmentName || ncr.receiver_department?.department_name || ncr.receiver_department?.departmentName}</td>
                                            <td className="border-none py-1 px-2">Date of Issue</td>
                                            <td className="border-none py-1 px-2">: {formatDate(ncr.issuedDate || ncr.issued_date || ncr.dateFound || ncr.date_found)}</td>
                                        </tr>
                                        <tr>
                                            <td className="border-none py-1 px-2">Project Name</td>
                                            <td className="border-none py-1 px-2">: {ncr.projectName || ncr.project_name}</td>
                                            <td className="border-none py-1 px-2">Order No.</td>
                                            <td className="border-none py-1 px-2">: {ncr.projectSn || ncr.project_sn}</td>
                                        </tr>
                                        <tr>
                                            <td className="border-none py-1 px-2">Customer</td>
                                            <td className="border-none py-1 px-2">: {ncr.customerName || ncr.customer_name}</td>
                                            <td className="border-none py-1 px-2">PO No.</td>
                                            <td className="border-none py-1 px-2">: {ncr.orderNumber || ncr.order_number}</td>
                                        </tr>
                                        <tr>
                                            <td className="border-none py-1 px-2">Line No.</td>
                                            <td className="border-none py-1 px-2">: {ncr.lineNo || ncr.line_no}</td>
                                            <td className="border-none py-1 px-2">Last NCR No.</td>
                                            <td className="border-none py-1 px-2">: {ncr.lastNcrNo || ncr.last_ncr_no}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div className="p-2 pr-3">
                                    <div className="border border-black w-28 h-28 flex items-center justify-center bg-white">
                                        {qrDataUrl ? <img src={qrDataUrl} alt="QR" className="w-24 h-24" /> : null}
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {/* Dwg/Part Info Header */}
                    <tr className="text-center font-bold bg-gray-100">
                        <td className="w-1/4">Dwg/Doc. No. :</td>
                        <td className="w-1/6">Rev.</td>
                        <td className="w-1/4">Part No.</td>
                        <td className="w-1/6">Q'ty</td>
                        <td className="w-1/4">Defect Area</td>
                    </tr>
                    <tr className="text-center">
                        <td>{ncr.drawingNumber || ncr.drawing_number || '-'}</td>
                        <td>-</td>
                        <td>{ncr.partName || ncr.part_name || ncr.productDescription || ncr.product_description || '-'}</td>
                        <td>{ncr.quantityAffected || ncr.quantity_affected || '-'}</td>
                        <td className="text-left text-[10px]">
                            <div className="flex items-center space-x-2">
                                <span><input type="checkbox" checked={ncr.defectLocation === 'Workshop' || ncr.defect_location === 'Workshop'} readOnly /> Workshop</span>
                                <span><input type="checkbox" checked={ncr.defectLocation === 'Supplier' || ncr.defect_location === 'Supplier'} readOnly /> S/C or Supplier</span>
                            </div>
                        </td>
                    </tr>

                    {/* Description Section */}
                    <tr>
                        <td colSpan="5" className="border-b-0">
                            <div className="font-bold mb-1">The Non Conformance is violates / contrary to :</div>
                            <div className="mb-1">
                                <span className="font-semibold">Defect Mode:</span> {ncr.defectMode || ncr.defect_mode}
                            </div>
                            <div className="font-bold underline mb-1">Description of Non-Conformance:</div>
                            <div className="min-h-[80px] whitespace-pre-wrap">{ncr.defectDescription || ncr.defect_description}</div>
                            <div className="mt-2">(See Attachment for detail)</div>
                        </td>
                    </tr>

                    {/* Signatures Row 1 */}
                    <tr>
                         <td colSpan="5" className="p-0 border-t-0">
                            <div className="flex justify-end mt-4 px-4 space-x-12">
                                <div className="text-center">
                                    <div className="border-b border-black w-32 mb-1 flex items-end justify-center h-10">
                                        {finderSig ? <img src={finderSig} alt="finder-sign" className="max-h-8" /> : ''}
                                    </div>
                                    <div className="text-[10px]">{ncr.createdBy?.name || ncr.created_by?.name || '________________'}</div>
                                    <div className="text-[10px]">Finder</div>
                                </div>
                                <div className="text-center">
                                    <div className="border-b border-black w-32 mb-1 flex items-end justify-center h-10">
                                        {finderMgrSig ? <img src={finderMgrSig} alt="finder-mgr-sign" className="max-h-8" /> : ''}
                                    </div>
                                    <div className="text-[10px]">{ncr.finderManager?.name || ncr.finder_manager?.name || '________________'}</div>
                                    <div className="text-[10px]">Finder's Manager</div>
                                </div>
                            </div>
                         </td>
                    </tr>

                    {/* Validity Check */}
                    <tr>
                        <td colSpan="5" className="py-2">
                            <div className="flex items-center justify-between px-8">
                                <div className="space-x-8">
                                    <label><input type="checkbox" checked={ncr.status !== 'Rejected'} readOnly /> Valid</label>
                                    <label><input type="checkbox" checked={ncr.status === 'Rejected'} readOnly /> Invalid</label>
                                </div>
                                <div className="flex items-center">
                                    <span className="mr-2">Sign by QC Manager :</span>
                                    <div className="border-b border-black w-32 h-6 flex items-end justify-center">
                                        {qcSig ? <img src={qcSig} alt="qc-sign" className="max-h-5" /> : ''}
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {/* Disposition Decision */}
                    <tr>
                        <td colSpan="5" className="py-2 bg-gray-50">
                            <div className="mb-1">After discussed with all concern parties, the decision below must be finished on : {formatDate(ncr.caFinishDate || ncr.ca_finish_date)}</div>
                            <div className="flex justify-start space-x-12 px-8 mt-2">
                                <div className="flex flex-col space-y-1">
                                    <label><input type="checkbox" checked={isRepaired} readOnly /> Repaired</label>
                                    <label><input type="checkbox" checked={isUseAsIs} readOnly /> Use as it is</label>
                                </div>
                                <div className="flex flex-col space-y-1">
                                    <label><input type="checkbox" checked={isRejected} readOnly /> Rejected and make new</label>
                                </div>
                                <div className="flex flex-col space-y-1">
                                    <div className="font-bold text-xs">PIC of CA:</div>
                                    <div className="border-b border-black min-w-[100px]">{ncr.assignedPic?.name || ncr.assigned_pic?.name || '-'}</div>
                                </div>
                                <div className="flex-grow text-right mt-4">
                                     <div className="inline-block text-center">
                                        <div className="border-b border-black w-40 mb-1 flex items-end justify-center h-10">
                                            {aiSig ? <img src={aiSig} alt="ai-sign" className="max-h-8" /> : ''}
                                        </div>
                                        <div className="text-[10px]">Approved by AI (if ASME Stamp)</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {/* Receiver Comments & Cost */}
                    <tr>
                        <td colSpan="5" className="p-0">
                            <div className="flex">
                                <div className="w-2/3 border-r border-black p-2">
                                    <div className="font-bold mb-2">Receiver's Comments / Statements :</div>
                                    <div className="min-h-[100px]">{ncr.receiverAssignmentRemarks || ncr.receiver_assignment_remarks}</div>
                                    <div className="text-center mt-4">
                                        <div className="border-b border-black w-40 mx-auto mb-1 flex items-end justify-center h-10">
                                            {receiverSig ? <img src={receiverSig} alt="receiver-sign" className="max-h-8" /> : ''}
                                        </div>
                                        <div className="text-[10px]">Receiver</div>
                                    </div>
                                </div>
                                <div className="w-1/3 p-0">
                                    <table className="w-full h-full border-none">
                                        <thead>
                                            <tr><th className="text-center border-b border-black">Cost</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td className="border-b border-black flex justify-between"><span>Man hours :</span> <span>USD {ncr.laborCost || ncr.labor_cost || (ncr.mhUsed ? (ncr.mhUsed * (ncr.mhRate || 0)) : (ncr.mh_used ? (ncr.mh_used * (ncr.mh_rate || 0)) : 0))}</span></td></tr>
                                            <tr><td className="border-b border-black flex justify-between"><span>Material :</span> <span>USD {ncr.materialCost || ncr.material_cost || 0}</span></td></tr>
                                            <tr><td className="border-b border-black flex justify-between"><span>Subcont :</span> <span>USD {ncr.subcontCost || ncr.subcont_cost || 0}</span></td></tr>
                                            <tr><td className="border-b border-black flex justify-between"><span>Engineering :</span> <span>USD {ncr.engineeringCost || ncr.engineering_cost || 0}</span></td></tr>
                                            <tr><td className="border-b border-black flex justify-between"><span>Other :</span> <span>USD {ncr.otherCost || ncr.other_cost || 0}</span></td></tr>
                                            <tr><td className="font-bold flex justify-between"><span>Total :</span> <span>USD {ncr.totalCost || ncr.total_cost || 0}</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {/* Root Cause */}
                    <tr>
                        <td colSpan="5">
                            <div className="font-bold">Root Cause (stated by Receiver's Manager)</div>
                            <div className="min-h-[60px] whitespace-pre-wrap">{ncr.rootCause || ncr.root_cause}</div>
                        </td>
                    </tr>

                    {/* Preventive Action */}
                    <tr>
                        <td colSpan="5">
                            <div className="font-bold">Preventive Action (stated by Receiver's Manager)</div>
                            <div className="min-h-[60px] whitespace-pre-wrap">{ncr.preventiveAction || ncr.preventive_action}</div>
                        </td>
                    </tr>

                    {/* Note & Receiver Manager Sign */}
                    <tr>
                        <td colSpan="5" className="p-0">
                            <div className="flex items-end justify-between">
                                <div className="bg-gray-600 text-white px-2 py-1 text-[10px] w-2/3">
                                    Note : Receiver's Manager is responsible to complete this form and return it to QC Manager
                                </div>
                                <div className="text-center px-4 pb-1">
                                    <div className="border-b border-black w-40 mb-1 flex items-end justify-center h-10">
                                        {receiverMgrSig ? <img src={receiverMgrSig} alt="receiver-mgr-sign" className="max-h-8" /> : ''}
                                    </div>
                                    <div className="text-[10px]">{ncr.receiverManager?.name || ncr.receiver_manager?.name || '________________'}</div>
                                    <div className="text-[10px]">Receiver's Manager</div>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {/* Inspection / Verification */}
                    <tr>
                        <td colSpan="5" className="p-0">
                            <div className="flex border-b border-black">
                                <div className="w-1/2 p-1 border-r border-black">Inspection / Verification after Repairing</div>
                                <div className="w-1/4 p-1 border-r border-black">By : {ncr.verifiedBy?.name || ncr.verified_by_user?.name}</div>
                                <div className="w-1/4 p-1">Result : {(ncr.effectivenessVerified || ncr.effectiveness_verified) ? 'OK' : ''}</div>
                            </div>
                            <div className="p-2">
                                <div className="font-bold">Inspector's Comment / Notes :</div>
                                <div className="min-h-[40px]">{ncr.verificationRemarks || ncr.verification_remarks}</div>
                            </div>
                        </td>
                    </tr>

                    {/* Effectiveness Evaluation */}
                    <tr>
                        <td colSpan="5">
                            <div className="mb-1">Evaluation of effectiveness of the Preventive Action Taken. Ref. para 3.3.5 Proc. No. TAB-Q10-01-OP-01-01</div>
                            <div className="flex space-x-4 px-4">
                                <div className="flex items-start w-1/2">
                                    <input type="checkbox" className="mt-1 mr-2" checked={!!(ncr.evaluationSustainabilityVerified || ncr.evaluation_sustainability_verified)} readOnly />
                                    <span className="text-[10px] leading-tight text-blue-800">Sustainability of the preventive action has been verified after 3 (three) times or more applied, no same issue has raised.</span>
                                </div>
                                <div className="flex items-start w-1/2">
                                    <input type="checkbox" className="mt-1 mr-2" checked={!!(ncr.evaluationIssueClosed3Months || ncr.evaluation_issue_closed_3months)} readOnly />
                                    <span className="text-[10px] leading-tight text-blue-800">The issue considered verified and closed since there was no issue of same after 3 (three) months since the issue has raised.</span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {/* Final Status */}
                    <tr>
                        <td colSpan="5">
                            <div className="font-bold mb-2">Final Status of NCR :</div>
                            <div className="flex flex-wrap gap-4 px-8 mb-4">
                                <label><input type="checkbox" checked={ncr.status === 'Closed'} readOnly /> NCR closed</label>
                                <label><input type="checkbox" checked={!!(ncr.customerApprovalReference || ncr.customer_approval_reference)} readOnly /> Customer approval reference</label>
                                <label><input type="checkbox" checked={ncr.status === 'Open'} readOnly /> NCR still open</label>
                            </div>
                            <div className="flex items-center px-8 mb-4">
                                <span className="mr-4">Need to raise improvement Request (IR)</span>
                                <label className="mr-4"><input type="checkbox" checked={(ncr.irRequired ?? ncr.ir_required) === true} readOnly /> yes</label>
                                <label><input type="checkbox" checked={(ncr.irRequired ?? ncr.ir_required) === false} readOnly /> No</label>
                            </div>
                            <div className="flex items-center px-8 mb-2">
                                <span className="mr-2">If yes, IR No. :</span>
                                <div className="border-b border-black w-64">{ncr.irNo || ncr.ir_no || ''}</div>
                            </div>
                            
                            <div className="flex justify-between items-end mt-8 px-4">
                                <div>Reviewed and approved by :</div>
                                <div className="text-center">
                                    <div className="border-b border-black w-40 mb-1 flex items-end justify-center h-10">
                                        {qcSig ? <img src={qcSig} alt="qc-sign-bottom" className="max-h-8" /> : ''}
                                    </div>
                                    <div className="text-[10px]">QC Manager</div>
                                </div>
                                <div className="text-center">
                                    <div className="border-b border-black w-40 mb-1 flex items-end justify-center h-10">
                                        {aiSig ? <img src={aiSig} alt="ai-sign-bottom" className="max-h-8" /> : ''}
                                    </div>
                                    <div className="text-[10px]">AI (if ASME Stamp)</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div className="text-right text-[10px] mt-1">PT. Topsystem Asia Base</div>
        </div>
    );
});

export default NCRPrint;
