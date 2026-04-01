import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';
import NCRTrendChart from '../components/NCRTrendChart';
import ParetoChart from '../components/ParetoChart';

const CompanyDashboard = () => {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchDashboardData = async () => {
            try {
                const response = await api.get('/dashboard/company');
                setData(response.data.data);
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchDashboardData();
    }, []);

    if (loading) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (!data) return null;

    const stats = [
        { name: 'Total NCRs', value: data.ncr_stats.total, link: '/ncrs', color: 'bg-blue-500' },
        { name: 'Open NCRs', value: data.ncr_stats.open, link: '/ncrs?status=open', color: 'bg-yellow-500' },
        { name: 'Overdue NCRs', value: data.ncr_stats.overdue, link: '/ncrs?status=overdue', color: 'bg-red-500' },
        { name: 'Open CAPAs', value: data.capa_stats.total - data.capa_stats.completed, link: '/capas?status=open', color: 'bg-green-500' },
    ];

    return (
        <div>
            <h1 className="text-2xl font-semibold text-gray-900 mb-6">Company Dashboard</h1>
            
            {/* Stats Grid */}
            <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                {stats.map((item) => (
                    <div key={item.name} className="bg-white overflow-hidden shadow rounded-lg transition hover:shadow-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <div className={`h-10 w-10 rounded-md ${item.color} flex items-center justify-center text-white font-bold text-lg`}>
                                        {item.name.charAt(0)}
                                    </div>
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">{item.name}</dt>
                                        <dd>
                                            <div className="text-lg font-medium text-gray-900">{item.value}</div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div className="bg-gray-50 px-5 py-3">
                            <div className="text-sm">
                                <Link to={item.link} className="font-medium text-blue-700 hover:text-blue-900 flex items-center">
                                    View all
                                    <span aria-hidden="true" className="ml-1">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Charts Section */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div className="bg-white shadow rounded-lg p-6 h-96">
                    <NCRTrendChart data={data.ncr_trend} />
                </div>
                <div className="bg-white shadow rounded-lg p-6 h-96">
                    <ParetoChart data={data.ncr_by_category} />
                </div>
            </div>

            {/* Recent Activity */}
            <div className="bg-white shadow rounded-lg mb-8">
                <div className="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 className="text-lg leading-6 font-medium text-gray-900">Recent Activity</h3>
                </div>
                <div className="px-4 py-5 sm:p-6 max-h-96 overflow-y-auto">
                    {data.recent_activities && data.recent_activities.length > 0 ? (
                        <ul className="divide-y divide-gray-200">
                            {data.recent_activities.map((activity) => (
                                <li key={activity.id} className="py-4">
                                    <div className="flex space-x-3">
                                        <div className="flex-1 space-y-1">
                                            <div className="flex items-center justify-between">
                                                <h3 className="text-sm font-medium">
                                                    <span className="font-bold text-gray-900">{activity.user ? activity.user.name : 'Unknown User'}</span>
                                                    <span className="text-gray-500 mx-1">performed</span>
                                                    <span className="font-semibold text-blue-600">{activity.action_type}</span>
                                                    {activity.entity_type && (
                                                        <span className="text-gray-600 ml-1">on {activity.entity_type} #{activity.entity_id}</span>
                                                    )}
                                                </h3>
                                                <p className="text-xs text-gray-500">{new Date(activity.performed_at).toLocaleString()}</p>
                                            </div>
                                            <p className="text-sm text-gray-500">{activity.action_description || 'No description available.'}</p>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    ) : (
                        <div className="text-center py-8">
                            <p className="text-gray-500 text-sm">No recent activity to display.</p>
                            <p className="text-xs text-gray-400 mt-1">Actions performed by users will appear here.</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default CompanyDashboard;
