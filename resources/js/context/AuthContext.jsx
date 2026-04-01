import React, { createContext, useState, useContext, useEffect } from 'react';
import api from '../services/api';
import { useNavigate } from 'react-router-dom';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [errors, setErrors] = useState([]);
    
    // We don't have navigate here because this provider wraps the Router
    // So we'll handle navigation in the components or pass a callback

    const getUser = async () => {
        try {
            const response = await api.get('/auth/me');
            setUser(response.data.data);
        } catch (error) {
            setUser(null);
        } finally {
            setLoading(false);
        }
    };

    const login = async ({ email, password }) => {
        setLoading(true);
        setErrors([]);
        try {
            // Token based auth
            const response = await api.post('/auth/login', { email, password });
            const { token, user } = response.data.data;
            
            localStorage.setItem('token', token);
            setUser(user);
            
            setLoading(false);
            return true;
        } catch (e) {
            console.error('Login error:', e);
            if (e.response && e.response.status === 422) {
                setErrors(e.response.data.errors);
            } else if (e.response && e.response.data && e.response.data.message) {
                setErrors({ email: [e.response.data.message] });
            } else if (e.response && e.response.status === 401) {
                setErrors({ email: ['Invalid credentials.'] });
            } else {
                setErrors({ email: ['Invalid credentials or server error.'] });
            }
            setLoading(false);
            return false;
        }
    };

    const logout = async () => {
        try {
            await api.post('/auth/logout');
        } catch (error) {
            console.error('Logout failed', error);
        } finally {
            localStorage.removeItem('token');
            setUser(null);
        }
    };

    useEffect(() => {
        getUser();
    }, []);

    return (
        <AuthContext.Provider value={{ user, login, logout, loading, errors }}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => useContext(AuthContext);
