import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import Select from '../Select';
import React from 'react';

describe('Select Component', () => {
  const options = [
    { value: '1', label: 'Option 1' },
    { value: '2', label: 'Option 2' },
  ];

  it('renders correctly', () => {
    render(<Select id="test-select" options={options} />);
    expect(screen.getByRole('combobox')).toBeInTheDocument();
    expect(screen.getByText('Select...')).toBeInTheDocument();
    expect(screen.getByText('Option 1')).toBeInTheDocument();
  });

  it('renders label if provided', () => {
    render(<Select id="test-select" label="Choose Option" options={options} />);
    expect(screen.getByLabelText('Choose Option')).toBeInTheDocument();
  });

  it('handles change events', () => {
    const handleChange = vi.fn();
    render(<Select id="test-select" onChange={handleChange} options={options} />);
    
    const select = screen.getByRole('combobox');
    fireEvent.change(select, { target: { value: '1' } });
    
    expect(handleChange).toHaveBeenCalledTimes(1);
    expect(select.value).toBe('1');
  });

  it('displays error message', () => {
    render(<Select id="test-select" error="Invalid selection" options={options} />);
    expect(screen.getByText('Invalid selection')).toBeInTheDocument();
  });

  it('renders required state', () => {
    render(<Select id="test-select" required options={options} />);
    expect(screen.getByRole('combobox')).toBeRequired();
  });
});
