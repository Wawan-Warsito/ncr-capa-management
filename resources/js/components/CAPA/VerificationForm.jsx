import React, { useState } from 'react';
import Input from '../Input';
import DatePicker from '../DatePicker';
import Select from '../Select';
import Button from '../Button';

const VerificationForm = ({ verification, onChange, onSubmit }) => {
  const [localVerification, setLocalVerification] = useState(verification || {
    method: '',
    verified_by: '',
    verified_at: '',
    result: '',
    effectiveness: 'Pending', // Effective, Not Effective, Pending
    comments: ''
  });

  const handleChange = (e) => {
    const { name, value } = e.target;
    const updated = { ...localVerification, [name]: value };
    setLocalVerification(updated);
    onChange(updated);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (onSubmit) onSubmit(localVerification);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4 bg-white p-6 shadow sm:rounded-lg">
      <h3 className="text-lg font-medium text-gray-900">Verification of Effectiveness</h3>
      
      <div className="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
        <div className="sm:col-span-2">
            <Input
                label="Verification Method"
                id="method"
                value={localVerification.method}
                onChange={handleChange}
                placeholder="How was the effectiveness verified?"
                required
            />
        </div>
        
        <Input
            label="Verified By"
            id="verified_by"
            value={localVerification.verified_by}
            onChange={handleChange}
            required
        />

        <DatePicker
            label="Verification Date"
            id="verified_at"
            value={localVerification.verified_at}
            onChange={handleChange}
            required
        />

        <Select
            label="Effectiveness Result"
            id="effectiveness"
            value={localVerification.effectiveness}
            onChange={handleChange}
            options={[
                { value: 'Effective', label: 'Effective - Issue Resolved' },
                { value: 'Not Effective', label: 'Not Effective - Issue Persists' },
                { value: 'Pending', label: 'Pending Review' }
            ]}
            required
        />
        
        <div className="sm:col-span-2">
            <label htmlFor="comments" className="block text-sm font-medium text-gray-700">Comments / Evidence</label>
            <textarea
                id="comments"
                name="comments"
                rows={3}
                className="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md"
                value={localVerification.comments}
                onChange={handleChange}
            />
        </div>
      </div>

      <div className="flex justify-end">
        <Button type="submit">
            Submit Verification
        </Button>
      </div>
    </form>
  );
};

export default VerificationForm;
