import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import Login from '../Login';
import { useAuth } from '../../context/AuthContext';
import { MemoryRouter } from 'react-router-dom';
import React from 'react';

// Mock the AuthContext
vi.mock('../../context/AuthContext', () => ({
  useAuth: vi.fn(),
}));

// Mock useNavigate
const mockNavigate = vi.fn();
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  };
});

describe('Login Page', () => {
  it('renders login form', () => {
    useAuth.mockReturnValue({ login: vi.fn(), errors: {} });
    render(
      <MemoryRouter>
        <Login />
      </MemoryRouter>
    );
    expect(screen.getByPlaceholderText('Email address')).toBeInTheDocument();
    expect(screen.getByPlaceholderText('Password')).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /sign in/i })).toBeInTheDocument();
  });

  it('handles user input', () => {
    useAuth.mockReturnValue({ login: vi.fn(), errors: {} });
    render(
      <MemoryRouter>
        <Login />
      </MemoryRouter>
    );

    fireEvent.change(screen.getByPlaceholderText('Email address'), { target: { value: 'test@example.com' } });
    fireEvent.change(screen.getByPlaceholderText('Password'), { target: { value: 'password' } });

    expect(screen.getByPlaceholderText('Email address').value).toBe('test@example.com');
    expect(screen.getByPlaceholderText('Password').value).toBe('password');
  });

  it('submits form with credentials', async () => {
    const mockLogin = vi.fn().mockResolvedValue(true);
    useAuth.mockReturnValue({ login: mockLogin, errors: {} });
    
    render(
      <MemoryRouter>
        <Login />
      </MemoryRouter>
    );

    fireEvent.change(screen.getByPlaceholderText('Email address'), { target: { value: 'test@example.com' } });
    fireEvent.change(screen.getByPlaceholderText('Password'), { target: { value: 'password' } });
    fireEvent.click(screen.getByRole('button', { name: /sign in/i }));

    await waitFor(() => {
        expect(mockLogin).toHaveBeenCalledWith({ email: 'test@example.com', password: 'password' });
    });
  });
  
  it('navigates to dashboard on success', async () => {
    const mockLogin = vi.fn().mockResolvedValue(true);
    useAuth.mockReturnValue({ login: mockLogin, errors: {} });
    
    render(
      <MemoryRouter>
        <Login />
      </MemoryRouter>
    );

    fireEvent.change(screen.getByPlaceholderText('Email address'), { target: { value: 'test@example.com' } });
    fireEvent.change(screen.getByPlaceholderText('Password'), { target: { value: 'password' } });
    fireEvent.click(screen.getByRole('button', { name: /sign in/i }));

    await waitFor(() => {
        expect(mockNavigate).toHaveBeenCalledWith('/dashboard');
    });
  });

  it('displays errors on failure', () => {
    useAuth.mockReturnValue({ login: vi.fn(), errors: { email: ['Invalid credentials'] } });
    
    render(
      <MemoryRouter>
        <Login />
      </MemoryRouter>
    );
    
    expect(screen.getByText('Invalid credentials')).toBeInTheDocument();
  });
});
