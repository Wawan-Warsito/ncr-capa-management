import React from 'react';
import { Link } from 'react-router-dom';
import Breadcrumb from '../components/Breadcrumb';
import { FileText, BarChart2, PieChart, Layers } from 'lucide-react';

const ReportList = () => {
  const reports = [
    {
      id: 'ncr-summary',
      title: 'NCR Summary Report',
      description: 'Overview of all Non-Conformance Reports with status and department breakdown.',
      icon: <FileText className="h-8 w-8 text-blue-500" />,
      link: '/reports/builder?type=ncr-summary'
    },
    {
      id: 'capa-effectiveness',
      title: 'CAPA Effectiveness Report',
      description: 'Analysis of Corrective and Preventive Actions effectiveness and closure rates.',
      icon: <Layers className="h-8 w-8 text-green-500" />,
      link: '/reports/builder?type=capa-effectiveness'
    },
    {
      id: 'department-performance',
      title: 'Department Performance',
      description: 'NCR and CAPA metrics broken down by department.',
      icon: <BarChart2 className="h-8 w-8 text-purple-500" />,
      link: '/reports/builder?type=department-performance'
    },
    {
      id: 'pareto-analysis',
      title: 'Pareto Analysis',
      description: 'Top defects and root causes analysis (80/20 rule).',
      icon: <PieChart className="h-8 w-8 text-orange-500" />,
      link: '/reports/builder?type=pareto-analysis'
    }
  ];

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="md:flex md:items-center md:justify-between">
        <div className="flex-1 min-w-0">
          <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Reports Center
          </h2>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {reports.map((report) => (
          <Link 
            key={report.id} 
            to={report.link}
            className="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex items-center space-x-3 hover:border-gray-400 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
          >
            <div className="flex-shrink-0">
              {report.icon}
            </div>
            <div className="flex-1 min-w-0">
              <span className="absolute inset-0" aria-hidden="true" />
              <p className="text-sm font-medium text-gray-900">
                {report.title}
              </p>
              <p className="text-sm text-gray-500 truncate">
                {report.description}
              </p>
            </div>
          </Link>
        ))}
      </div>
    </div>
  );
};

export default ReportList;
