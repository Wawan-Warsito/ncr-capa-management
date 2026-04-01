import './bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';

// Import Layouts and Pages
import MainLayout from './layouts/MainLayout';
import Login from './pages/Login';
import ForgotPassword from './pages/ForgotPassword';
import ResetPassword from './pages/ResetPassword';
import CompanyDashboard from './pages/CompanyDashboard';
import NCRList from './pages/NCRList';
import NCRCreate from './pages/NCRCreate';
import NCRDetail from './pages/NCRDetail';
import NCREdit from './pages/NCREdit';
import NCRApproval from './pages/NCRApproval';
import NCRPrintPage from './pages/NCRPrintPage';
import DepartmentDashboard from './pages/DepartmentDashboard';
import PersonalDashboard from './pages/PersonalDashboard';
import CAPAList from './pages/CAPAList';
import CAPACreate from './pages/CAPACreate';
import CAPADetail from './pages/CAPADetail';
import CAPAProgress from './pages/CAPAProgress';
import ReportList from './pages/ReportList';
import ReportBuilder from './pages/ReportBuilder';
import ReportViewer from './pages/ReportViewer';
import UserManagement from './pages/UserManagement';
import DepartmentManagement from './pages/DepartmentManagement';
import Settings from './pages/Settings';
import NotificationList from './pages/NotificationList';
import MySignature from './pages/MySignature';

// Protected Route Wrapper
const ProtectedRoute = () => {
    const { user, loading } = useAuth();

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    return <Outlet />;
};

const canViewCompanyDashboard = (user) => {
    const roleName = (user?.role?.role_name || user?.role?.name || user?.role || '').toString();
    return ['Administrator', 'Super Admin', 'QC Manager'].includes(roleName);
};

const DashboardRedirect = () => {
    const { user } = useAuth();
    const to = canViewCompanyDashboard(user) ? '/dashboard/company' : '/dashboard/department';
    return <Navigate to={to} replace />;
};

const CompanyDashboardRoute = () => {
    const { user } = useAuth();
    if (!canViewCompanyDashboard(user)) return <Navigate to="/dashboard/department" replace />;
    return <CompanyDashboard />;
};

function App() {
    return (
        <BrowserRouter>
            <AuthProvider>
                <Routes>
                    {/* Public Routes */}
                    <Route path="/login" element={<Login />} />
                    <Route path="/forgot-password" element={<ForgotPassword />} />
                    <Route path="/password-reset/:token" element={<ResetPassword />} />

                    {/* Protected Routes */}
                    <Route element={<ProtectedRoute />}>
                        {/* Print Route (No Layout) */}
                        <Route path="/ncrs/:id/print" element={<NCRPrintPage />} />

                        <Route path="/" element={<MainLayout />}>
                            <Route index element={<DashboardRedirect />} />
                            <Route path="dashboard" element={<DashboardRedirect />} />
                            <Route path="dashboard/company" element={<CompanyDashboardRoute />} />
                            <Route path="dashboard/department" element={<DepartmentDashboard />} />
                            <Route path="dashboard/personal" element={<PersonalDashboard />} />
                            
                            {/* NCR Routes */}
                            <Route path="ncrs">
                                <Route index element={<NCRList />} />
                                <Route path="create" element={<NCRCreate />} />
                                <Route path=":id" element={<NCRDetail />} />
                                <Route path=":id/edit" element={<NCREdit />} />
                                <Route path=":id/approve" element={<NCRApproval />} />
                            </Route>
                            {/* Compatibility for old routes or shorthand */}
                            <Route path="ncr" element={<Navigate to="/ncrs" replace />} />

                            {/* CAPA Routes */}
                            <Route path="capas">
                                <Route index element={<CAPAList />} />
                                <Route path="create" element={<CAPACreate />} />
                                <Route path=":id" element={<CAPADetail />} />
                                <Route path=":id/progress" element={<CAPAProgress />} />
                            </Route>

                            {/* Report Routes */}
                            <Route path="reports">
                                <Route index element={<ReportList />} />
                                <Route path="builder" element={<ReportBuilder />} />
                                <Route path="view/:type" element={<ReportViewer />} />
                                <Route path="view" element={<Navigate to="/reports/view/ncr-summary" replace />} />
                            </Route>

                            {/* Notifications */}
                            <Route path="notifications" element={<NotificationList />} />

                            {/* User Profile */}
                            <Route path="my-signature" element={<MySignature />} />

                            {/* Admin Routes */}
                            <Route path="admin">
                                <Route path="users" element={<UserManagement />} />
                                <Route path="departments" element={<DepartmentManagement />} />
                                <Route path="settings" element={<Settings />} />
                            </Route>

                        </Route>
                    </Route>
                </Routes>
            </AuthProvider>
        </BrowserRouter>
    );
}

if (document.getElementById('app')) {
    const container = document.getElementById('app');
    
    // Check if root already exists to avoid double mounting
    if (!container._reactRootContainer) {
        const root = ReactDOM.createRoot(container);
        root.render(
            <React.StrictMode>
                <App />
            </React.StrictMode>
        );
    }
}
