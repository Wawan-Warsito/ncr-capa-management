import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import Input from '../Input';
import React from 'react';

describe('Input Component', () => {
  it('renders correctly', () => {
    render(<Input id="test-input" placeholder="Enter text" />);
    expect(screen.getByPlaceholderText('Enter text')).toBeInTheDocument();
  });

  it('renders label if provided', () => {
    render(<Input id="test-input" label="Test Label" />);
    expect(screen.getByText('Test Label')).toBeInTheDocument();
    expect(screen.getByLabelText('Test Label')).toBeInTheDocument();
  });

  it('handles change events', () => {
    const handleChange = vi.fn();
    render(<Input id="test-input" onChange={handleChange} />);
    
    const input = screen.getByRole('textbox');
    fireEvent.change(input, { target: { value: 'New Value' } });
    
    expect(handleChange).toHaveBeenCalledTimes(1);
  });

  it('displays error message', () => {
    render(<Input id="test-input" error="This is an error" />);
    expect(screen.getByText('This is an error')).toBeInTheDocument();
    expect(screen.getByRole('textbox')).toHaveClass('border-red-300');
  });

  it('sets required attribute', () => {
    render(<Input id="test-input" required />);
    expect(screen.getByRole('textbox')).toBeRequired();
  });
});
