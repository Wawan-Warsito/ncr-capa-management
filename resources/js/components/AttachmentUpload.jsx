import React, { useState } from 'react';
import { Upload, X, FileText } from 'lucide-react';
import api from '../services/api';

const AttachmentUpload = ({ entityId, entityType = 'ncr', onUploadSuccess }) => {
  const [files, setFiles] = useState([]);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState(null);

  const handleFileChange = (e) => {
    setFiles([...e.target.files]);
    setError(null);
  };

  const removeFile = (index) => {
    setFiles(files.filter((_, i) => i !== index));
  };

  const handleUpload = async () => {
    if (files.length === 0) return;

    setUploading(true);
    setError(null);

    const formData = new FormData();
    files.forEach((file) => {
      formData.append('attachments[]', file);
    });

    try {
      const endpoint = entityType === 'ncr' 
        ? `/ncrs/${entityId}/attachments` 
        : `/capas/${entityId}/attachments`; // Adjust if CAPA has different endpoint

      await api.post(endpoint, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      setFiles([]);
      if (onUploadSuccess) onUploadSuccess();
    } catch (err) {
      console.error('Upload failed:', err);
      setError('Failed to upload files. Please try again.');
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="bg-white shadow sm:rounded-lg p-6">
      <h3 className="text-lg font-medium leading-6 text-gray-900 mb-4">Attachments</h3>
      
      <div className="space-y-4">
        <div className="flex items-center justify-center w-full">
          <label htmlFor="dropzone-file" className="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
            <div className="flex flex-col items-center justify-center pt-5 pb-6">
              <Upload className="w-8 h-8 mb-3 text-gray-400" />
              <p className="mb-2 text-sm text-gray-500"><span className="font-semibold">Click to upload</span> or drag and drop</p>
              <p className="text-xs text-gray-500">PDF, PNG, JPG, DOC (MAX. 10MB)</p>
            </div>
            <input id="dropzone-file" type="file" className="hidden" multiple onChange={handleFileChange} />
          </label>
        </div>

        {files.length > 0 && (
          <ul className="divide-y divide-gray-200 border border-gray-200 rounded-md">
            {files.map((file, index) => (
              <li key={index} className="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                <div className="w-0 flex-1 flex items-center">
                  <FileText className="flex-shrink-0 h-5 w-5 text-gray-400" />
                  <span className="ml-2 flex-1 w-0 truncate">{file.name}</span>
                  <span className="text-gray-500 text-xs ml-2">{(file.size / 1024 / 1024).toFixed(2)} MB</span>
                </div>
                <div className="ml-4 flex-shrink-0">
                  <button
                    type="button"
                    onClick={() => removeFile(index)}
                    className="font-medium text-red-600 hover:text-red-500"
                  >
                    <X className="h-5 w-5" />
                  </button>
                </div>
              </li>
            ))}
          </ul>
        )}

        {error && <div className="text-sm text-red-600">{error}</div>}

        <div className="flex justify-end">
          <button
            type="button"
            onClick={handleUpload}
            disabled={files.length === 0 || uploading}
            className={`inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 ${
              (files.length === 0 || uploading) ? 'opacity-50 cursor-not-allowed' : ''
            }`}
          >
            {uploading ? 'Uploading...' : 'Upload Files'}
          </button>
        </div>
      </div>
    </div>
  );
};

export default AttachmentUpload;
