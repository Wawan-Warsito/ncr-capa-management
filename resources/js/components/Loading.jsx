import React from 'react';
import { Loader } from 'lucide-react';

const Loading = ({ message = 'Loading...', fullScreen = false }) => {
  const content = (
    <div className="flex flex-col items-center justify-center space-y-4">
      <Loader className="animate-spin h-10 w-10 text-blue-600" />
      <p className="text-gray-500 text-lg">{message}</p>
    </div>
  );

  if (fullScreen) {
    return (
      <div className="fixed inset-0 bg-white bg-opacity-75 z-50 flex items-center justify-center">
        {content}
      </div>
    );
  }

  return (
    <div className="w-full h-64 flex items-center justify-center">
      {content}
    </div>
  );
};

export default Loading;
