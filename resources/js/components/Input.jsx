import React from 'react';

const Input = ({ label, id, type = 'text', value, onChange, placeholder, required = false, error, className = '' }) => {
  return (
    <div className={className}>
      {label && <label htmlFor={id} className="block text-sm font-medium text-gray-700">{label}</label>}
      <div className="mt-1">
        <input
          type={type}
          name={id}
          id={id}
          value={value}
          onChange={onChange}
          required={required}
          className={`shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md ${error ? 'border-red-300' : ''}`}
          placeholder={placeholder}
        />
      </div>
      {error && <p className="mt-2 text-sm text-red-600">{error}</p>}
    </div>
  );
};

export default Input;
