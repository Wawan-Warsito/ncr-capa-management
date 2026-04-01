import React from 'react';
import Breadcrumb from '../components/Breadcrumb';

const NotificationList = () => {
    // Mock data - in a real app this would come from an API
    const notifications = [
        {
            id: 1,
            title: 'New NCR Assigned',
            message: 'NCR #26.P00-QC-01 has been assigned to you.',
            time: '10 minutes ago',
            read: false,
            type: 'ncr'
        },
        {
            id: 2,
            title: 'CAPA Reminder',
            message: 'Action plan due for CAPA-2026-003.',
            time: '2 hours ago',
            read: false,
            type: 'capa'
        },
        {
            id: 3,
            title: 'System Update',
            message: 'The system will be down for maintenance on Sunday.',
            time: '1 day ago',
            read: true,
            type: 'system'
        }
    ];

    return (
        <div className="space-y-6">
            <Breadcrumb />
            
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-semibold text-gray-900">Notifications</h1>
                <button className="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Mark all as read
                </button>
            </div>

            <div className="bg-white shadow overflow-hidden sm:rounded-md">
                <ul className="divide-y divide-gray-200">
                    {notifications.length > 0 ? (
                        notifications.map((notification) => (
                            <li key={notification.id} className={`block hover:bg-gray-50 ${!notification.read ? 'bg-blue-50' : ''}`}>
                                <div className="px-4 py-4 sm:px-6">
                                    <div className="flex items-center justify-between">
                                        <p className={`text-sm font-medium truncate ${!notification.read ? 'text-blue-600' : 'text-gray-900'}`}>
                                            {notification.title}
                                        </p>
                                        <div className="ml-2 flex-shrink-0 flex">
                                            <p className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {notification.type.toUpperCase()}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="mt-2 sm:flex sm:justify-between">
                                        <div className="sm:flex">
                                            <p className="flex items-center text-sm text-gray-500">
                                                {notification.message}
                                            </p>
                                        </div>
                                        <div className="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                            <p>
                                                {notification.time}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        ))
                    ) : (
                        <li className="px-4 py-8 text-center text-gray-500">
                            No notifications found.
                        </li>
                    )}
                </ul>
            </div>
        </div>
    );
};

export default NotificationList;
