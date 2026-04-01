import React, { useState, useEffect } from 'react';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';
import SummaryCard from '../components/dashboard/SummaryCard';
import TrendChart from '../components/dashboard/TrendChart';
import ParetoChart from '../components/dashboard/ParetoChart';
import NCRTable from '../components/dashboard/NCRTable';
import { AlertCircle, CheckCircle, Clock, FileText } from 'lucide-react';

const DepartmentDashboard = () => {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const response = await api.get('/dashboard/department');
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

  if (loading) return <div className="p-6 text-center">Loading dashboard...</div>;
  if (error) return <div className="p-6 text-center text-red-600">{error}</div>;

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-semibold text-gray-900">Department Dashboard</h1>
        <div className="text-sm text-gray-500">
          Overview for {stats?.department_name || 'Your Department'}
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <SummaryCard 
          title="Open NCRs" 
          value={stats?.open_ncrs || 0} 
          icon={AlertCircle} 
          color="red"
        />
        <SummaryCard 
          title="Open CAPAs" 
          value={stats?.open_capas || 0} 
          icon={FileText} 
          color="yellow"
        />
        <SummaryCard 
          title="Completed (This Month)" 
          value={stats?.completed_this_month || 0} 
          icon={CheckCircle} 
          color="green"
        />
        <SummaryCard 
          title="Avg Resolution Time" 
          value={`${stats?.avg_resolution_time || 0} days`} 
          icon={Clock} 
          color="blue"
        />
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <TrendChart data={stats?.monthly_trend || []} title="Department NCR Trend (6 Months)" />
        <ParetoChart data={stats?.defect_pareto || []} title="Top Defects by Category" />
      </div>

      {/* Recent NCRs Table */}
      <NCRTable ncrs={stats?.recent_ncrs || []} />
    </div>
  );
};

export default DepartmentDashboard;
