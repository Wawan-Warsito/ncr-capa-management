import React, { useState } from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { Home, ClipboardList, Shield, BarChart2, Settings, Users, Briefcase, ChevronDown, ChevronRight, X } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

const SidebarContent = ({ onClose, isMobile }) => {
    const location = useLocation();
    const { user } = useAuth();
    const roleName = (user?.role?.role_name || user?.role?.name || user?.role || '').toString();
    const canSeeCompany = ['Administrator', 'Super Admin', 'QC Manager'].includes(roleName);
    const canSeeAdmin = ['Administrator', 'Super Admin'].includes(roleName);
    const [expanded, setExpanded] = useState({
        dashboard: true,
        reports: false,
        admin: false
    });

    const toggleExpand = (key) => {
        setExpanded(prev => ({ ...prev, [key]: !prev[key] }));
    };

    const navigation = [
        {
            name: 'Dashboard',
            icon: Home,
            key: 'dashboard',
            children: [
                ...(canSeeCompany ? [{ name: 'Company Overview', href: '/dashboard/company' }] : []),
                { name: 'My Department', href: '/dashboard/department' },
                { name: 'My Dashboard', href: '/dashboard/personal' },
            ]
        },
        { name: 'NCR Management', href: '/ncrs', icon: ClipboardList },
        { name: 'CAPA Management', href: '/capas', icon: Shield },
        {
            name: 'Reports',
            icon: BarChart2,
            key: 'reports',
            children: [
                { name: 'Report List', href: '/reports' },
                { name: 'Report Builder', href: '/reports/builder' },
            ]
        },
        ...(canSeeAdmin ? [{
            name: 'Admin',
            icon: Settings,
            key: 'admin',
            children: [
                { name: 'Users', href: '/admin/users', icon: Users },
                { name: 'Departments', href: '/admin/departments', icon: Briefcase },
                { name: 'Settings', href: '/admin/settings', icon: Settings },
            ]
        }] : []),
    ];

    const isChildActive = (children) => {
        return children.some(child => location.pathname === child.href);
    };

    return (
        <aside className="flex flex-col w-64 bg-white border-r border-gray-200 h-full">
            <div className="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                <span className="text-xl font-bold text-blue-600">NCR CAPA</span>
                {isMobile && (
                    <button 
                        onClick={onClose} 
                        className="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none"
                    >
                        <X className="h-6 w-6" />
                    </button>
                )}
            </div>
            <nav className="flex-1 overflow-y-auto py-4">
                <ul className="space-y-1 px-2">
                    {navigation.map((item) => (
                        <li key={item.name}>
                            {item.children ? (
                                <div>
                                    <button
                                        onClick={() => toggleExpand(item.key)}
                                        className={`w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-md transition-colors ${
                                            isChildActive(item.children) || expanded[item.key]
                                                ? 'bg-gray-50 text-gray-900'
                                                : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                                        }`}
                                    >
                                        <div className="flex items-center">
                                            <item.icon className="mr-3 h-5 w-5 text-gray-500" />
                                            {item.name}
                                        </div>
                                        {expanded[item.key] ? (
                                            <ChevronDown className="h-4 w-4 text-gray-400" />
                                        ) : (
                                            <ChevronRight className="h-4 w-4 text-gray-400" />
                                        )}
                                    </button>
                                    {expanded[item.key] && (
                                        <ul className="mt-1 space-y-1 pl-10">
                                            {item.children.map((child) => (
                                                <li key={child.name}>
                                                    <NavLink
                                                        to={child.href}
                                                        end={child.href === '/dashboard' || child.href === '/reports'}
                                                        className={({ isActive }) =>
                                                            `block px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                                                                isActive
                                                                    ? 'bg-blue-50 text-blue-700'
                                                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                                                            }`
                                                        }
                                                        onClick={() => isMobile && onClose && onClose()}
                                                    >
                                                        {child.name}
                                                    </NavLink>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </div>
                            ) : (
                                <NavLink
                                    to={item.href}
                                    className={({ isActive }) =>
                                        `flex items-center px-4 py-3 text-sm font-medium rounded-md transition-colors ${
                                            isActive
                                                ? 'bg-blue-50 text-blue-700'
                                                : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'
                                        }`
                                    }
                                    onClick={() => isMobile && onClose && onClose()}
                                >
                                    <item.icon className={`mr-3 h-5 w-5 ${location.pathname.startsWith(item.href) ? 'text-blue-700' : 'text-gray-500'}`} />
                                    {item.name}
                                </NavLink>
                            )}
                        </li>
                    ))}
                </ul>
            </nav>
            <div className="p-4 border-t border-gray-200">
                <div className="bg-blue-50 p-4 rounded-md">
                    <p className="text-xs text-blue-800 font-semibold">Need Help?</p>
                    <p className="text-xs text-blue-600 mt-1">Check the documentation or contact support.</p>
                </div>
            </div>
        </aside>
    );
};

const Sidebar = ({ isOpen, onClose }) => {
    return (
        <>
            {/* Mobile Sidebar Overlay */}
            <div className={`fixed inset-0 z-40 md:hidden ${isOpen ? 'block' : 'hidden'}`}>
                {/* Backdrop */}
                <div 
                    className="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" 
                    onClick={onClose}
                ></div>

                {/* Sidebar Panel */}
                <div className="fixed inset-y-0 left-0 flex flex-col w-64 bg-white transform transition-transform duration-300 ease-in-out">
                    <SidebarContent onClose={onClose} isMobile={true} />
                </div>
            </div>

            {/* Desktop Sidebar (Static) */}
            <div className="hidden md:flex md:flex-shrink-0 min-h-screen">
                <SidebarContent isMobile={false} />
            </div>
        </>
    );
};

export default Sidebar;
