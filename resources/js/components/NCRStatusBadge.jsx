import React from 'react';

const NCRStatusBadge = ({ status }) => {
    const getStatusColor = (status) => {
        // Normalize status to handle potential inconsistencies
        const normalizedStatus = status ? status.toLowerCase() : '';

        switch (normalizedStatus) {
            case 'draft':
                return 'bg-gray-100 text-gray-800';
            case 'pending_finder_approval':
                return 'bg-yellow-100 text-yellow-800';
            case 'pending_qc_registration':
                return 'bg-orange-100 text-orange-800';
            case 'sent_to_receiver':
            case 'open':
                return 'bg-blue-100 text-blue-800';
            case 'pending_asme_review':
                return 'bg-purple-100 text-purple-800';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            case 'closed':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const formatStatus = (status) => {
        if (!status) return 'Unknown';
        return status.replace(/_/g, ' ');
    };

    return (
        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(status)}`}>
            {formatStatus(status)}
        </span>
    );
};

export default NCRStatusBadge;
