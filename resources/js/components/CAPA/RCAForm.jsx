import React from 'react';

const RCAForm = ({ value, onChange, readOnly = false }) => {
  // value expected to be { five_whys: ['', '', '', '', ''], fishbone: '' } or similar string structure
  
  const handleWhyChange = (index, text) => {
    const newWhys = [...(value.five_whys || ['', '', '', '', ''])];
    newWhys[index] = text;
    onChange({ ...value, five_whys: newWhys });
  };

  const handleFishboneChange = (text) => {
    onChange({ ...value, fishbone: text });
  };

  const whys = value?.five_whys || ['', '', '', '', ''];

  return (
    <div className="space-y-6">
      <div>
        <h4 className="text-md font-medium text-gray-900 mb-2">5 Whys Analysis</h4>
        <div className="space-y-3">
          {whys.map((why, index) => (
            <div key={index} className="flex items-start">
              <span className="flex-shrink-0 w-16 pt-2 text-sm font-medium text-gray-500">Why {index + 1}?</span>
              <input
                type="text"
                value={why}
                onChange={(e) => handleWhyChange(index, e.target.value)}
                readOnly={readOnly}
                className="flex-1 focus:ring-blue-500 focus:border-blue-500 block w-full min-w-0 rounded-md sm:text-sm border-gray-300"
                placeholder={`Cause level ${index + 1}`}
              />
            </div>
          ))}
        </div>
      </div>

      <div>
        <h4 className="text-md font-medium text-gray-900 mb-2">Fishbone (Ishikawa) Diagram / Description</h4>
        <textarea
          rows={4}
          value={value?.fishbone || ''}
          onChange={(e) => handleFishboneChange(e.target.value)}
          readOnly={readOnly}
          className="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md"
          placeholder="Describe Man, Machine, Method, Material, Measurement, Environment factors..."
        />
      </div>
    </div>
  );
};

export default RCAForm;
