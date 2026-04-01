import React, { useCallback } from 'react';
import { UploadCloud } from 'lucide-react';

const FileUpload = ({ onFileSelect, accept = '*/*', multiple = false, className = '', label = 'Upload Files' }) => {
  
  const handleFileChange = (e) => {
    if (e.target.files && e.target.files.length > 0) {
        if (multiple) {
            onFileSelect(Array.from(e.target.files));
        } else {
            onFileSelect(e.target.files[0]);
        }
    }
  };

  return (
    <div className={className}>
      <label className="block text-sm font-medium text-gray-700">{label}</label>
      <div className="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition-colors">
        <div className="space-y-1 text-center">
          <UploadCloud className="mx-auto h-12 w-12 text-gray-400" />
          <div className="flex text-sm text-gray-600">
            <label
              htmlFor="file-upload"
              className="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
            >
              <span>Upload a file</span>
              <input id="file-upload" name="file-upload" type="file" className="sr-only" onChange={handleFileChange} accept={accept} multiple={multiple} />
            </label>
            <p className="pl-1">or drag and drop</p>
          </div>
          <p className="text-xs text-gray-500">
            {accept === '*/*' ? 'Any file type' : accept} up to 10MB
          </p>
        </div>
      </div>
    </div>
  );
};

export default FileUpload;
