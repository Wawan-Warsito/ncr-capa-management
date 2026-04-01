import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '../services/api';
import NCRForm from '../components/NCRForm';

const NCREdit = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [errors, setErrors] = useState({});
    const [ncr, setNcr] = useState({});

    useEffect(() => {
        const fetchNCR = async () => {
            try {
                const response = await api.get(`/ncrs/${id}`);
                setNcr(response.data.data);
            } catch (err) {
                console.error('Error fetching NCR:', err);
                if (err.response && err.response.status === 404) {
                    setErrors({ general: 'NCR not found' });
                } else {
                    // Fallback for dev/demo if API fails
                    // In real app, we might redirect or show error
                    setNcr({
                       id: id,
                       ncrNumber: `NCR-2025-00${id}`,
                       finderDepartment: { department_name: 'Production' },
                       defectDescription: 'Defective material found in batch A1',
                       dateFound: '2025-02-09',
                       productDescription: 'Widget X',
                       orderNumber: 'LOT-12345',
                       status: 'Draft' // Only draft NCRs should be editable usually
                    });
                }
            } finally {
                setLoading(false);
            }
        };

        fetchNCR();
    }, [id]);

    const handleSubmit = async (formData) => {
        setSubmitting(true);
        setErrors({});

        try {
            await api.put(`/ncrs/${id}`, formData);
            navigate(`/ncrs/${id}`);
        } catch (error) {
            if (error.response && error.response.status === 422) {
                setErrors(error.response.data.errors);
            } else {
                console.error('Error updating NCR:', error);
                const msg = error.response?.data?.message || 'An unexpected error occurred. Please try again.';
                setErrors({ general: msg });
            }
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) return <div className="p-12 flex justify-center"><div className="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div></div>;

    return (
        <div className="max-w-3xl mx-auto">
            <div className="md:flex md:items-center md:justify-between mb-6">
                <div className="flex-1 min-w-0">
                    <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                        Edit {ncr.ncrNumber || 'NCR'}
                    </h2>
                </div>
            </div>

            <div className="bg-white shadow overflow-hidden sm:rounded-lg">
                <div className="px-4 py-5 sm:p-6">
                    <NCRForm 
                        initialData={ncr}
                        onSubmit={handleSubmit}
                        loading={submitting}
                        errors={errors}
                        onCancel={() => navigate(`/ncrs/${id}`)}
                        submitLabel="Update NCR"
                    />
                </div>
            </div>
        </div>
    );
};

export default NCREdit;
