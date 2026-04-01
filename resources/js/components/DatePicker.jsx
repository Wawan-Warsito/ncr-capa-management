import React from 'react';

const DatePicker = ({ label, id, value, onChange, required = false, error, className = '', min, max }) => {
  return (
    <div className={className}>
      {label && <label htmlFor={id} className="block text-sm font-medium text-gray-700">{label}</label>}
      <div className="mt-1">
        <input
          type="date"
          name={id}
          id={id}
          value={value}
          onChange={onChange}
          required={required}
          min={min}
          max={max}
          className={`shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md ${error ? 'border-red-300' : ''}`}
        />
      </div>
      {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
    </div>
  );
};

export default DatePicker;
