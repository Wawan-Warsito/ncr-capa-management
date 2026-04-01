import React from 'react';
import { ComposedChart, Bar, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const ParetoChart = ({ data, title }) => {
  return (
    <div className="bg-white p-4 shadow rounded-lg">
      <h3 className="text-lg font-medium leading-6 text-gray-900 mb-4">{title || 'Pareto Analysis'}</h3>
      <div className="h-72">
        <ResponsiveContainer width="100%" height="100%">
          <ComposedChart
            data={data}
            margin={{
              top: 20,
              right: 20,
              bottom: 20,
              left: 20,
            }}
          >
            <CartesianGrid stroke="#f5f5f5" />
            <XAxis dataKey="name" scale="band" />
            <YAxis yAxisId="left" orientation="left" stroke="#8884d8" />
            <YAxis yAxisId="right" orientation="right" stroke="#ff7300" unit="%" />
            <Tooltip />
            <Legend />
            <Bar yAxisId="left" dataKey="count" name="Defect Count" barSize={20} fill="#413ea0" />
            <Line yAxisId="right" type="monotone" dataKey="cumulative" name="Cumulative %" stroke="#ff7300" />
          </ComposedChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
};

export default ParetoChart;
