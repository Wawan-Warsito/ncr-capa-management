import React from 'react';

const NCRTimeline = ({ timeline = [] }) => {
    if (!timeline || timeline.length === 0) {
        return <div className="text-gray-500 italic text-sm">No history available.</div>;
    }

    return (
        <div className="flow-root">
            <ul className="-mb-8">
                {timeline.map((event, eventIdx) => (
                    <li key={event.id || eventIdx}>
                        <div className="relative pb-8">
                            {eventIdx !== timeline.length - 1 ? (
                                <span className="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true" />
                            ) : null}
                            <div className="relative flex space-x-3">
                                <div>
                                    <span className="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                        <svg className="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clipRule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <div className="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p className="text-sm text-gray-500">
                                            {event.action} by <span className="font-medium text-gray-900">{event.user_name || 'Unknown User'}</span>
                                        </p>
                                        {event.comment && (
                                            <p className="mt-1 text-sm text-gray-600">{event.comment}</p>
                                        )}
                                    </div>
                                    <div className="text-right text-sm whitespace-nowrap text-gray-500">
                                        <time dateTime={event.created_at}>{new Date(event.created_at).toLocaleDateString()} {new Date(event.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default NCRTimeline;
