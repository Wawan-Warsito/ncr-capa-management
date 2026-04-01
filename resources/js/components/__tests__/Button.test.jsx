import { render, screen, fireEvent } from '@testing-library/react';
import { describe, it, expect, vi } from 'vitest';
import Button from '../Button';
import React from 'react';

describe('Button Component', () => {
  it('renders correctly', () => {
    render(<Button>Click Me</Button>);
    expect(screen.getByText('Click Me')).toBeInTheDocument();
  });

  it('handles click events', () => {
    const handleClick = vi.fn();
    render(<Button onClick={handleClick}>Click Me</Button>);
    
    fireEvent.click(screen.getByText('Click Me'));
    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  it('renders disabled state', () => {
    render(<Button disabled>Click Me</Button>);
    const button = screen.getByText('Click Me');
    expect(button).toBeDisabled();
  });

  it('applies correct variant class', () => {
    const { rerender } = render(<Button variant="danger">Delete</Button>);
    expect(screen.getByText('Delete')).toHaveClass('bg-red-600');

    rerender(<Button variant="secondary">Cancel</Button>);
    expect(screen.getByText('Cancel')).toHaveClass('bg-white');
  });
});
