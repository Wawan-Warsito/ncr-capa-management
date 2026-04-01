import React, { useState, useEffect } from 'react';
import api from '../services/api';
import SignaturePad from '../components/SignaturePad';

const MySignature = () => {
    const [signatureUrl, setSignatureUrl] = useState(null);
    const [loading, setLoading] = useState(true);
    const [message, setMessage] = useState({ type: '', text: '' });

    useEffect(() => {
        fetchSignature();
    }, []);

    const fetchSignature = async () => {
        try {
            setLoading(true);
            const res = await api.get('/auth/me');
            setSignatureUrl(res.data.data.signature_url);
        } catch (error) {
            console.error('Error fetching signature:', error);
            setMessage({ type: 'error', text: 'Failed to load signature.' });
        } finally {
            setLoading(false);
        }
    };

    const uploadSignatureFile = async (file) => {
        if (!file) return;
        const fd = new FormData();
        fd.append('signature', file);
        try {
            setLoading(true);
            const res = await api.post('/auth/signature', fd, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            setSignatureUrl(res.data.data.signature_url);
            setMessage({ type: 'success', text: 'Signature uploaded successfully!' });
        } catch (error) {
            console.error('Error uploading signature:', error);
            setMessage({ type: 'error', text: 'Failed to upload signature.' });
        } finally {
            setLoading(false);
        }
    };

    const uploadSignatureData = async (dataUrl) => {
        if (!dataUrl) return;
        try {
            setLoading(true);
            const res = await api.post('/auth/signature', { signature_data: dataUrl });
            setSignatureUrl(res.data.data.signature_url);
            setMessage({ type: 'success', text: 'Signature saved successfully!' });
        } catch (error) {
            console.error('Error saving signature:', error);
            setMessage({ type: 'error', text: 'Failed to save signature.' });
        } finally {
            setLoading(false);
        }
    };

    const deleteSignature = async () => {
        if (!confirm('Are you sure you want to delete your signature?')) return;
        try {
            setLoading(true);
            await api.delete('/auth/signature');
            setSignatureUrl(null);
            setMessage({ type: 'success', text: 'Signature deleted successfully!' });
        } catch (error) {
            console.error('Error deleting signature:', error);
            setMessage({ type: 'error', text: 'Failed to delete signature.' });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <h1 className="text-2xl font-bold text-gray-900 mb-6">My Signature</h1>
            
            {message.text && (
                <div className={`mb-4 p-4 rounded-md ${message.type === 'error' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'}`}>
                    {message.text}
                </div>
            )}

            <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                <div className="px-4 py-5 sm:px-6">
                    <h3 className="text-lg leading-6 font-medium text-gray-900">Digital Signature Management</h3>
                    <p className="mt-1 max-w-2xl text-sm text-gray-500">
                        Manage your digital signature for signing NCR and CAPA documents.
                    </p>
                </div>
                <div className="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <div className="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        {/* Current Signature */}
                        <div>
                            <h4 className="text-sm font-medium text-gray-700 mb-2">Current Signature</h4>
                            <div className="border-2 border-dashed border-gray-300 rounded-lg p-4 flex flex-col items-center justify-center h-48 bg-gray-50">
                                {loading ? (
                                    <span className="text-gray-400">Loading...</span>
                                ) : signatureUrl ? (
                                    <img src={signatureUrl} alt="Your Signature" className="max-h-full max-w-full object-contain" />
                                ) : (
                                    <span className="text-gray-400">No signature set</span>
                                )}
                            </div>
                            {signatureUrl && (
                                <button
                                    onClick={deleteSignature}
                                    className="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Remove Signature
                                </button>
                            )}
                        </div>

                        {/* Upload / Draw */}
                        <div className="space-y-6">
                            <div>
                                <h4 className="text-sm font-medium text-gray-700 mb-2">Upload Image</h4>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) => uploadSignatureFile(e.target.files[0])}
                                    className="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100"
                                />
                                <p className="mt-1 text-xs text-gray-500">PNG, JPG up to 2MB. Transparent background recommended.</p>
                            </div>

                            <div>
                                <h4 className="text-sm font-medium text-gray-700 mb-2">Draw Signature</h4>
                                <SignaturePad onSave={uploadSignatureData} height={150} />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default MySignature;
