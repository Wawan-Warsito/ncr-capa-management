import React, { useState } from 'react';
import { Outlet } from 'react-router-dom';
import Sidebar from '../components/Sidebar';
import Header from '../components/Header';
import ErrorBoundary from '../components/ErrorBoundary';

const MainLayout = () => {
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="flex min-h-screen bg-gray-50">
            {/* Sidebar */}
            <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

            <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
                {/* Header */}
                <Header onMobileMenuOpen={() => setSidebarOpen(true)} />

                {/* Main Content */}
                <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    <ErrorBoundary>
                        <Outlet />
                    </ErrorBoundary>
                </main>

                {/* Footer */}
                <footer className="bg-white border-t border-gray-200 p-4">
                    <div className="text-center text-sm text-gray-500">
                        <p>&copy; {new Date().getFullYear()} NCR CAPA Management System. All rights reserved.</p>
                    </div>
                </footer>
            </div>
        </div>
    );
};

export default MainLayout;
