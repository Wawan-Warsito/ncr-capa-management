import React, { useState, useEffect } from 'react';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import SummaryCard from '../components/dashboard/SummaryCard';
import NCRTable from '../components/dashboard/NCRTable';
import { AlertCircle, Clock, CheckSquare, List } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

const PersonalDashboard = () => {
  const { user } = useAuth();
  const roleName = (user?.role?.role_name || user?.role?.name || user?.role || '').toString();
  const roleKey = roleName.trim().toLowerCase().replace(/\s+/g, ' ').replace(/[\s-]/g, '_');
  const isDeptManager = roleKey === 'department_manager';
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const response = await api.get('/dashboard/personal');
        setStats(response.data.data || response.data);
      } catch (err) {
        console.error('Error fetching dashboard data:', err);
        setError('Failed to load dashboard data.');
      } finally {
        setLoading(false);
      }
    };

    fetchDashboardData();
  }, []);

  // Transform API data to match component expectations
  const dashboardData = stats ? {
    pending_actions: isDeptManager
      ? ((stats.department_ncrs?.open || 0) + (stats.department_capas?.open || 0) + (stats.pending_approvals || 0))
      : ((stats.assigned_ncrs?.open || 0) + (stats.my_capas?.in_progress || 0) + (stats.my_capas?.pending_verification || 0)),
    assigned_ncrs: isDeptManager ? (stats.department_ncrs?.open || 0) : (stats.assigned_ncrs?.total || 0),
    assigned_capas: isDeptManager ? (stats.department_capas?.open || 0) : (stats.my_capas?.total || 0),
    pending_approvals: stats.pending_approvals || 0,
    my_recent_ncrs: isDeptManager ? (stats.department_recent_ncrs || []) : (stats.my_tasks?.ncrs || []),
    attention_items: [
        ...(stats.my_tasks?.ncrs || []).map(ncr => ({
            message: `NCR Due: ${ncr.ncrNumber || ncr.ncr_number}`,
            type: 'NCR',
            reference: ncr.ncrNumber || ncr.ncr_number,
            link: `/ncrs/${ncr.id}`
        })),
        ...(stats.my_tasks?.capas || []).map(capa => ({
            message: `CAPA Due: ${capa.capaNumber || capa.capa_number}`,
            type: 'CAPA',
            reference: capa.capaNumber || capa.capa_number,
            link: `/capas/${capa.id}`
        }))
    ]
  } : null;

  if (loading) return <div className="p-6 text-center">Loading dashboard...</div>;
  if (error) return <div className="p-6 text-center text-red-600">{error}</div>;

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-semibold text-gray-900">Personal Dashboard</h1>
        <div className="text-sm text-gray-500">My Tasks & Overview</div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <SummaryCard 
          title="My Pending Actions" 
          value={dashboardData?.pending_actions || 0} 
          icon={AlertCircle} 
          color="red"
        />
        <SummaryCard 
          title="Assigned NCRs" 
          value={dashboardData?.assigned_ncrs || 0} 
          icon={List} 
          color="blue"
        />
        <SummaryCard 
          title="Assigned CAPAs" 
          value={dashboardData?.assigned_capas || 0} 
          icon={CheckSquare} 
          color="yellow"
        />
        <SummaryCard 
          title="Pending Approvals" 
          value={dashboardData?.pending_approvals || 0} 
          icon={Clock} 
          color="purple"
        />
      </div>

      {/* Action Required Section */}
      <div className="bg-white shadow sm:rounded-lg">
        <div className="px-4 py-5 sm:px-6">
          <h3 className="text-lg leading-6 font-medium text-gray-900">Items Requiring Your Attention</h3>
        </div>
        <div className="border-t border-gray-200 p-4">
            {dashboardData?.attention_items && dashboardData.attention_items.length > 0 ? (
                <ul className="divide-y divide-gray-200">
                    {dashboardData.attention_items.map((item, index) => (
                        <li key={index} className="py-4 flex items-center justify-between">
                             <div>
                                <p className="text-sm font-medium text-gray-900">{item.message}</p>
                                <p className="text-sm text-gray-500">{item.type} - {item.reference}</p>
                             </div>
                             <a href={item.link} className="text-blue-600 hover:text-blue-800 text-sm font-medium">View</a>
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="text-sm text-gray-500">No urgent items requiring attention.</p>
            )}
        </div>
      </div>

      {/* Recent Activity / Assigned NCRs */}
      <NCRTable ncrs={dashboardData?.my_recent_ncrs || []} />
    </div>
  );
};

export default PersonalDashboard;
