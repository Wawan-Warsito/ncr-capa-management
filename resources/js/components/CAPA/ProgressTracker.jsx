import React from 'react';
import { CheckCircle, Circle, Clock } from 'lucide-react';

const ProgressTracker = ({ steps, currentStep }) => {
  // Steps structure: [{ id: 1, name: 'Root Cause', status: 'completed' }, ...]
  
  const getStatusIcon = (status) => {
    switch (status) {
      case 'completed': return <CheckCircle className="h-6 w-6 text-green-500" />;
      case 'current': return <Clock className="h-6 w-6 text-blue-500" />;
      default: return <Circle className="h-6 w-6 text-gray-300" />;
    }
  };

  return (
    <nav aria-label="Progress">
      <ol className="overflow-hidden rounded-md lg:flex lg:border lg:border-gray-200">
        {steps.map((step, stepIdx) => (
          <li key={step.id} className="relative overflow-hidden lg:flex-1">
            <div className={`border border-gray-200 overflow-hidden lg:border-0 ${stepIdx === 0 ? 'rounded-t-md border-b-0' : ''} ${stepIdx === steps.length - 1 ? 'rounded-b-md border-t-0' : ''}`}>
                <div className="group flex items-center w-full">
                  <span className="px-6 py-4 flex items-center text-sm font-medium">
                    <span className="flex-shrink-0">
                      {getStatusIcon(step.status)}
                    </span>
                    <span className={`ml-4 text-sm font-medium ${step.status === 'completed' ? 'text-gray-900' : step.status === 'current' ? 'text-blue-600' : 'text-gray-500'}`}>
                        {step.name}
                    </span>
                  </span>
                </div>
                {stepIdx !== steps.length - 1 ? (
                  <>
                    <div className="absolute top-0 right-0 h-full w-5 hidden lg:block" aria-hidden="true">
                      <svg
                        className="h-full w-full text-gray-300"
                        viewBox="0 0 22 80"
                        fill="none"
                        preserveAspectRatio="none"
                      >
                        <path
                          d="M0 -2L20 40L0 82"
                          vectorEffect="non-scaling-stroke"
                          stroke="currentcolor"
                          strokeLinejoin="round"
                        />
                      </svg>
                    </div>
                  </>
                ) : null}
            </div>
          </li>
        ))}
      </ol>
    </nav>
  );
};

export default ProgressTracker;
