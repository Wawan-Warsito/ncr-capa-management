import React, { useState } from 'react';
import { Plus, Trash2 } from 'lucide-react';
import DatePicker from '../DatePicker';
import Input from '../Input';
import Button from '../Button';

const ActionPlanForm = ({ actions, onChange, type = 'corrective' }) => {
  const handleAddAction = () => {
    onChange([
      ...actions,
      { description: '', assigned_to: '', due_date: '', status: 'Pending' }
    ]);
  };

  const handleRemoveAction = (index) => {
    const newActions = actions.filter((_, i) => i !== index);
    onChange(newActions);
  };

  const handleChange = (index, field, value) => {
    const newActions = [...actions];
    newActions[index] = { ...newActions[index], [field]: value };
    onChange(newActions);
  };

  return (
    <div className="space-y-4">
      <div className="flex justify-between items-center">
        <h4 className="text-md font-medium text-gray-900 capitalize">{type} Actions</h4>
        <Button onClick={handleAddAction} size="sm" variant="secondary">
          <Plus className="h-4 w-4 mr-1" /> Add Action
        </Button>
      </div>

      {actions.length === 0 && (
        <p className="text-sm text-gray-500 italic">No actions added yet.</p>
      )}

      {actions.map((action, index) => (
        <div key={index} className="bg-gray-50 p-4 rounded-md border border-gray-200 relative">
          <button
            type="button"
            onClick={() => handleRemoveAction(index)}
            className="absolute top-2 right-2 text-red-500 hover:text-red-700"
          >
            <Trash2 className="h-4 w-4" />
          </button>
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div className="sm:col-span-2">
              <Input
                label="Action Description"
                id={`action-desc-${index}`}
                value={action.description}
                onChange={(e) => handleChange(index, 'description', e.target.value)}
                placeholder={`Describe the ${type} action`}
              />
            </div>
            <Input
              label="Assigned To (User ID or Name)"
              id={`action-assigned-${index}`}
              value={action.assigned_to}
              onChange={(e) => handleChange(index, 'assigned_to', e.target.value)}
            />
            <DatePicker
              label="Due Date"
              id={`action-due-${index}`}
              value={action.due_date}
              onChange={(e) => handleChange(index, 'due_date', e.target.value)}
            />
          </div>
        </div>
      ))}
    </div>
  );
};

export default ActionPlanForm;
