import React, { useState, useRef, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import { Menu, Bell, User, LogOut, Settings, PenTool } from 'lucide-react';

const Header = ({ onMobileMenuOpen }) => {
    const { user, logout } = useAuth();
    const navigate = useNavigate();
    const [showNotifications, setShowNotifications] = useState(false);
    const [showProfile, setShowProfile] = useState(false);
    
    // Mock notifications state
    const [notifications, setNotifications] = useState([
        {
            id: 1,
            title: 'New NCR Assigned',
            message: 'NCR #26.P00-QC-01 has been assigned to you.',
            time: '10 minutes ago',
            read: false
        },
        {
            id: 2,
            title: 'CAPA Reminder',
            message: 'Action plan due for CAPA-2026-003.',
            time: '2 hours ago',
            read: false
        }
    ]);

    const unreadCount = notifications.filter(n => !n.read).length;
    
    const notificationRef = useRef(null);
    const profileRef = useRef(null);

    // Close dropdowns when clicking outside
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (notificationRef.current && !notificationRef.current.contains(event.target)) {
                setShowNotifications(false);
            }
            if (profileRef.current && !profileRef.current.contains(event.target)) {
                setShowProfile(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    const handleMarkAllRead = () => {
        setNotifications(notifications.map(n => ({ ...n, read: true })));
    };

    const handleViewAllNotifications = () => {
        setShowNotifications(false);
        navigate('/notifications');
    };

    return (
        <header className="bg-white border-b border-gray-200 shadow-sm z-10">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                {/* Mobile menu button */}
                <div className="md:hidden">
                    <button 
                        onClick={onMobileMenuOpen}
                        className="text-gray-500 hover:text-gray-700 focus:outline-none p-2 rounded-md hover:bg-gray-100"
                    >
                        <Menu className="h-6 w-6" />
                    </button>
                </div>

                <div className="flex-1 flex justify-end items-center gap-4">
                    {/* Notifications */}
                    <div className="relative" ref={notificationRef}>
                        <button 
                            onClick={() => setShowNotifications(!showNotifications)}
                            className="p-2 rounded-full text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none relative"
                        >
                            <span className="sr-only">View notifications</span>
                            <Bell className="h-6 w-6" />
                            {/* Notification Badge */}
                            {unreadCount > 0 && (
                                <span className="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white" />
                            )}
                        </button>

                        {/* Notification Dropdown */}
                        {showNotifications && (
                            <div className="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                <div className="px-4 py-2 border-b border-gray-100 flex justify-between items-center">
                                    <h3 className="text-sm font-semibold text-gray-700">Notifications</h3>
                                    {unreadCount > 0 && (
                                        <button 
                                            onClick={handleMarkAllRead}
                                            className="text-xs text-blue-600 hover:text-blue-800 cursor-pointer focus:outline-none"
                                        >
                                            Mark all as read
                                        </button>
                                    )}
                                </div>
                                <div className="max-h-60 overflow-y-auto">
                                    {notifications.length > 0 ? (
                                        notifications.map((notification) => (
                                            <div 
                                                key={notification.id}
                                                className={`px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition-colors ${!notification.read ? 'bg-blue-50' : ''}`}
                                            >
                                                <p className="text-sm text-gray-800 font-medium">{notification.title}</p>
                                                <p className="text-sm text-gray-600 truncate">{notification.message}</p>
                                                <p className="text-xs text-gray-400 mt-1">{notification.time}</p>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="px-4 py-3 text-center text-gray-500 text-sm">
                                            No notifications
                                        </div>
                                    )}
                                </div>
                                <div className="px-4 py-2 border-t border-gray-100 bg-gray-50 text-center rounded-b-md">
                                    <button 
                                        onClick={handleViewAllNotifications}
                                        className="text-xs font-medium text-blue-600 hover:text-blue-800 focus:outline-none"
                                    >
                                        View all notifications
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Profile Dropdown */}
                    <div className="relative ml-3" ref={profileRef}>
                        <div className="flex items-center gap-3 cursor-pointer" onClick={() => setShowProfile(!showProfile)}>
                            <div className="flex flex-col text-right hidden sm:block">
                                <span className="text-sm font-medium text-gray-900">{user?.name || 'User'}</span>
                                <span className="text-xs text-gray-500">{user?.role?.role_name || user?.role?.name || 'Role'}</span>
                            </div>
                            
                            <button 
                                className="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                <span className="sr-only">Open user menu</span>
                                <div className="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm border border-blue-200">
                                    {user?.name ? user.name.charAt(0).toUpperCase() : 'U'}
                                </div>
                            </button>
                        </div>

                        {/* Profile Menu */}
                        {showProfile && (
                            <div className="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div className="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-md">
                                    <p className="text-sm font-medium text-gray-900">Signed in as</p>
                                    <p className="text-sm text-gray-500 truncate font-medium">{user?.email}</p>
                                </div>
                                
                                <div className="py-1">
                                    <button
                                        onClick={() => { setShowProfile(false); navigate('/my-signature'); }}
                                        className="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        <PenTool className="mr-3 h-4 w-4 text-gray-500" />
                                        My Signature
                                    </button>
                                    <button
                                        onClick={() => { setShowProfile(false); navigate('/admin/settings'); }}
                                        className="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        <Settings className="mr-3 h-4 w-4 text-gray-500" />
                                        Settings
                                    </button>
                                </div>
                                
                                <div className="py-1 border-t border-gray-100">
                                    <button
                                        onClick={handleLogout}
                                        className="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                    >
                                        <LogOut className="mr-3 h-4 w-4 text-red-500" />
                                        Sign out
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </header>
    );
};

export default Header;
