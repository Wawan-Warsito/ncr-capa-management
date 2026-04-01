import React, { useState, useEffect } from 'react';
import api from '../services/api';
import Breadcrumb from '../components/Breadcrumb';

const Settings = () => {
  const [settings, setSettings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      const response = await api.get('/admin/settings');
      const items = response.data.data || response.data || [];
      const mapped = Array.isArray(items)
        ? items.map((s) => ({
            key: s.setting_key,
            value:
              s.setting_type === 'boolean'
                ? (s.value ? '1' : '0')
                : (s.value ?? ''),
            type: s.setting_type,
            description: s.description || '',
            category: s.category || 'General',
            is_public: !!s.is_public,
          }))
        : [];
      setSettings(mapped);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSettingChange = (key, value) => {
    setSettings(prev => prev.map(s => s.key === key ? { ...s, value } : s));
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const payload = {
        settings: settings.reduce((acc, curr) => {
          let v = curr.value;
          if (curr.type === 'boolean') {
            v = v === '1' || v === 1 || v === true || v === 'true';
          }
          acc[curr.key] = v;
          return acc;
        }, {}),
      };
      await api.put('/admin/settings', payload);
      alert('Settings saved successfully');
    } catch (err) {
      console.error(err);
      const message = err.response?.data?.message || err.message;
      const errors = err.response?.data?.errors;
      const errorText = errors ? Object.values(errors).flat().join('\n') : message;
      alert('Failed to save settings:\n' + errorText);
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="space-y-6">
      <Breadcrumb />
      
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-semibold text-gray-900">System Settings</h1>
      </div>

      <div className="bg-white shadow sm:rounded-lg">
        <div className="px-4 py-5 sm:p-6">
          <div className="space-y-6">
            {settings.length > 0 ? (
                settings.map((setting) => (
                  <div key={setting.key}>
                    <div className="flex items-start justify-between gap-4">
                      <label htmlFor={setting.key} className="block text-sm font-medium text-gray-700">
                        {setting.key.replace(/_/g, ' ').toUpperCase()}
                      </label>
                      <div className="text-xs text-gray-500 shrink-0">
                        {setting.category}
                      </div>
                    </div>
                    <div className="mt-1">
                      {setting.type === 'boolean' ? (
                        <select
                          id={setting.key}
                          value={setting.value}
                          onChange={(e) => handleSettingChange(setting.key, e.target.value)}
                          className="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        >
                          <option value="1">Yes</option>
                          <option value="0">No</option>
                        </select>
                      ) : (
                        <input
                          type={setting.type === 'integer' ? 'number' : 'text'}
                          id={setting.key}
                          value={setting.value}
                          onChange={(e) => handleSettingChange(setting.key, e.target.value)}
                          className="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        />
                      )}
                    </div>
                    {setting.description && <p className="mt-2 text-sm text-gray-500">{setting.description}</p>}
                  </div>
                ))
            ) : (
                <p className="text-gray-500">No settings available.</p>
            )}
            
            <div className="pt-5 border-t border-gray-200">
              <div className="flex justify-end">
                <button
                  type="button"
                  onClick={handleSave}
                  disabled={saving}
                  className="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  {saving ? 'Saving...' : 'Save Settings'}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Settings;
