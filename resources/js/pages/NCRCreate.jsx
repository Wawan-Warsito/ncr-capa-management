import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';
import NCRForm from '../components/NCRForm';

const NCRCreate = () => {
    const navigate = useNavigate();
    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});

    const handleSubmit = async (formData) => {
        setLoading(true);
        setErrors({});

        try {
            // Ensure CSRF cookie is set before making POST request
            await api.get('/sanctum/csrf-cookie');
            
            await api.post('/ncrs', formData);
            navigate('/ncrs');
        } catch (error) {
            console.error('Error creating NCR:', error);
            if (error.response) {
                if (error.response.status === 422) {
                    setErrors(error.response.data.errors);
                } else if (error.response.status === 405) {
                    setErrors({ general: 'Method not allowed (405). Please check if you are logged in or refresh the page.' });
                } else {
                    setErrors({ general: `Error: ${error.response.status} - ${error.response.data.message || 'An unexpected error occurred.'}` });
                }
            } else {
                setErrors({ general: 'Network error or server not reachable.' });
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-3xl mx-auto">
            <div className="md:flex md:items-center md:justify-between mb-6">
                <div className="flex-1 min-w-0">
                    <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Create New NCR
                    </h2>
                </div>
            </div>

            <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                <div className="px-4 py-5 sm:p-6">
                    <NCRForm 
                        onSubmit={handleSubmit}
                        loading={loading}
                        errors={errors}
                        onCancel={() => navigate('/ncrs')}
                        submitLabel="Create NCR"
                    />
                </div>
            </div>
        </div>
    );
};

export default NCRCreate;
