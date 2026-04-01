import React, { useState, useEffect, useRef } from 'react';
import { useParams } from 'react-router-dom';
import api from '../services/api';
import NCRPrint from '../components/NCRPrint';

const NCRPrintPage = () => {
    const { id } = useParams();
    const [ncr, setNcr] = useState(null);
    const [loading, setLoading] = useState(true);
    const printRef = useRef();

    useEffect(() => {
        const fetchNCR = async () => {
            try {
                const response = await api.get(`/ncrs/${id}`);
                setNcr(response.data.data);
            } catch (err) {
                console.error('Error fetching NCR:', err);
            } finally {
                setLoading(false);
            }
        };
        fetchNCR();
    }, [id]);

    const handlePrint = () => {
        window.print();
    };

    if (loading) return <div>Loading...</div>;
    if (!ncr) return <div>NCR not found</div>;

    return (
        <div className="min-h-screen bg-gray-100 p-8 print:p-0 print:bg-white">
            <div className="max-w-[210mm] mx-auto print:max-w-none print:mx-0">
                <div className="mb-4 flex justify-end print:hidden">
                    <button 
                        onClick={handlePrint}
                        className="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700"
                    >
                        Print / Save as PDF
                    </button>
                </div>
                <div className="bg-white shadow-lg print:shadow-none">
                    <NCRPrint ref={printRef} ncr={ncr} />
                </div>
            </div>
        </div>
    );
};

export default NCRPrintPage;
